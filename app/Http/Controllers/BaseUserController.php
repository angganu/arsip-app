<?php

namespace App\Http\Controllers;

use App\Models\BaseRole;
use App\Models\MstDepartment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BaseUserController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('per_page', 10), [5, 10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $keyword = trim((string) $request->input('keyword', ''));
        $role = trim((string) $request->input('role', ''));
        $departmentId = (int) $request->input('department_id', 0);
        $sortBy = $request->input('sort_by', 'latest');

        $query = User::query()
            ->with(['roles', 'profile.department']);

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (in_array($role, ['manager', 'administrator'], true)) {
            $query->whereHas('roles', function ($builder) use ($role) {
                $builder->where('name', $role);
            });
        }

        if ($departmentId > 0) {
            $query->whereHas('profile', function ($builder) use ($departmentId) {
                $builder->where('mst_department_id', $departmentId);
            });
        }

        if ($sortBy === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sortBy === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('name', 'asc');
        }

        $users = $query->paginate($perPage)
            ->appends($request->only(['per_page', 'keyword', 'role', 'department_id', 'sort_by']));

        return view('base-users.index', [
            'users' => $users,
            'perPage' => $perPage,
            'keyword' => $keyword,
            'role' => $role,
            'departmentId' => $departmentId,
            'sortBy' => $sortBy,
            'roles' => BaseRole::query()->orderBy('name')->get(),
            'departments' => MstDepartment::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('base-users.form', [
            'baseUser' => new User(),
            'mode' => 'create',
            'selectedRole' => 'manager',
            'roles' => BaseRole::query()->orderBy('name')->get(),
            'departments' => MstDepartment::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:base_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::exists('base_roles', 'name')],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'mst_department_id' => ['nullable', 'exists:mst_departments,id'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $roleId = BaseRole::query()->where('name', $data['role'])->value('id');
        if ($roleId) {
            $user->roles()->sync([$roleId]);
        }

        $user->profile()->updateOrCreate(
            ['base_user_id' => $user->id],
            [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'mst_department_id' => $data['mst_department_id'] ?? null,
            ]
        );

        return redirect()->route('base-users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $baseUser): View
    {
        return view('base-users.form', [
            'baseUser' => $baseUser->load(['roles', 'profile']),
            'mode' => 'edit',
            'selectedRole' => $baseUser->roles()->value('name') ?? 'manager',
            'roles' => BaseRole::query()->orderBy('name')->get(),
            'departments' => MstDepartment::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $baseUser): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('base_users', 'email')->ignore($baseUser->id),
            ],
            'role' => ['required', Rule::exists('base_roles', 'name')],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'mst_department_id' => ['nullable', 'exists:mst_departments,id'],
        ]);

        $baseUser->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $roleId = BaseRole::query()->where('name', $data['role'])->value('id');
        if ($roleId) {
            $baseUser->roles()->sync([$roleId]);
        }

        $baseUser->profile()->updateOrCreate(
            ['base_user_id' => $baseUser->id],
            [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'mst_department_id' => $data['mst_department_id'] ?? null,
            ]
        );

        return redirect()->route('base-users.index')
            ->with('success', 'User updated successfully.');
    }

    public function editPassword(User $baseUser): View
    {
        return view('base-users.password', ['baseUser' => $baseUser]);
    }

    public function updatePassword(Request $request, User $baseUser): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $baseUser->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('base-users.index')
            ->with('success', 'User password updated successfully.');
    }

    public function destroy(Request $request, User $baseUser): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $baseUser->id) {
            return redirect()->route('base-users.index')
                ->withErrors(['base_user' => 'You cannot delete your own account.']);
        }

        $baseUser->delete();

        return redirect()->route('base-users.index')
            ->with('success', 'User deleted successfully.');
    }
}
