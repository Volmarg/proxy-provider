<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Throwable;

class LoggerService
{
    public function __construct(
        public readonly LoggerInterface $logger
    ) {

    }

    /**
     * @param Throwable $e
     */
    public function logException(Throwable $e): void
    {
        $this->logger->critical("Exception was thrown", [
            "message" => $e->getMessage(),
            "code"    => $e->getCode(),
            "class"   => $e::class,
        ]);
    }
}