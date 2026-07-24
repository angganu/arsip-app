<?php

use App\Models\BaseRole;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('detail planning dates must stay within the parent planning range', function () {
    $role = BaseRole::query()->firstOrCreate(['name' => 'administrator']);
    $user = User::factory()->create();
    $user->roles()->sync([$role->id]);

    $category = TaskCategory::query()->create([
        'name' => 'Test Category',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('task-masters.store'), [
        'task_category_id' => $category->id,
        'name' => 'Task with invalid detail range',
        'date_planning_start' => '2026-06-01',
        'date_planning_finish' => '2026-06-10',
        'has_schedule' => false,
        'details' => [[
            'activity' => 'Invalid detail activity',
            'date_planning_start' => '2026-05-31',
            'date_planning_finish' => '2026-06-11',
            'description' => 'Outside range',
        ]],
    ]);

    $response->assertSessionHasErrors([
        'details.0.date_planning_start',
        'details.0.date_planning_finish',
    ]);
    $response->assertStatus(302);
});
