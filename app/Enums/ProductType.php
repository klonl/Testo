<?php

namespace App\Enums;

enum ProductType: string
{
    case TOPUP = 'topup';
    case KEY = 'key';
    case SUBSCRIPTION = 'subscription';
    case GIFTCARD = 'giftcard';
}
