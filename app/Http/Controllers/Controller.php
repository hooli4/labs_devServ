<?php

namespace App\Http\Controllers;
use App\Models\UserAndRole;
use App\Models\RoleAndPermission;
use App\Models\Permission;

abstract class Controller
{
    /**
     * Функция check_right возвращает массив кодов разрешений для авторизованного пользователя
     * 
     * 
     */
    public static function check_right($user_id) {
        $role_ids = UserAndRole::where('user_id', $user_id)->pluck('role_id')->toArray();
        $permission_codes = [];
        foreach ($role_ids as $role_id) {
            $permission_ids = RoleAndPermission::where('role_id', $role_id)->pluck('permission_id')->toArray();
            foreach ($permission_ids as $permission_id) {
                $permission_codes[]= Permission::find($permission_id)['code'];
            }
        }
        $permission_codes = array_unique($permission_codes);
        return $permission_codes;
    }
}
