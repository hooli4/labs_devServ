<?php

namespace App\Http\DTOs;

class ClientDTO
{
    public string $ip;
    public string $useragent;

    public function __construct(string $ip, string $useragent) {
        $this->ip = $ip;
        $this->useragent = $useragent;
    }
}