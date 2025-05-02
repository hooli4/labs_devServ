<?php

namespace App\DTOS;

use Illuminate\Support\Collection;

class RoleCollectionDTO {
    public function __construct(
        array $data) {
            $this->data = $data;
    }

    public static function fromCollection(Collection $data) {
        return new self($data->toArray());
    }

}