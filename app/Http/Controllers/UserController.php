<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
Use Illuminate\Support\Facades\Auth;
use App\Models\UserAndRole;
use Carbon\Carbon;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function getUsersList() {

        if (!in_array('GLU', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "get-list-user"'],403);
        }

        $users = User::all(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function getUserRoles(Request $request) {

        if (!in_array('readUR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "read-userRole"'],403);
        }

        if (User::find($request->user_id)) {
            $role_ids = UserAndRole::where('user_id', $request->user_id)->pluck('role_id')->toArray();
            $roles = Role::whereIn('id', $role_ids)->pluck('name')->toArray();
            return response()->json($roles);
        }
        return response()->json(['message'=> 'User is not found'], 404);

    }

    public function setUserRole(Request $request) {

        if (!in_array('CUR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "create-userRole"'],403);
        }

        $user_role = UserAndRole::where('user_id', $request->user_id)->where('role_id', $request->role_id)->first();
        if ($user_role) {
            return response()->json(['message' => 'User already has this role']);
        }


        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        $role = Role::where('id', $request->role_id)->first();

        if (!$role) {
            return response()->json(['message' => 'Role is not found'], 404);
        }

        $user->roles()->attach($role->id, [
            'created_at' => Carbon::now(),
            'created_by' => Auth::user()->id,
        ]);

        return response()->json(['message'=> 'Role was set successfully']);

    }

    public function deleteUserRole(Request $request) {

        if (!in_array('DUR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-userRole"'],403);
        }

        if (!User::find($request->user_id)) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        if (!Role::find($request->role_id)) {
            return response()->json(['message'=> 'Role is not found'], 404);
        }

        $user_role = UserAndRole::where('user_id', $request->user_id)->where('role_id', $request->role_id)->first();
        if ($user_role) {
            $user_role->forceDelete();
            return response()->json(['message'=> 'User`s Role was deleted successfully']);
        }

        return response()->json(['message' => 'This user and role are not connected']);
    }

    public function softDeleteUserRole(Request $request) {

        if (!in_array('DUR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-userRole"'],403);
        }

        if (!User::find($request->user_id)) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        if (!Role::find($request->role_id)) {
            return response()->json(['message'=> 'Role is not found'], 404);
        }

        $user_role = UserAndRole::where('user_id', $request->user_id)->where('role_id', $request->role_id)->first();
        if ($user_role) {
            $user_role->deleted_by = Auth::user()->id;
            $user_role->save();
            $user_role->delete();
            return response()->json(['message'=> 'User`s role was deleted softly and successfully']);
        }

        return response()->json(['message' => 'This user and role are not connected']);
    }

    public function restoreUserRole(Request $request) {

        if (!in_array('restoreUR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "restore-userRole"'],403);
        }

        if (!User::find($request->user_id)) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        if (!Role::find($request->role_id)) {
            return response()->json(['message'=> 'Role is not found'], 404);
        }

        $user_role = UserAndRole::withTrashed()->where('user_id', $request->user_id)->where('role_id', $request->role_id)->first();
        if ($user_role) {
            if ($user_role->deleted_by === null) return response()->json(['message' => 'User`s role is already restored or wasn`t deleted']);
            $user_role->deleted_by = null;
            $user_role->save();
            $user_role->restore();
            return response()->json(['message'=> 'User`s role was restored']);
        }

        return response()->json(['message'=> 'This user and role are not connected']);
    }

}
