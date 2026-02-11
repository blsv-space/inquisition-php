<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Router;

interface RouteMatchResultInterface
{
    public function getRoute(): RouteInterface;

    public function getMiddleware(): array;

    public function getParameters(): array;

    public function getParameter(string $name, ?string $default = null): ?string;

    public function getRouteName(): ?string;

    public function hasParameter(string $name): bool;

    public function getPath(): string;

    public function getMethods(): array;
}
