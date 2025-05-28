<?php

namespace App\Http\Controllers;
    /**
     * @OA\Info(title="Segui Tus Compras", version="0.5.0")
     * @OA\Server(url="http://localhost:8000")
     * @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         type="http",
     *         scheme="bearer",
     *         bearerFormat="JWT"
     *  )
     */
abstract class Controller
{

}
