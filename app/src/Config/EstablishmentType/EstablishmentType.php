<?php

namespace App\Config\EstablishmentType;

enum EstablishmentType: string
{
    case Restaurant = 'restaurant';
    case Bar = 'bar';
    case BarRestaurant = 'bar-restaurant';
}