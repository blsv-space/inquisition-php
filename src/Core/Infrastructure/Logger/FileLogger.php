<?php

namespace Inquisition\Core\Infrastructure\Logger;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final class FileLogger implements LoggerInterface
{

    /** @var resource */
    private $stream;

    public LogLevelEnum $level {
        get {
            return $this->level;
        }
        set {
            $this->level = $value;
        }
    }

    protected(set) string $channel = 'app' {
        get {
            return $this->channel;
        }
    }

    private array $baseContext = [];

    /**
     * @param LogLevelEnum $level
     * @param string|null $stream A writable stream resource or path. Defaults to php://stderr
     */
    public function __construct(LogLevelEnum $level, ?string $stream = null)
    {
        $this->level = $level;
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream) && $stream !== '') {
            $opened = @fopen($stream, 'ab');
            if ($opened === false) {
                throw new RuntimeException(sprintf('Unable to open log stream: %s', $stream));
            }
            $this->stream = $opened;
        } else {
            $opened = @fopen('php://stderr', 'ab');
            if ($opened === false) {
                throw new RuntimeException('Unable to open default log stream (php://stderr)');
            }
            $this->stream = $opened;
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::EMERGENCY, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::ALERT, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::CRITICAL, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::WARNING, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevelEnum::DEBUG, $message, $context);
    }

    /**
     * @param LogLevelEnum $level
     * @param string $message
     * @param array $context
     * @return void
     * @throws DateMalformedStringException
     */
    public function log(LogLevelEnum $level, string $message, array $context = []): void
    {
        if (!$level->isLoggable($this->level)) {
            return;
        }

        $mergedContext = $this->mergeContext($context, $this->baseContext);
        $interpolated = $this->interpolate($message, $mergedContext);

        $timestamp = $this->now();
        $line = sprintf(
            "[%s] %s.%s: %s",
            $timestamp,
            $this->channel,
            $level->getLabel(),
            $interpolated
        );

        $contextPayload = $this->contextToString($mergedContext);
        if ($contextPayload !== '') {
            $line .= ' | ' . $contextPayload;
        }

        $line .= PHP_EOL;

        @fwrite($this->stream, $line);
    }

    /**
     * @param array $context
     * @return $this
     */
    public function withContext(array $context): self
    {
        $clone = clone $this;
        $clone->baseContext = $this->mergeContext($this->baseContext, $context);

        return $clone;
    }

    /**
     * @param string $channel
     * @return $this
     */
    public function channel(string $channel): self
    {
        $clone = clone $this;
        $clone->channel = $channel;

        return $clone;
    }

    /**
     * @throws DateMalformedStringException
     */
    private function now(): string
    {
        $dt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolate(string $message, array $context): string
    {
        if (str_contains($message, '{') === false) {
            return $message;
        }

        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{' . $key . '}'] = $this->scalarForMessage($value);
        }

        return strtr($message, $replacements);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function scalarForMessage(mixed $value): string
    {
        if ($value instanceof Throwable) {
            return sprintf('[%s] %s', $value::class, $value->getMessage());
        }
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return (string)$value;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }
        if (is_object($value)) {
            return sprintf('object(%s)', $value::class);
        }
        if (is_array($value)) {
            return 'array(' . count($value) . ')';
        }

        return gettype($value);
    }

    /**
     * @param array $context
     * @return string
     */
    private function contextToString(array $context): string
    {
        if ($context === []) {
            return '';
        }

        $normalized = [];
        foreach ($context as $k => $v) {
            if ($v instanceof Throwable) {
                $normalized[$k] = [
                    'type' => $v::class,
                    'message' => $v->getMessage(),
                    'code' => $v->getCode(),
                    'file' => $v->getFile(),
                    'line' => $v->getLine(),
                ];
            } else {
                $normalized[$k] = $v;
            }
        }

        $json = json_encode(
            $normalized,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
        );

        return $json === false ? '' : $json;
    }

    /**
     * Right-biased merge:
     * - scalar values from $right override $left
     * - arrays are merged recursively with right precedence
     */
    private function mergeContext(array $left, array $right): array
    {
        $result = $left;

        foreach ($right as $key => $value) {
            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = $this->mergeContext($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}