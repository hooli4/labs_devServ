<?php

namespace App\DTOS;

class PermissionDTO {
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $description,
        public readonly ?string $code
    ) {

    }

    public static function fromArray(array $data) {
        return new self(
            $data['name'],
            $data['description'],
            $data['code']
        );
    }

}