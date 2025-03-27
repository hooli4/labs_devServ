<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\DTOs\ClientDTO;

class MainController extends Controller
{
    /*
    Метод server_info относится к роуту /server, возвращает данные об установленной версии PHP
    */
    public function server_info() {
        return phpinfo();
    }

    /*
    Метод client_info относится к роуту /client, возвращает данные о пользователе,
    перешедшему по роуту, в формате JSON
    */
    public function client_info(Request $request) {
        $client = new ClientDTO($request->ip(), $request->userAgent());

        $client_to_json = json_encode(['ip' => "$client->ip", 'useragent' => "$client->useragent"]);

        return "$client_to_json";

    }

    /*
    Метод database_info относится к роуту /database, возвращает данные об
    используемой базе данных в формате JSON
    */

    public function database_info() {
        return config('database.connections.'. config('database.default'));
    }
}