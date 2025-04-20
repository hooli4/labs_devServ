<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeRoleRequest;
use App\Models\Role;
use App\Http\Requests\CreateRoleRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\RoleAndPermission;
use Illuminate\Support\Facades\DB;
use App\DTOS\RoleDTO;

class RoleController extends Controller
{
    public function getRolesList() {

        if (!in_array('GLR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "get-list-role"'],403);
        }

        $roles = Role::all();

        return response()->json($roles);
    }

    public function getRole($id) {

        if (!in_array('ReadR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "read-role"'],403);
        }

        $role = Role::find($id);

        if ($role) {
            return response()->json($role);
        }

        return response()->json(['message' => 'Role is not found'], 404);
    }

    public function createRole(CreateRoleRequest $request) {

        if (!in_array('CR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "create-role"'],403);
        }

        $user = Auth::user();
        DB::transaction(function () use ($request, $user) {
            try {
                Role::create([
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

        return response()->json(['message' => 'Role was created successfully'], 201);
    }

    public function updateRole(ChangeRoleRequest $request) {

        if (!in_array('UR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "update-role"'],403);
        }

        $role = Role::find($request->id);

        if (!$role) {
            return response()->json(['message' => 'Role is not found'], 404);
        }

        $validated = [
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
        ];

        $roleDTO = RoleDTO::fromArray($validated);

        DB::transaction(function () use ($roleDTO, $role) {
            try {
                if ($roleDTO->name !== null) {
                    $role->name = $roleDTO->name;
                }

                if ($roleDTO->description !== null) {
                    $role->description = $roleDTO->description;
                }

                if ($roleDTO->code !== null) {
                    $role->code = $roleDTO->code;
                }

                $role->save();
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message' => 'Role was changed successfully']);
    }

    public function deleteRole($id) {

        if (!in_array('DR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-role"'],403);
        }

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role is not found'], 404);
        }

        DB::transaction(function () use ($role) {
            try {
                if ($role) {
                    $role->forceDelete();
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message' => 'Role was deleted fully and successfully']);
    }

    public function softDeleteRole($id) {

        if (!in_array('DR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-role"'],403);
        }

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message'=> 'Role is not found'], 404);
        }

        DB::transaction(function () use ($role) {
            try {
                if ($role) {
                    $role->deleted_by = Auth::user()->id;
                    $role->save();
                    $role->delete();
                }        
            }
            catch (\Exception $e) {
                throw $e;
            }
        });
        
        return response()->json(['message'=> 'Role was deleted softly and successfully']);
    }

    public function restoreRole($id) {

        if (!in_array('RestoreR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "restore-role"'],403);
        }

        $role = Role::withTrashed()->find($id);

        if (!$role) {
        return response()->json(['message' => 'Role is not found'], 404);
        }

        DB::transaction(function () use ($role) {
            try {
                if ($role) {
                    $role->deleted_by = null;
                    $role->save();
                    $role->restore();
                }     
            }
            catch (\Exception $e) {
                throw $e;
            }
        });

        return response()->json(['message'=> 'Role was restored successfully']);
    }

    public function ConnectRoleAndPermission(Request $request) {
        if (!in_array('CRP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "connect-RolePermission"'],403);
        }
        
        $roleAndPermission = RoleAndPermission::where('role_id', $request->role_id)->where('permission_id', $request->permission_id)->first();

        if ($roleAndPermission) {
            return response()->json(['message'=> 'This role and permission are already connected']);
        }

        $role = Role::find($request->role_id);
        if (!$role) return response()->json(['message'=> 'Role is not found'],404);

        $permission = Permission::find($request->permission_id);
        if (!$permission) return response()->json(['message'=> 'Permission is not found'], 404);

        $role->permissions()->attach($request->permission_id, [
            'created_at' => Carbon::now(),
            'created_by' => Auth::user()->id,
        ]);

        return response()->json(['message' => 'Connection between role and permission was set successfully']);

    }

    public function DeleteConnectRoleAndPermission(Request $request) {
        if (!in_array('DCRP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "deleteConnect-RolePermission"'],403);
        }

        $role = Role::find($request->role_id);
        if (!$role) return response()->json(['message'=> 'Role is not found'],404);

        $permission = Permission::find($request->permission_id);
        if (!$permission) return response()->json(['message'=> 'Permission is not found'], 404);

        $RoleAndPermission = RoleAndPermission::where('role_id', $request->role_id)->where('permission_id', $request->permission_id)->first();

        if ($RoleAndPermission) {
            $RoleAndPermission->forceDelete();
            return response()->json(['message' => 'Connection between role and permission was deleted successfully']);
        }

        return response()->json(['message'=> 'This role and permission are not connected']);
    }

    public function SoftDeleteConnectRoleAndPermission(Request $request) {
        if (!in_array('DCRP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "deleteConnect-RolePermission"'],403);
        }

        $role = Role::find($request->role_id);
        if (!$role) return response()->json(['message'=> 'Role is not found'],404);

        $permission = Permission::find($request->permission_id);
        if (!$permission) return response()->json(['message'=> 'Permission is not found'], 404);

        $RoleAndPermission = RoleAndPermission::where('role_id', $request->role_id)->where('permission_id', $request->permission_id)->first();

        if ($RoleAndPermission) {
            $RoleAndPermission->deleted_by = Auth::user()->id;
            $RoleAndPermission->save();
            $RoleAndPermission->delete();
            return response()->json(['message' => 'Connection between role and permission was deleted softly and successfully']);
        }

        return response()->json(['message'=> 'This role and permission are not connected']);
    }

    public function RestoreConnectRoleAndPermission(Request $request) {
        if (!in_array('RCRP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "restoreConnect-RolePermission"'],403);
        }

        $role = Role::find($request->role_id);
        if (!$role) return response()->json(['message'=> 'Role is not found'],404);

        $permission = Permission::find($request->permission_id);
        if (!$permission) return response()->json(['message'=> 'Permission is not found'], 404);

        $RoleAndPermission = RoleAndPermission::withTrashed()->where('role_id', $request->role_id)->where('permission_id', $request->permission_id)->first();

        if ($RoleAndPermission) {
            $RoleAndPermission->deleted_by = null;
            $RoleAndPermission->save();
            $RoleAndPermission->restore();
            return response()->json(['message' => 'Connection between role and permission was restored successfully']);
        }

        return response()->json(['message'=> 'This role and permission are not connected']);
    }

}
