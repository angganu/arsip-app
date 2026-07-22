<?php

namespace App\Http\Controllers;

use App\Models\MstDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        return view('profile.edit', [
            'user' => $user,
            'departments' => MstDepartment::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('base_users', 'email')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'mst_department_id' => ['nullable', 'exists:mst_departments,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $profileData = [
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'mst_department_id' => $validated['mst_department_id'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->profile?->avatar_path) {
                Storage::disk('public')->delete($user->profile->avatar_path);
            }

            $profileData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->profile()->updateOrCreate(
            ['base_user_id' => $user->id],
            $profileData
        );

        return redirect()->route('profile.edit')
            ->with('status', 'Profile berhasil diperbarui.');
    }

    public function editPassword(): View
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        $request->session()->regenerate();

        return redirect()->route('password.edit')
            ->with('status', 'Password berhasil diperbarui.');
    }
}
