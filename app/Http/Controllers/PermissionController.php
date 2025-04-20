<?php

namespace App\Http\Controllers;


use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\ChangePermissionRequest;
use App\DTOS\PermissionDTO;

class PermissionController extends Controller
{
    public function getPermissionsList() {

        if (!in_array('GLP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "get-list-permission"'],403);
        }

        $permissions = Permission::all();

        return response()->json($permissions);
    }

    public function getPermission($id) {

        if (!in_array('RP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "read-permission"'],403);
        }

        $permission = Permission::find($id);

        if ($permission) {
            return response()->json($permission);
        }

        return response()->json(['message' => 'Permission is not found'], 404);
    }

    public function createPermission(CreatePermissionRequest $request) {
        if (!in_array('CP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "create-permission"'],403);
        }
        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            try {
                Permission::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'code' => $request->code,
                    'created_at' => Carbon::now(),
                    'created_by' => $user->id,
                ]);
            }
            catch (\Exception $e) {
                throw $e;
            }
        });
        

        return response()->json(['message' => 'Permission was created successfully'], 201);
    }

    public function updatePermission(ChangePermissionRequest $request) {
        if (!in_array('UP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "update-permission"'],403);
        }

        $permission = Permission::find($request->id);

        if (!$permission) {
            return response()->json(['message' => 'Permission is not found'], 404);
        }

        $validated = [
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
        ];

        $permissionDTO = PermissionDTO::fromArray($validated);
        DB::transaction(function () use ($permissionDTO, $permission) {
            try {
                if (!is_null($permissionDTO->name)) {
                    $permission->name = $permissionDTO->name;
                }

                if ($permissionDTO->description !== null) {
                    $permission->description = $permissionDTO->description;
                }

                if ($permissionDTO->code !== null) {
                    $permission->code = $permissionDTO->code;
                }

                $permission->save();
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message' => 'Permission was changed successfully']);
    }

    public function deletePermission($id) {
        if (!in_array('DP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-permission"'],403);
        }

        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permission is not found'], 404);
        }
        
        DB::transaction(function () use ($permission) {
            try {
                if ($permission) {
                    $permission->forceDelete();
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message'=> 'Permission was deleted fully and successfully']);

    }

    public function softDeletePermission($id) {
        if (!in_array('DP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-permission"'],403);
        }

        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['message'=> 'Permission is not found'], 404);
        }

        DB::transaction(function () use ($permission) {
            try {
                if ($permission) {
                    $permission->deleted_by = Auth::user()->id;
                    $permission->save();
                    $permission->delete();
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message'=> 'Permission was deleted softly and successfully']);
    }

    public function restorePermission($id) {
        if (!in_array('RestoreP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "restore-permission"'],403);
        }

        $permission = Permission::withTrashed()->find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permission is not found'], 404);
        }

        DB::transaction(function () use ($permission) {
            try {
                if ($permission) {
                    $permission->deleted_by = null;
                    $permission->save();
                    $permission->restore();
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message' => 'Permission was restored successfully']);
    }
}
