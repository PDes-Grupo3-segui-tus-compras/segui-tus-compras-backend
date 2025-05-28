<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;

/*
 * Nota: Este codigo fue creado para poder emular lo que seria un Unit Test puro dejando a la funcionalidad de Active records
 * de Eloquent fuera de la ecuacion. No se realizara en todos los lugares, pero queriamos al menos dejar un ejemplo de Unit Test Puro.
 */
class AuthService implements AuthServiceInterface {
    public function id(): int {
        return Auth::id();
    }
}
