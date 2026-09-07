<?php

namespace App\Enums;

enum ProductKeyStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case USED = 'used';
}
