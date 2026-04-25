<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::orderBy('name')->paginate(50),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['member', 'instructor', 'admin'])],
            'rank' => ['nullable', 'string', 'max:64'],
        ]);
        $user->update($data);
        return response()->json(['data' => $user]);
    }
}
