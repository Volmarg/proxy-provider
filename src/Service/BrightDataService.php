<?php

namespace App\Service;

use App\Service\Request\Guzzle\GuzzleInterface;
use App\Service\Request\Guzzle\GuzzleService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

/**
 * {@see https://get.brightdata.com/mwjfqnunf35j}
 *
 * > Warning - Information based on discussions with support <
 *  - there is no direct way to check if the SERP zone is up,
 *  - there was an idea to check how many failed calls where there in last X hours, but this
 *    was abandoned as it would be hard to track ....
 */
class BrightDataService implements ProxyServiceInterface
{
    /**
     * For more details {@see self::isProxyOk}
     */
    private const MAX_IS_OK_RECHECK = 2;

    /**
     * This is added in order to avoid calling services again if it's really some server hiccup
     * for more {@see self::isProxyOk()}
     */
    private const SLEEP_SECONDS_BETWEEN_RECHECK = 1;

    public function __construct(
        private readonly LoggerService $loggerService,
        private readonly GuzzleService $guzzleService,
        private readonly string        $apiToken,
    ) {

    }

    /**
     * - {@see self::areServicesUp()}
     * - {@see self::areAllProxyZonesUp()}
     *
     * In general this check should be done only once. The reason why it's doing this weird "re-check" on fail is:
     * - bright data says that they rarely if never have services down,
     * - found myself out that services were down, and actually got exceptions for this,
     * - contacted bright data support, but they didn't see anything like that,
     * - suspecting that this might be some weird hiccup or connection issue thus making the rechecks,
     *
     * Rule is, if the last check in re-check passes then assuming that everything is ok.
     *
     * @return bool
     * @throws GuzzleException
     */
    public function isProxyOk(): bool
    {
        $isOk = ($this->areServicesUp() && $this->areAllProxyZonesUp());
        if (!$isOk) {
            $reCheckOk = false;
            for ($counter = 0; $counter <= self::MAX_IS_OK_RECHECK; $counter++) {
                sleep(self::SLEEP_SECONDS_BETWEEN_RECHECK);
                $reCheckOk = ($this->areServicesUp() && $this->areAllProxyZonesUp());
            }

            return $reCheckOk;
        }

        return true;
    }

    /**
     * Quote from received E-mail:
     * - We have a proprietary website - https://lumtest.com/myip.json,
     * that should work at all times and is an accurate test to see if the services are down or not.
     *
     * @return bool
     *
     * @throws Exception
     * @throws GuzzleException
     */
    private function areServicesUp(): bool
    {
        try {
            $response = $this->guzzleService->get('https://lumtest.com/myip.json');
            $content  = $response->getBody()->getContents();

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Lumtest response is not a json! Error: " . json_last_error_msg(), Response::HTTP_SERVICE_UNAVAILABLE);
            }

            if (empty($data)) {
                throw new Exception("Lumtest response json is empty.", Response::HTTP_SERVICE_UNAVAILABLE);
            }

        } catch (Exception $e) {
            $this->loggerService->logException($e);
            return false;
        }

        return true;
    }

    /**
     * Based on E-Mail based discussion with support:
     * - I've asked about {@link https://help.brightdata.com/hc/en-us/articles/4420514904209-Get-proxy-port-status}
     *   not listing SERP,
     * - got response: "
     *      If I may, I'd suggest not wasting your time on checking whether a specific zone is down,
     *      checking the network status itself should be enough, since the zones are dependent on the service status,
     *      meaning if the service is down, the zones won't work.
     * "
     *
     * @return bool
     * @throws GuzzleException
     */
    private function areAllProxyZonesUp(): bool
    {
        try {
            $this->guzzleService->setHeaders([
                GuzzleInterface::HEADER_AUTHORIZATION => "Bearer: {$this->apiToken}",
            ]);

            $response = $this->guzzleService->get('https://api.brightdata.com/network_status/all');
            $content  = $response->getBody()->getContents();

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Network status response is not a json! Error: " . json_last_error_msg(), Response::HTTP_SERVICE_UNAVAILABLE);
            }

            if (empty($data)) {
                throw new Exception("Network status response json is empty.", Response::HTTP_SERVICE_UNAVAILABLE);
            }

            if (!array_key_exists('status', $data)) {
                throw new Exception("Network status response has no key named: status.", Response::HTTP_SERVICE_UNAVAILABLE);
            }

            $status = $data['status'];
            if (!is_bool($status)) {
                throw new Exception("Network status response key 'status' should be bool! Status value: " . json_encode($status), Response::HTTP_SERVICE_UNAVAILABLE);
            }

            if (!$status) {
                $this->loggerService->logger->critical("Network status is: FALSE (not reachable)");
            }

            return $status;
        } catch (Exception $e) {
            $this->loggerService->logException($e);
            return false;
        }
    }

}