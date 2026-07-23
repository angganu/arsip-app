<?php

namespace App\Http\Controllers;

use App\Models\TaskMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskDiscussionController extends Controller
{
    public function index(Request $request, TaskMaster $taskMaster): View
    {
        $user = $request->user();
        $roleNames = $user?->roles()->pluck('name') ?? collect();
        $isManager = $roleNames->contains('manager');
        $isAdministrator = $roleNames->contains('administrator');

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to access this discussion.');
        }

        if ($isAdministrator && ! $isManager && (int) $taskMaster->planned_by !== (int) $user->id) {
            abort(403, 'You can only access discussions for your own tasks.');
        }

        $taskMaster->load([
            'category',
            'discussions' => function ($query) {
                $query->with(['user.roles'])->orderBy('created_at', 'asc');
            },
        ]);

        return view('task-masters.discussion', [
            'taskMaster' => $taskMaster,
        ]);
    }

    public function store(Request $request, TaskMaster $taskMaster): RedirectResponse
    {
        $user = $request->user();
        $roleNames = $user?->roles()->pluck('name') ?? collect();
        $isManager = $roleNames->contains('manager');
        $isAdministrator = $roleNames->contains('administrator');

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to post in this discussion.');
        }

        if ($isAdministrator && ! $isManager && (int) $taskMaster->planned_by !== (int) $user->id) {
            abort(403, 'You can only post in discussions for your own tasks.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $taskMaster->discussions()->create([
            'base_user_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        return redirect()->route('task-masters.discussion.index', $taskMaster)
            ->with('success', 'Message sent.');
    }
}
