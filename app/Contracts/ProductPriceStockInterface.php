<?php

namespace App\Contracts;

interface ProductPriceStockInterface
{
    public function price();

    public function stock();

    public function finalPrice();
}
