<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

enum QueryOperatorEnum: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUALS = '<=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUALS = '>=';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
}
