<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Service;

enum EnvironmentEnum: string
{
    case PROD = 'prod';
    case DEV = 'dev';
    case TEST = 'test';

    public static function fromString(string $value): self
    {
        $environmentEnum = self::tryFrom($value);
        if ($environmentEnum) {
            return $environmentEnum;
        }

        return match ($value) {
            'development' => self::DEV,
            'test' => self::TEST,
            default => self::PROD,
        };
    }

    public function isProduction(): bool
    {
        return $this === self::PROD;
    }

    public function isDevelopment(): bool
    {
        return $this === self::DEV;
    }

    public function isTest(): bool
    {
        return $this === self::TEST;
    }
}
