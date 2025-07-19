<?php

namespace Inquisition\Core\Infrastructure\Http;

enum HttpSchema: string
{
    case HTTP = 'http';
    case HTTPS = 'https';

}