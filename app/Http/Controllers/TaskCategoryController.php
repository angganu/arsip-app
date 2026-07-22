<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\Request;

class TaskCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $categories = TaskCategory::query()
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->only('per_page'));

        return view('task-categories.index', compact('categories', 'perPage'));
    }

    public function create()
    {
        return view('task-categories.form', [
            'category' => new TaskCategory(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:191'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['code'])) {
            $data['code'] = 'TC-' . str_pad((TaskCategory::withTrashed()->max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT);
        }

        TaskCategory::create($data);

        return redirect()->route('task-categories.index')
            ->with('success', 'Document category created successfully.');
    }

    public function edit(TaskCategory $taskCategory)
    {
        return view('task-categories.form', [
            'category' => $taskCategory,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, TaskCategory $taskCategory)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:191'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['code'])) {
            $data['code'] = 'TC-' . str_pad($taskCategory->id, 3, '0', STR_PAD_LEFT);
        }

        $taskCategory->update($data);

        return redirect()->route('task-categories.index')
            ->with('success', 'Document category updated successfully.');
    }

    public function destroy(TaskCategory $taskCategory)
    {
        $taskCategory->delete();

        return redirect()->route('task-categories.index')
            ->with('success', 'Document category deleted successfully.');
    }
}
