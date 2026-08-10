<?php

namespace Inquisition\Core\Infrastructure\Http\Router\Exception;

interface ExceptionWithErrors
{
    public function getErrors(): array;
}