<?php

namespace Inquisition\Foundation\Singleton;

interface SingletonInterface
{
    public static function getInstance(): self;
}