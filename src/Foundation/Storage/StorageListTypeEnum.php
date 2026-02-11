<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Storage;

enum StorageListTypeEnum
{
    case All;
    case Files;
    case Directories;
}
