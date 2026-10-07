<?php

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('course index can show courses', function () {
    Course::create([
        'name' => 'PHP Fundamentals',
        'description' => 'Learn the basics of PHP.',
        'duration' => 8,
        'fee' => 199.99,
        'difficulty' => 'Easy',
        'is_active' => true,
    ]);

    $response = $this->get('/courses');

    $response->assertStatus(200)
        ->assertSee('PHP Fundamentals')
        ->assertSee('Easy');
});

test('course can be created', function () {
    $response = $this->post('/courses', [
        'name' => 'Laravel Basics',
        'description' => 'Intro to Laravel.',
        'duration' => 12,
        'fee' => 249.50,
        'difficulty' => 'Medium',
        'is_active' => '1',
    ]);

    $response->assertRedirect('/courses');
    $this->assertDatabaseHas('courses', [
        'name' => 'Laravel Basics',
        'difficulty' => 'Medium',
        'is_active' => 1,
    ]);
});

test('course validation rejects invalid data', function () {
    $response = $this->from('/courses/create')->post('/courses', [
        'name' => '',
        'description' => 'Missing required fields.',
        'duration' => 0,
        'fee' => -1,
        'difficulty' => 'Unknown',
        'is_active' => 'yes',
    ]);

    $response->assertSessionHasErrors(['name', 'duration', 'fee', 'difficulty']);
});
