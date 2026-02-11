<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http;

enum HttpSchema: string
{
    case HTTP = 'http';
    case HTTPS = 'https';

}
