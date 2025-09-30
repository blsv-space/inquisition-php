<?php

namespace Inquisition\Core\Infrastructure\Http\RateLimit;

interface RateLimitIdentifierInterface
{
    public function getIdentifier(): string;
}