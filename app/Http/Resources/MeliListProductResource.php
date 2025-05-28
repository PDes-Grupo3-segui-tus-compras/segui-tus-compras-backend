<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeliListProductResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'image' => !empty($this['pictures']) ? $this['pictures'][0]['url'] : null,
        ];
    }
}
