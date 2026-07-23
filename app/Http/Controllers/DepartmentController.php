<?php

namespace App\Http\Controllers;

use App\Models\MstDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('per_page', 10), [5, 10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $keyword = trim((string) $request->input('keyword', ''));
        $sortBy = $request->input('sort_by', 'latest');

        $query = MstDepartment::query()->withCount('userProfiles');

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%");
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

        $departments = $query->paginate($perPage)
            ->appends($request->only(['per_page', 'keyword', 'sort_by']));

        return view('departments.index', compact('departments', 'perPage', 'keyword', 'sortBy'));
    }

    public function create(): View
    {
        return view('departments.form', [
            'department' => new MstDepartment(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:25', 'unique:mst_departments,code'],
            'name' => ['required', 'string', 'max:255', 'unique:mst_departments,name'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = 'DPT-' . str_pad((MstDepartment::query()->max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT);
        }

        MstDepartment::query()->create($data);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(MstDepartment $department): View
    {
        return view('departments.form', [
            'department' => $department,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, MstDepartment $department): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:25', 'unique:mst_departments,code,' . $department->id],
            'name' => ['required', 'string', 'max:255', 'unique:mst_departments,name,' . $department->id],
        ]);

        $department->update($data);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(MstDepartment $department): RedirectResponse
    {
        if ($department->userProfiles()->exists()) {
            return redirect()->route('departments.index')
                ->withErrors(['department' => 'Department cannot be deleted because it is used by one or more users.']);
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
