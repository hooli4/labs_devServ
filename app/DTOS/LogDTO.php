<?php

namespace App\DTOS;

class LogDTO {
    public function __construct(
        public readonly int $id,
        public readonly string $entity_type,
        public readonly int $entity_id,
        public readonly string $field,
        public readonly string $old_value,
        public readonly string $new_value,
        public readonly string $created_at,
        public readonly int $created_by,
        ) {

    }

    public static function fromArray(Array $data) {
        return new self(
            $data['id'],
            $data['entity_type'],
            $data['entity_id'],
            $data['field'],
            $data['old_value'],
            $data['new_value'],
            $data['created_at'],
            $data['created_by'],
        );
    }

}