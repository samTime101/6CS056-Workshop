@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <h1>Students</h1>

    <a href="{{ route('students.create') }}">Create Student</a>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->phone }}</td>
                    <td>
                        <a href="{{ route('students.show', $student) }}">View</a>
                        <a href="{{ route('students.edit', $student) }}">Edit</a>

                        <form
                            action="{{ route('students.destroy', $student) }}"
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
                    <td colspan="5">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection