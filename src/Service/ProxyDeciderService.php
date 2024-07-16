<?php

namespace App\Service;

use App\Entity\Proxy;
use App\Repository\ProxyRepository;
use Doctrine\ORM\NonUniqueResultException;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decides the best proxy candidate for next call
 */
class ProxyDeciderService
{
    public function __construct(
        private readonly ProxyRepository $proxyRepository
    ) {

    }

    /**
     * @param string|null $internalId
     * @param string|null $usage
     * @param string|null $countryIsoCode
     * @param string|null $provider
     *
     * @return Proxy
     *
     * @throws NonUniqueResultException
     */
    public function provide(
        ?string $internalId = null,
        ?string $usage = null,
        ?string $countryIsoCode = null,
        ?string $provider = null
    ): Proxy
    {
        $proxy = null;

        // nothing is set, get a random least used one
        if (empty($internalId) && empty($usage) && empty($countryIsoCode) && empty($provider)) {
            $proxy = $this->proxyRepository->findLeastUsed(Proxy::USAGE_GENERIC);
            if (empty($proxy)) {
                throw new LogicException(
                    "Could not find any (generic) least used proxy at all!",
                    Response::HTTP_NOT_FOUND
                );
            }
        }

        if (empty($proxy) && !empty($usage)) {
            $proxy = $this->proxyRepository->findForUsage($usage, $countryIsoCode, $provider);

            // try obtaining ANY for target usage if none was found for country / provider
            if (empty($proxy)) {
                $proxy = $this->proxyRepository->findForUsage($usage, null , $provider);
            }

            if (empty($proxy)) {
                throw new LogicException(
                    "Could not find proxy for usage: {$usage}, country code: {$countryIsoCode}, provider: {$provider}",
                    Response::HTTP_NOT_FOUND
                );
            }
        }

        if (empty($proxy) && !empty($internalId)) {
            $proxy = $this->proxyRepository->findOneBy(['internalId' => $internalId]);
        }

        if (empty($proxy)) {
            throw new LogicException("Could not find proxy for identifier: {$internalId}", Response::HTTP_NOT_FOUND);
        }

        return $proxy;
    }

}