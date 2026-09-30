<!DOCTYPE html>
<html>

<head>
    <title>Student Details</title>
</head>

<body>
    22 / 33
    <h1>Student Details</h1>
    <p>
        <strong>ID:</strong>
        {{ $student->id }}
    </p>
    <p>
        <strong>Name:</strong>
        {{ $student->name }}
    </p>
    <p>
        <strong>Email:</strong>
        {{ $student->email }}
    </p>
    <p>
        <strong>Phone:</strong>
        {{ $student->phone }}
    </p>
    <p>
        <strong>Address:</strong>
        {{ $student->address }}
    </p>
    <p>
        <strong>Date of Birth:</strong>
        {{ $student->date_of_birth }}
    </p>
    <a href="/students/{{ $student->id }}/edit">
        Edit Student
    </a>
    23 / 33
    <br>
    <a href="/students">
        Back to Students
    </a>
</body>

</html>
