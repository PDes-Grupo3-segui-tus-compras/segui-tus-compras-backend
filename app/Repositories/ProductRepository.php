<?php

namespace App\Repositories;

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;

/*
 * Nota: Este codigo fue creado para poder emular lo que seria un Unit Test puro dejando a la funcionalidad de Active records
 * de Eloquent fuera de la ecuacion. No se realizara en todos los lugares, pero queriamos al menos dejar un ejemplo de Unit Test Puro.
 */
class ProductRepository implements ProductRepositoryInterface {
    public function firstOrCreate(array $conditions, array $data): Product {
        return Product::firstOrCreate($conditions, $data);
    }
}
