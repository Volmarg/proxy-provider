<?php

namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;

interface ProxyServiceInterface
{
    /**
     * @return bool
     */
    public function isProxyOk(): bool;
}