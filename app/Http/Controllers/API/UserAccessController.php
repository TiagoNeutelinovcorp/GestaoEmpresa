<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function index()
    {
        return response()->json(User::query()->with('roles')->orderBy('name')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'estado' => ['boolean'],
            'password' => ['required', Password::defaults()],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'telemovel' => $data['telemovel'] ?? null,
            'estado' => $data['estado'] ?? true,
            'password' => $data['password'],
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles([Role::findByName($data['role'])]);
        }

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'estado' => ['boolean'],
            'password' => ['nullable', Password::defaults()],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->fill(collect($data)->except(['password', 'role'])->toArray());
        $user->save();

        if (array_key_exists('role', $data)) {
            if ($data['role']) {
                $user->syncRoles([Role::findByName($data['role'])]);
            } else {
                $user->syncRoles([]);
            }
        }

        return response()->json($user->load('roles'));
    }
}
