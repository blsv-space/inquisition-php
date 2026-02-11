<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\RateLimit;

interface RateLimitIdentifierInterface
{
    public function getIdentifier(): string;
}
