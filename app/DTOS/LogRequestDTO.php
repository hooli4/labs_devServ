<?php

namespace App\DTOS;

class LogRequestDTO {
    public function __construct(
        public readonly string $URL_API_METHOD,
        public readonly string $METHOD_HTTP_REQUEST,
        public readonly string $CONTROLLER_PATH,
        public readonly string $NAME_METHOD_CONTROLLER,
        public readonly string $BODY_REQUEST,
        public readonly string $HEADERS_REQUEST,
        public readonly string $USER_ID,
        public readonly string $USER_IP_ADDRESS,
        public readonly string $USER_USER_AGENT,
        public readonly string $CODE_STATUS_RESPONSE,
        public readonly string $BODY_RESPONSE,
        public readonly string $HEADERS_RESPONSE,
        public readonly string $TIME_CALL,
    ) {
    }

    public static function fromArray(array $data) {
        return new self(
            $data['URL_API_METHOD'],
            $data['METHOD_HTTP_REQUEST'],
            $data['CONTROLLER_PATH'],
            $data['NAME_METHOD_CONTROLLER'],
            $data['BODY_REQUEST'],
            $data['HEADERS_REQUEST'],
            $data['USER_ID'],
            $data['USER_IP_ADDRESS'],
            $data['USER_USER_AGENT'],
            $data['CODE_STATUS_RESPONSE'],
            $data['BODY_RESPONSE'],
            $data['HEADERS_RESPONSE'],
            $data['TIME_CALL'],
        );
    }
}