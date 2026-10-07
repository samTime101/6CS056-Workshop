@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
    <h1>Create Course</h1>

    @include('partials.errors')

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>
        <br>

        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <br>

        <div>
            <label for="duration">Duration (weeks)</label>
            <input type="number" id="duration" name="duration" min="1" value="{{ old('duration') }}">
        </div>
        <br>

        <div>
            <label for="fee">Fee</label>
            <input type="number" id="fee" name="fee" step="0.01" min="0" value="{{ old('fee') }}">
        </div>
        <br>

        <div>
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty">
                @foreach(['Easy', 'Medium', 'Hard'] as $level)
                    <option
                        value="{{ $level }}"
                        {{ old('difficulty', $course->difficulty ?? '') === $level ? 'selected' : '' }}
                    >
                        {{ $level }}
                    </option>
                @endforeach
            </select>
        </div>
        <br>

        <div>
            <label for="is_active">Is Active</label>
            <input type="hidden" name="is_active" value="0">
            <input
                type="checkbox"
                id="is_active"
                name="is_active"
                value="1"
                {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}
            >
        </div>
        <br>

        <button type="submit">Create Course</button>
    </form>

    <br>
    <a href="{{ route('courses.index') }}">Back to Courses</a>
@endsection
