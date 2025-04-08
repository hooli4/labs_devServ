<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\ChangePermissionRequest;

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

        Permission::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'created_at' => Carbon::now(),
            'created_by' => $user->id,
        ]);

        return response()->json(['message' => 'Permission was created successfully'], 201);
    }

    public function updatePermission(ChangePermissionRequest $request) {
        if (!in_array('UP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "update-permission"'],403);
        }

        $permission = Permission::find($request->id);

        if ($permission) {
            $permission->name = $request->name;
            $permission->code = $request->code;
            $permission->description = $request->description;
            $permission->save();
            return response()->json(['message' => 'Permission was changed successfully']);
        }

        return response()->json(['message' => 'Permission is not found'], 404);


    }

    public function deletePermission($id) {
        if (!in_array('DP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-permission"'],403);
        }

        $permission = Permission::find($id);

        if ($permission) {
            $permission->forceDelete();
            return response()->json(['message'=> 'Permission was deleted fully and successfully']);
        }

        return response()->json(['message' => 'Permission is not found'], 404);

    }

    public function softDeletePermission($id) {
        if (!in_array('DP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-permission"'],403);
        }

        $permission = Permission::find($id);

        if ($permission) {
            $permission->deleted_by = Auth::user()->id;
            $permission->save();
            $permission->delete();
            return response()->json(['message'=> 'Permission was deleted softly and successfully']);
        }

        return response()->json(['message'=> 'Permission is not found'], 404);
    }

    public function restorePermission($id) {
        if (!in_array('RestoreP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "restore-permission"'],403);
        }

        $permission = Permission::withTrashed()->find($id);

        if ($permission) {
            $permission->deleted_by = null;
            $permission->save();
            $permission->restore();
            return response()->json(['message'=> 'Permission was restored successfully']);
        }

        return response()->json(['message' => 'Permission is not found'], 404);
    }
}
