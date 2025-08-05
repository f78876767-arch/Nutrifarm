<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return Role::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);
        $role = Role::create(['name' => $request->name]);
        return response()->json($role, 201);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);
        $user = User::findOrFail($request->user_id);
        $user->roles()->syncWithoutDetaching([$request->role_id]);
        return response()->json(['message' => 'Role assigned.']);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);
        $user = User::findOrFail($request->user_id);
        $user->roles()->detach($request->role_id);
        return response()->json(['message' => 'Role removed.']);
    }
}
