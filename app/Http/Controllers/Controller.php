<?php

namespace App\Http\Controllers;
use App\Models\UserAndRole;
use App\Models\RoleAndPermission;
use App\Models\Permission;
use App\Models\ChangeLog;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

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

                $permission_code = Permission::find($permission_id);
                if ($permission_code) {
                    $permission_codes[] = $permission_code['code'];
                }
            }
        }
        $permission_codes = array_unique($permission_codes);
        return $permission_codes;
    }

    /**
     *  getBackToLog - Процедура возврата записей сущности к записям из логов
     *  entity_type - тип сущности (user, role, permission)
     *  entity_id - id сущности
     *  log_to_back_id - id лога, до которого нужно вернуть записи
     *  delete_logs - значение булеан, в зависимости от которого удаляются логи, id которых выше log_to_back_id, по умолчанию true
     */

    public static function getBackToLog($entity_type, $entity_id, $log_to_back_id, $delete_logs=false) {
        $save_all_actions = false;
        $delete_all = false;

       

        $date_to_group = ChangeLog::find($log_to_back_id)->created_at;
        $log_to_back_id = ChangeLog::where('entity_type', $entity_type)->
        where('created_at', $date_to_group)->
        OrderBy('created_at', 'desc')->
        OrderBy('id', 'desc')->
        first()->id;

        $entity_logs = ChangeLog::where('entity_type', $entity_type)->
        where('entity_id', $entity_id)->
        where('id', '<=', $log_to_back_id)->get()->toArray();

        /**
         * определяем сущность модели, если сущность была удалена из таблицы, то при
         * попытке возврата будет создаваться новая сущность и принимать те же значения
         * удаленной сущности 
         * 
         */

        if ($entity_type == 'App\Models\User') {
            $entity = User::find($entity_id); 
            if (!$entity) {

                $entity_logs_created = ChangeLog::where('entity_type', $entity_type)->
                where('entity_id', $entity_id)->
                where('old_value', 'created')->get()->toArray();

                foreach ($entity_logs_created as $log_created) {
                    $field = $log_created['field'];
                    if ($field == 'name') $name = $log_created['new_value'];
                    elseif ($field == 'password') $password = $log_created['new_value'];
                    elseif ($field == 'email') $email = $log_created['new_value'];
                    elseif ($field == 'birthday') $birthday = $log_created['new_value'];
                }

                $entity = User::create([
                    'name' => $name,
                    'password' => $password,
                    'email' => $email,
                    'birthday' => $birthday,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $delete_all = true;
                $save_all_actions = true;
            }
        }

        if ($entity_type == 'App\Models\Role') {
            $entity = Role::find($entity_id);
            if (!$entity) {

                $entity_logs_created = ChangeLog::where('entity_type', $entity_type)->
                where('entity_id', $entity_id)->
                where('old_value', 'created')->get()->toArray();

                foreach ($entity_logs_created as $log_created) {
                    $field = $log_created['field'];
                    if ($field == 'name') $name = $log_created['new_value'];
                    elseif ($field == 'description') $description = $log_created['new_value'];
                    elseif ($field == 'code') $code = $log_created['new_value'];
                }

                $entity = Role::create([
                    'name' => $name,
                    'description' => $description,
                    'code' => $code,
                    'created_at' => Carbon::now(),
                    'created_by' => 0,
                ]);

                $delete_all = true;
                $save_all_actions = true;
            }
        }

        if ($entity_type == 'App\Models\Permission') {
            $entity = Permission::find($entity_id);
            if (!$entity) {

                $entity_logs_created = ChangeLog::where('entity_type', $entity_type)->
                where('entity_id', $entity_id)->
                where('old_value', 'created')->get()->toArray();

                foreach ($entity_logs_created as $log_created) {
                    $field = $log_created['field'];
                    if ($field == 'name') $name = $log_created['new_value'];
                    elseif ($field == 'description') $description = $log_created['new_value'];
                    elseif ($field == 'code') $code = $log_created['new_value'];
                }

                $entity = Permission::create([
                    'name' => $name,
                    'description' => $description,
                    'code' => $code,
                    'created_at' => Carbon::now(),
                    'created_by' => 0,
                ]);
                
                $delete_all = true;
                $save_all_actions = true;
            }
        }

        foreach ($entity_logs as $log) {
            if ($log['new_value'] != 'deleted') {
                $field = $log['field'];
                $entity["$field"] = $log['new_value'];
            }

            if($save_all_actions) $entity->save();
        }

        if ($delete_logs) {
            $entity_logs_to_delete = ChangeLog::where('entity_type', $entity_type)->
            where('entity_id', $entity_id)->
            where('id', '>', $log_to_back_id)->get();

            if ($delete_all) {
                $entity_logs_to_delete = ChangeLog::where('entity_type', $entity_type)->
                where('entity_id', $entity_id)->get();
            }

            foreach ($entity_logs_to_delete as $log_to_delete) {
                $log_to_delete->forceDelete();
            }
        }

        if (!$save_all_actions) $entity->save();

    }
}
