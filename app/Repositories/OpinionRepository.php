<?php

namespace App\Repositories;

use App\Contracts\OpinionRepositoryInterface;
use App\Models\Opinion;

class OpinionRepository implements OpinionRepositoryInterface {
    public function create(array $data): Opinion {
        return Opinion::create($data);
    }
}
