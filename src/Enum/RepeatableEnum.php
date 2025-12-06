<?php

namespace App\Enum;

enum RepeatableEnum
{
    case Daily;

    case Weekly;

    case Monthly;

    case Yearly;

    public function r()
    {
        // TODO: Implement r() method.
    }
}
