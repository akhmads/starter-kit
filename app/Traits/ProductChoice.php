<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use App\Models\Product;

trait ProductChoice
{
    public Collection $productChoice;

    public function mountProductChoice()
    {
        $this->searchProduct();
    }

    public function searchProduct(string $value = ''): void
    {
        $selected = Product::where('id', intval($this->product_id ?? ''))->get();
        $this->productChoice = Product::query()
            ->filterLike('name', $value)
            ->take(20)
            ->orderBy('name')
            ->get()
            ->merge($selected);
    }
}
