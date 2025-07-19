<?php

namespace Inquisition\Foundation\Factory;

interface FactoryInterface
{
    public function create(array $parameters = []);
}