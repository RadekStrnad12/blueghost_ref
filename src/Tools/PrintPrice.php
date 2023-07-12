<?php
namespace App\Tools;

class PrintPrice
{
    static function printPrice($price)
    {
        return ($price / 100) . ' Kč';
    }
}