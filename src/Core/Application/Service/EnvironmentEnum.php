<?php

namespace Inquisition\Core\Application\Service;

enum EnvironmentEnum: string
{
    case PROD = 'prod';
    case DEV = 'dev';
    case TEST = 'test';

    /**
     * @param string $value
     * @return self
     */
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

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this === self::PROD;
    }

    /**
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this === self::DEV;
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return $this === self::TEST;
    }
}
