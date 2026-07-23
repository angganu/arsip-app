<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\Request;

class TaskCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 5), [5, 10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 5)
            : 5;

        $keyword = trim((string) $request->input('keyword', ''));
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'latest');

        $query = TaskCategory::query();

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('is_active', $status === 'active');
        }

        if ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('name', 'asc');
        }

        $categories = $query->paginate($perPage)
            ->appends($request->only(['per_page', 'keyword', 'status', 'sort_by']));

        return view('task-categories.index', compact('categories', 'perPage', 'keyword', 'status', 'sortBy'));
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
            ->with('success', __('texts.success_category_created'));
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
            ->with('success', __('texts.success_category_updated'));
    }

    public function destroy(TaskCategory $taskCategory)
    {
        $taskCategory->delete();

        return redirect()->route('task-categories.index')
            ->with('success', __('texts.success_category_deleted'));
    }
}
