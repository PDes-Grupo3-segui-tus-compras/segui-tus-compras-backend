<?php

namespace App\Contracts;

use App\Models\Opinion;

interface OpinionRepositoryInterface {
    public function create(array $data): Opinion;
}
