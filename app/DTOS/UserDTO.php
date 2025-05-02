<?php

namespace App\DTOS;

class UserDTO {
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $birthday,
    ) {
    }

    public static function fromArray(array $data) {
        return new self(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['password'],
            $data['birthday'],
        );
    }
}