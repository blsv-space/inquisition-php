<?php

namespace Inquisition\Core\Infrastructure\Http\RateLimit;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;

interface RateLimitIdentifierInterface
{
    public function getIdentifier(): string;
}