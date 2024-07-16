<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Proxy;
use App\Repository\ProxyRepository;
use App\Response\BaseApiResponse;
use App\Response\GetProxyIpResponse;
use App\Response\StoreCallDataResponse;
use App\Service\LoggerService;
use App\Service\ProxyServiceInterface;
use App\Service\ProxyDeciderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class ProxyProviderController extends AbstractController
{
    public function __construct(
        private readonly LoggerService          $loggerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProxyDeciderService    $deciderService,
        private readonly LoggerInterface        $logger,
        private readonly ProxyServiceInterface  $proxyService
    ) {
    }

    /**
     * Return proxy connection data
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route("/get-proxy-connection-data/", methods: Request::METHOD_POST)]
    public function getConnectionData(Request $request): JsonResponse
    {
        try {
            $requestJson = $request->getContent();
            $requestData = json_decode($requestJson, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                return GetProxyIpResponse::buildBadRequestErrorResponse("Provided data is not a json!")->toJsonResponse();
            }

            $internalId     = $requestData['proxyInternalId'] ?: null;
            $usage          = $requestData['usage'] ?: null;
            $countryIsoCode = $requestData['countryIsoCode'] ?: null;
            $provider       = $requestData['provider'] ?: null;

            $this->logger->info("[{$request->getClientIp()}] Requested proxy data", [
                'internalId'     => $internalId,
                'usage'          => $usage,
                'countryIsoCode' => $countryIsoCode,
                'provider'       => $provider,
            ]);

            $response = GetProxyIpResponse::buildOkResponse();
            $proxy    = $this->deciderService->provide($internalId, $usage, $countryIsoCode, $provider);

            // calling the proxy does not mean it will be used, but assuming it will
            $proxy->setLastUsage(new DateTime());

            // doesn't care about transaction on purpose, if something fails - nothing bad really happens
            $this->entityManager->persist($proxy);
            $this->entityManager->flush();

            $response->setIp($proxy->getIp());
            $response->setPort($proxy->getPort());
            $response->setPassword($proxy->getPassword());
            $response->setUsername($proxy->getUsername());

            $this->logger->info("[{$request->getClientIp()}] Serving data of proxy with id: {$proxy->getId()}");

        } catch (Exception|TypeError $e) {
            $this->loggerService->logException($e);

            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                $response = BaseApiResponse::buildNotFoundResponse();
                $response->setMessage($e->getMessage());

                return $response->toJsonResponse();
            }

            return BaseApiResponse::buildInternalServerErrorResponse($e->getMessage())->toJsonResponse();
        }

        return $response->toJsonResponse();
    }

    /**
     * Stores the proxy call data
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route("/store-call-data", methods: Request::METHOD_POST)]
    public function storeCallData(Request $request): JsonResponse
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                return BaseApiResponse::buildBadRequestErrorResponse("Request does not contain valid json!")->toJsonResponse();
            }

            $url       = $dataArray['url'] ?? null;
            $proxyIp   = $dataArray['proxyIp'] ?? null;
            $proxyPort = $dataArray['proxyPort'] ?? null;

            if (empty($url)) {
                return BaseApiResponse::buildBadRequestErrorResponse("`url` is empty or no such key present in request")->toJsonResponse();
            }

            if (empty($proxyIp)) {
                return BaseApiResponse::buildBadRequestErrorResponse("`proxyIp` is empty or no such key present in request")->toJsonResponse();
            }

            if (empty($proxyPort)) {
                return BaseApiResponse::buildBadRequestErrorResponse("`proxyPort` is empty or no such key present in request")->toJsonResponse();
            }

            $call = new Call();
            $call->setCalledUrl($url);

            /** @var ProxyRepository $proxyRepo */
            $proxyRepo = $this->entityManager->getRepository(Proxy::class);
            $proxy     = $proxyRepo->findByIpAndPort($proxyIp, $proxyPort);
            if (empty($proxy)) {
                throw new LogicException("No proxy db entry found for ip / port: {$proxyIp} / {$proxyPort}");
            }

            $call->setProxy($proxy);

            $this->entityManager->persist($call);
            $this->entityManager->flush();

            $response = StoreCallDataResponse::buildOkResponse();
            $response->setId($call->getId());

            return $response->toJsonResponse();
        } catch (Exception|TypeError $e) {
            $this->loggerService->logException($e);

            return BaseApiResponse::buildInternalServerErrorResponse($e->getMessage())->toJsonResponse();
        }
    }

    /**
     * @param Call $call
     * @param bool $success
     *
     * @return JsonResponse
     */
    #[Route("/update-call-data/{id}/{success}", methods: Request::METHOD_POST)]
    public function updateCallData(Call $call, bool $success): JsonResponse
    {
        try {
            $call->setSuccess($success);
            $call->setFinished(new DateTime());

            $this->entityManager->persist($call);
            $this->entityManager->flush();

            return BaseApiResponse::buildOkResponse()->toJsonResponse();
        } catch (Exception|TypeError $e) {
            $this->loggerService->logException($e);

            return BaseApiResponse::buildInternalServerErrorResponse($e->getMessage())->toJsonResponse();
        }
    }

    /**
     * @return JsonResponse
     */
    #[Route("/is-proxy-reachable", methods: Request::METHOD_GET)]
    public function isProxyReachable(): JsonResponse
    {
        $isReachable = $this->proxyService->isProxyOk();
        if ($isReachable) {
            return BaseApiResponse::buildOkResponse()->toJsonResponse();
        }

        $response = new BaseApiResponse();
        $response->setSuccess(false);
        $response->setMessage("External proxy services are not reachable.");
        $response->setCode(Response::HTTP_SERVICE_UNAVAILABLE);

        return $response->toJsonResponse();
    }

}