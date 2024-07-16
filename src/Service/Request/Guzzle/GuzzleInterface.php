<?php

namespace App\Service\Request\Guzzle;

interface GuzzleInterface
{
    public const KEY_JSON_REQUEST_BODY = "json";
    public const KEY_HEADERS = "headers";
    public const HEADER_CONNECTION = "Connection";
    public const HEADER_AUTHORIZATION = "Authorization";

    /**
     * Will close the connection once it's done handling it, otherwise the connection is kept as long as,
     * it won't time out, thus it will have impact on the performance.
     *
     * {@link https://github.com/guzzle/guzzle/issues/1348#issuecomment-167875149}
     */
    public const CONNECTION_TYPE_CLOSE = "close";
}