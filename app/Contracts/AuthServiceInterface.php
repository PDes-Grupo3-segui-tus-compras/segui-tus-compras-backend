<?php

namespace App\Contracts;

/*
 * Nota: Este codigo fue creado para poder emular lo que seria un Unit Test puro dejando a la funcionalidad de Active records
 * de Eloquent fuera de la ecuacion. No se realizara en todos los lugares, pero queriamos al menos dejar un ejemplo de Unit Test Puro.
 */
interface AuthServiceInterface {
    public function id(): int;
}
