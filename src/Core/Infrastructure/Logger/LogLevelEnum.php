<?php

namespace Inquisition\Core\Infrastructure\Logger;

enum LogLevelEnum: int
{
    case DEBUG = 7;
    case INFO = 6;
    case NOTICE = 5;
    case WARNING = 4;
    case ERROR = 3;
    case CRITICAL = 2;
    case ALERT = 1;
    case EMERGENCY = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::NOTICE => 'NOTICE',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR',
            self::CRITICAL => 'CRITICAL',
            self::ALERT => 'ALERT',
            self::EMERGENCY => 'EMERGENCY',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DEBUG => 'blue',
            self::INFO => 'green',
            self::NOTICE => 'yellow',
            self::WARNING => 'orange',
            self::ERROR, self::CRITICAL, self::ALERT, self::EMERGENCY => 'red',
        };
    }

    public function isLoggable(self $level): bool
    {
        return $this->value <= $level->value;
    }
}
