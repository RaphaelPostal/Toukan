<?php

namespace App\Config;

enum QrCodeTemplate : int
{
    case BASIC = 1;
    case DOT = 2;
    case ROUNDED = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::BASIC => 'basique',
            self::DOT => 'pointillés',
            self::ROUNDED => 'arrondis',
        };
    }

    public function getDotsOption(): string
    {
        return match ($this) {
            self::BASIC => 'square',
            self::DOT => 'dots',
            self::ROUNDED => 'rounded',
        };
    }

    public function getCornerSquareOption(): string
    {
        return match ($this) {
            self::BASIC => 'square',
            self::DOT => 'extra-rounded',
            self::ROUNDED => 'extra-rounded',
        };
    }

    public function getCornerDotOption(): string
    {
        return match ($this) {
            self::BASIC => 'square',
            self::DOT => 'dot',
            self::ROUNDED => 'dot',
        };
    }
}