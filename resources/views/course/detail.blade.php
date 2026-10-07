@extends('layouts.app')

@section('title', 'Course Details')

@section('content')
    <h1>Course Details</h1>

    <p><strong>ID:</strong> {{ $course->id }}</p>
    <p><strong>Name:</strong> {{ $course->name }}</p>
    <p><strong>Description:</strong> {{ $course->description ?? 'No description provided.' }}</p>
    <p><strong>Duration:</strong> {{ $course->duration }} weeks</p>
    <p><strong>Fee:</strong> {{ number_format($course->fee, 2) }}</p>
    <p><strong>Difficulty:</strong> {{ $course->difficulty }}</p>
    <p><strong>Status:</strong> {{ $course->is_active ? 'Active' : 'Inactive' }}</p>

    <a href="{{ route('courses.edit', $course) }}">Edit Course</a>
    <br>
    <a href="{{ route('courses.index') }}">Back to Courses</a>
@endsection
