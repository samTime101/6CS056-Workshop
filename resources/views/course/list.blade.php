@extends('layouts.app')

@section('title', 'Courses')

@section('content')
    <h1>Courses</h1>

    <a href="{{ route('courses.create') }}">Create Course</a>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Fee</th>
                <th>Difficulty</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>{{ $course->id }}</td>
                    <td>{{ $course->name }}</td>
                    <td>{{ $course->duration }} weeks</td>
                    <td>{{ number_format($course->fee, 2) }}</td>
                    <td>{{ $course->difficulty }}</td>
                    <td>{{ $course->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('courses.show', $course) }}">View</a>
                        <a href="{{ route('courses.edit', $course) }}">Edit</a>

                        <form
                            action="{{ route('courses.destroy', $course) }}"
                            method="POST"
                            style="display:inline"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No courses found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
