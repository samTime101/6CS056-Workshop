# Workshop 2 MVC

# Scenario

We are continuing to build the application for a **training institute**.

In Week 1 we built the first working version of the Training Institute Management System. The application can now:

- Create, view, edit and delete Students
- Create, view, edit and delete Courses
- Validate form input
- Store the information in MySQL

The complete system will **not** be built in a single workshop.

Instead, we will keep developing the application progressively throughout the course.

---

# Let's Start with Week 2

Week 2 focuses on organizing the application we built in Week 1.

At this stage, we are **not adding new features**. The application should behave exactly as it does today.

Instead, we want to focus on the simple question:

> **How can we organize a web application so that it stays easy to read, change and extend as it grows?**
> 

To answer this question, we need to understand:

1. MVC Architecture
2. Controllers
3. Resource Routes
4. Named Routes
5. Route Model Binding
6. Form Requests
7. Model Responsibilities
8. Blade Layouts and Partials

---

# Problem Definition

The Week 1 application works, but look at where the code lives. Every request is handled by a closure inside `routes/web.php`. That one file is doing all of these jobs at the same time:

- Deciding which URL leads where (routing)
- Reading and validating form input
- Talking to the database
- Choosing which page to show or where to redirect

With two resources this is manageable. With the features the institute still needs (relationships, search, filtering, pagination, authentication and a REST API) the file would become huge, repetitive and painful to change.

In Week 2 we **refactor** the application: we keep the same behaviour and move each piece of code to the place where it belongs. The structure we will use is called **MVC**.

### Problems in the Week 1 code

| Problem | Where you can see it in Week 1 |
| --- | --- |
| Everything in one file | All Student routes, validation and queries are inside `routes/web.php` |
| Duplicated validation | The create and update routes repeat almost the same rules |
| Hard-coded URLs | Views contain links such as `/students/create` and `/students/{{ $student->id }}/edit` |
| Repeated HTML | Every view repeats `<!DOCTYPE html>`, `<head>` and the error message block |
| Cannot be cached | Laravel cannot cache routes that use closures, which hurts performance later |

---

# Goal & Deliverables

By the end of Week 2, your application should behave **exactly like Week 1**, but be organized using MVC:

- `StudentController` and `CourseController` handle all request logic
- `routes/web.php` contains only a few lines and no business logic
- Named routes are used everywhere instead of hard-coded URLs
- Validation is moved into Form Request classes
- Models contain data-related logic
- Views share a common layout and reusable partials

---

# Week 2 Checklist

By the end of the workshop, you should be able to:

- Explain what Model, View and Controller are responsible for
- Create controllers using Artisan
- Connect routes to controller methods
- Use named routes and `route()` in Blade
- Use `Route::resource()` for standard CRUD routes
- Use route model binding
- Create and use Form Request classes for validation
- Add casts and accessors to an Eloquent model
- Create a Blade layout and reuse it with `@extends`, `@section` and `@include`
- Decide which layer a piece of code belongs to

---

# What is MVC?

**MVC (Model–View–Controller)** is a way of organizing an application so that each part has one clear responsibility. Laravel is built around this idea.

| Layer | Responsibility | In Laravel | Location |
| --- | --- | --- | --- |
| **Model** | Represents the data and the rules about the data. Talks to the database. | Eloquent models | `app/Models/` |
| **View** | Presents data to the user. Produces the HTML. Contains no database logic. | Blade templates | `resources/views/` |
| **Controller** | Receives the request, coordinates the Model and the View, and returns the response. | Controller classes | `app/Http/Controllers/` |

**Things to understand here:**

- Routes are **not** one of the three letters. A route is the front door of the application: it only maps a URL and HTTP method to a controller method.
- Rule of thumb: **routes map, controllers coordinate, models hold data rules, views display.**

## The Request Flow in MVC

Here is what happens when a user submits the Create Student form, and then lands on the list page:

| # | What happens | Layer |
| --- | --- | --- |
| 1 | Browser sends `POST /students` with the form data | Browser |
| 2 | The route matches the URL and method and calls `StudentController@store` | Route |
| 3 | The Form Request validates the data (if invalid: redirect back with errors) | Controller layer |
| 4 | The controller calls `Student::create(...)` | Controller |
| 5 | The model builds the SQL, MySQL stores the record | Model |
| 6 | The controller returns a redirect to `/students` with a success message | Controller |
| 7 | Browser sends `GET /students`, the route calls `StudentController@index` | Route → Controller |
| 8 | The controller asks the model for the students: `Student::all()` | Controller → Model |
| 9 | The controller passes the data to `student/list.blade.php` | Controller → View |
| 10 | Blade generates the HTML and it is returned to the browser | View |

## Comparing Week 1 and Week 2

| Concern | Week 1 | Week 2 |
| --- | --- | --- |
| Request handling | Closure in `routes/web.php` | `StudentController` method |
| Validation | Inline in the route | Form Request class |
| Database access | Inline in the route | Controller calls the Model |
| Data rules and formatting | Nowhere | Model (casts, accessors) |
| HTML | Repeated in every view | Layout + partials |
| Links between pages | Hard-coded URLs | Named routes |

---

# Step 1: Prepare the Workshop 2 Branch

Workshop 2 builds on top of the Workshop 1 code, so first make sure Workshop 1 is complete and committed.

Open your WSL terminal and go to your workshop repository:

```bash
cd ~/6CS056/6CS056-Workshop
git switch Workshop1
git status
```

`git status` should say there is nothing to commit. If not, commit your Workshop 1 work first.

Now create the Workshop 2 branch from it:

```bash
git switch -c Workshop2
```

Start the server and confirm that Student CRUD still works before we change anything:

```bash
php artisan serve
```

**Checkpoint:** Having a working starting point matters in a refactor. If something breaks later, you know it was your refactoring and not the original code.

---

# Step 2: Create the Student Controller

A controller is a PHP class. Each public method of the class handles one kind of request. Create it with Artisan:

```bash
php artisan make:controller StudentController
```

Laravel creates:

```
app/Http/Controllers/StudentController.php
```

Open the file and add the imports that we will need:

**`app/Http/Controllers/StudentController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    //
}
```

**Things to understand here:**

- Every CRUD operation from Week 1 will become one method of this class: `index`, `create`, `store`, `show`, `edit`, `update` and `destroy`.
- Do **not** delete the closure routes yet. The application keeps working while we build the controller.

---

# Step 3: Move the Read Logic (index and show)

Inside the `StudentController` class, add these two methods. The code is the same as the Week 1 closures, only the location has changed.

```php
public function index()
{
    $students = Student::all();

    return view('student.list', [
        'students' => $students,
    ]);
}

public function show($id)
{
    $student = Student::findOrFail($id);

    return view('student.detail', [
        'student' => $student,
    ]);
}
```

**Things to understand here:**

- `index` handles the list page (`GET /students`). `show` handles the detail page (`GET /students/{id}`).
- The controller asks the **Model** for data (`Student::all()`) and hands it to the **View**. The controller does not build any HTML itself.

---

# Step 4: Move the Create Logic (create and store)

Add two more methods:

```php
public function create()
{
    return view('student.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|email|max:255|unique:students,email',
        'phone'         => 'required|string|max:20',
        'address'       => 'nullable|string|max:500',
        'date_of_birth' => 'nullable|date',
    ]);

    $student = Student::create($validated);

    return redirect()
        ->route('students.index')
        ->with('success', "Student {$student->name} created successfully!");
}
```

**Note:** We added `unique:students,email` to the store rules. In Week 1 a duplicate email passed validation and caused a database error, because the `email` column is unique.

**Things to understand here:**

- `create` only shows the empty form. `store` receives the submitted form. This is why one resource needs two methods for creating a record.
- `redirect()->route('students.index')` redirects using a route **name**. We will define that name in Step 7.

---

# Step 5: Move the Update Logic (edit and update)

Add two more methods. `edit` shows the form filled with the existing data and `update` saves the changes:

```php
public function edit($id)
{
    $student = Student::findOrFail($id);

    return view('student.edit', [
        'student' => $student,
    ]);
}

public function update(Request $request, $id)
{
    $student = Student::findOrFail($id);

    $validated = $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => [
            'required', 'email', 'max:255',
            Rule::unique('students')->ignore($student->id),
        ],
        'phone'         => 'required|string|max:20',
        'address'       => 'nullable|string|max:500',
        'date_of_birth' => 'nullable|date',
    ]);

    $student->update($validated);

    return redirect()
        ->route('students.show', $student->id)
        ->with('success', 'Student updated successfully!');
}
```

**Note:** In Week 1 the `Rule` class was used without being imported. That is why we added `use Illuminate\Validation\Rule;` at the top of the controller.

---

# Step 6: Move the Delete Logic (destroy)

Add the last method:

```php
public function destroy($id)
{
    $student = Student::findOrFail($id);
    $student->delete();

    return redirect()
        ->route('students.index')
        ->with('success', 'Student deleted successfully!');
}
```

**Checkpoint:**

- Your `StudentController` now has seven methods: `index`, `create`, `store`, `show`, `edit`, `update` and `destroy`.
- Compare each method with the matching closure in `routes/web.php`. The logic should be the same.

---

# Step 7: Connect Routes to the Controller

Now we replace the closures with routes that point to the controller. Open:

**`routes/web.php`**

**Delete every Student route from Week 1** (all the `Route::get`, `Route::post`, `Route::put` and `Route::delete` closures for students). Keep the default welcome route.

Then make sure the file starts with these imports:

```php
<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
```

Add the Student routes:

```php
Route::get('/students', [StudentController::class, 'index'])
    ->name('students.index');

Route::get('/students/create', [StudentController::class, 'create'])
    ->name('students.create');

Route::post('/students', [StudentController::class, 'store'])
    ->name('students.store');

Route::get('/students/{id}', [StudentController::class, 'show'])
    ->name('students.show');

Route::get('/students/{id}/edit', [StudentController::class, 'edit'])
    ->name('students.edit');

Route::put('/students/{id}', [StudentController::class, 'update'])
    ->name('students.update');

Route::delete('/students/{id}', [StudentController::class, 'destroy'])
    ->name('students.destroy');
```

**Things to understand here:**

- `[StudentController::class, 'index']` means: when this route matches, call the `index` method of `StudentController`.
- `>name('students.index')` gives the route a name. We can then use `route('students.index')` instead of typing `/students`.
- The **order matters**. `/students/create` must be defined before `/students/{id}`. Otherwise Laravel would treat the word `create` as an `{id}` and try to find a student with id "create".

Refresh `http://127.0.0.1:8000/students` and test the application. It should behave exactly as before.

**Watch out:** Week 1 used `/student/create` (singular) for the create form, but the list page linked to `/students/create` (plural). From now on all URLs use the plural form, `/students/...`.

---

# Step 8: Simplify with Route::resource()

The seven routes above follow a standard pattern for CRUD. Laravel can generate all of them with one line. Replace the seven Student routes with:

```php
Route::resource('students', StudentController::class);
```

Check what Laravel created:

```bash
php artisan route:list --except-vendor
```

You should see these routes:

| Method | URI | Controller method | Route name | Purpose |
| --- | --- | --- | --- | --- |
| GET | /students | index | students.index | List students |
| GET | /students/create | create | students.create | Show create form |
| POST | /students | store | students.store | Save new student |
| GET | /students/{student} | show | students.show | Show one student |
| GET | /students/{student}/edit | edit | students.edit | Show edit form |
| PUT/PATCH | /students/{student} | update | students.update | Save changes |
| DELETE | /students/{student} | destroy | students.destroy | Delete student |

**Things to understand here:**

- The URLs and names are identical to Step 7, so nothing in the application changes.
- The route parameter is now called `{student}` instead of `{id}`. Our controller methods still work because Laravel passes route parameters to the method by position.
- This is why controller method names such as `index` and `store` are not random. They follow the **resource controller convention**.

---

# Step 9: Use Named Routes in the Views

The views still contain hard-coded URLs. If a URL ever changes, every view would need to be edited. Named routes solve this: the URL is defined in one place and every link follows it.

Update the four Student views:

| View | Before | After |
| --- | --- | --- |
| create | `action="/students"` | `action="{{ route('students.store') }}"` |
| create | `href="/students"` | `href="{{ route('students.index') }}"` |
| list | `href="/students/create"` | `href="{{ route('students.create') }}"` |
| list | `href="/students/{{ $student->id }}"` | `href="{{ route('students.show', $student->id) }}"` |
| list | `href="/students/{{ $student->id }}/edit"` | `href="{{ route('students.edit', $student->id) }}"` |
| list | delete form `action="/students/{{ $student->id }}"` | `action="{{ route('students.destroy', $student->id) }}"` |
| detail | `href="/students/{{ $student->id }}/edit"` | `href="{{ route('students.edit', $student->id) }}"` |
| detail | `href="/students"` | `href="{{ route('students.index') }}"` |
| edit | `action="/students/{{ $student->id }}"` | `action="{{ route('students.update', $student->id) }}"` |
| edit | `href="/students"` | `href="{{ route('students.index') }}"` |

**Things to understand here:**

- `route('students.show', $student->id)` fills the `{student}` part of the URL with the id.
- Keep `@csrf` and `@method('PUT')` / `@method('DELETE')` exactly as they were.

Test every link and button again.

---

# Step 10: Route Model Binding

Look at the controller. Four methods start with the same two lines:

```php
$student = Student::findOrFail($id);
```

Laravel can do this automatically. If the type of a controller parameter is a model, and the name matches the route parameter (`{student}`), Laravel finds the record for you. If it does not exist, Laravel returns a 404 response.

Update the four methods in `StudentController`:

```php
public function show(Student $student)
{
    return view('student.detail', [
        'student' => $student,
    ]);
}

public function edit(Student $student)
{
    return view('student.edit', [
        'student' => $student,
    ]);
}

public function update(Request $request, Student $student)
{
    $validated = $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => [
            'required', 'email', 'max:255',
            Rule::unique('students')->ignore($student->id),
        ],
        'phone'         => 'required|string|max:20',
        'address'       => 'nullable|string|max:500',
        'date_of_birth' => 'nullable|date',
    ]);

    $student->update($validated);

    return redirect()
        ->route('students.show', $student)
        ->with('success', 'Student updated successfully!');
}

public function destroy(Student $student)
{
    $student->delete();

    return redirect()
        ->route('students.index')
        ->with('success', 'Student deleted successfully!');
}
```

**Things to understand here:**

- `Student $student` replaces both the `$id` parameter and the `findOrFail` line.
- The parameter name in the method (`$student`) must match the route parameter (`{student}`). This is the reason `Route::resource` uses `{student}`.
- You can pass a model directly to `route()`: `route('students.show', $student)`.
- Visit `/students/9999`. You should see a 404 page.

---

# Step 11: Move Validation into Form Requests

The controller is now short, except for the validation rules, which are duplicated in `store` and `update`. A **Form Request** is a class whose only job is to validate one kind of request.

```bash
php artisan make:request StoreStudentRequest
php artisan make:request UpdateStudentRequest
```

Laravel creates both files in `app/Http/Requests/`.

**`app/Http/Requests/StoreStudentRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:students,email',
            'phone'         => 'required|string|max:20',
            'address'       => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ];
    }
}
```

**`app/Http/Requests/UpdateStudentRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'email'         => [
                'required', 'email', 'max:255',
                Rule::unique('students', 'email')
                    ->ignore($this->route('student')),
            ],
            'phone'         => 'required|string|max:20',
            'address'       => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ];
    }
}
```

Now the controller no longer contains any validation rules. This is the complete `StudentController`:

**`app/Http/Controllers/StudentController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        return view('student.list', [
            'students' => Student::all(),
        ]);
    }

    public function create()
    {
        return view('student.create');
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', "Student {$student->name} created successfully!");
    }

    public function show(Student $student)
    {
        return view('student.detail', ['student' => $student]);
    }

    public function edit(Student $student)
    {
        return view('student.edit', ['student' => $student]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully!');
    }
}
```

**Things to understand here:**

- When a controller method type-hints `StoreStudentRequest`, Laravel runs the validation **before** the method is called.
- If validation fails, Laravel redirects back to the form with `$errors` and the old input. The controller method never runs. This is the same behaviour as `$request->validate()` in Week 1.
- `$request->validated()` returns only the fields that passed validation.
- `authorize()` returns `true` because we have no authentication yet. It will be used in a later workshop.
- Each controller method now has one job: hand data between the Model and the View.

---

# Step 12: Give the Model Some Responsibility

So far the Student model only defines `$fillable`. The Model is also the right place for rules about the data itself, such as how a date is represented or how an age is calculated.

**`app/Models/Student.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn () => $this->date_of_birth?->age);
    }
}
```

**Things to understand here:**

- **Cast**: `'date_of_birth' => 'date'` makes Eloquent return a Carbon date object instead of a plain string. Carbon lets us call methods like `>format()` and `>age`.
- **Accessor**: `age()` creates a virtual attribute. We can now write `$student->age` even though there is no `age` column in the table.
- `?->` is the null-safe operator. If `date_of_birth` is `null`, the result is `null` instead of an error.

**Watch out:** Because of the cast, `$student->date_of_birth` is no longer a plain string. The date input in the edit form needs the format `Y-m-d`, so change the edit view:

**`resources/views/student/edit.blade.php`**

```
value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
```

And in the detail view, show a readable date and the age:

**`resources/views/student/detail.blade.php`**

```
<p>
    <strong>Date of Birth:</strong>
    {{ $student->date_of_birth?->format('d M Y') }}
</p>

<p>
    <strong>Age:</strong>
    {{ $student->age ?? 'Not available' }}
</p>
```

---

# Step 13: Layout and Partials for the View Layer

Every Week 1 view repeats the same HTML boilerplate and the same error block. In Blade we can define the common structure **once** in a layout and let each page fill in only what is different.

Create these two files:

```
resources/views/layouts/app.blade.php
resources/views/partials/errors.blade.php
```

## The layout

**`resources/views/layouts/app.blade.php`**

```
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Training Institute')</title>
</head>
<body>
    <nav>
        <a href="{{ route('students.index') }}">Students</a>
    </nav>
    <hr>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @yield('content')
</body>
</html>
```

## The error partial

**`resources/views/partials/errors.blade.php`**

```
@if($errors->any())
    <div>
        <h3>Please fix the following errors:</h3>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

## Rewrite the Student views

Each view now starts with `@extends`, fills the `title` and `content` sections, and no longer contains `<html>`, `<head>` or `<body>`.

**`resources/views/student/list.blade.php`**

```
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
```

**`resources/views/student/create.blade.php`**

```
@extends('layouts.app')

@section('title', 'Create Student')

@section('content')
    <h1>Create Student</h1>

    @include('partials.errors')

    <form action="{{ route('students.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>
        <br>

        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
        </div>
        <br>

        <div>
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
        </div>
        <br>

        <div>
            <label for="address">Address</label>
            <textarea id="address" name="address">{{ old('address') }}</textarea>
        </div>
        <br>

        <div>
            <label for="date_of_birth">Date of Birth</label>
            <input
                type="date"
                id="date_of_birth"
                name="date_of_birth"
                value="{{ old('date_of_birth') }}"
            >
        </div>
        <br>

        <button type="submit">Create Student</button>
    </form>

    <br>
    <a href="{{ route('students.index') }}">Back to Students</a>
@endsection
```

**`resources/views/student/detail.blade.php`**

```
@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
    <h1>Student Details</h1>

    <p><strong>ID:</strong> {{ $student->id }}</p>
    <p><strong>Name:</strong> {{ $student->name }}</p>
    <p><strong>Email:</strong> {{ $student->email }}</p>
    <p><strong>Phone:</strong> {{ $student->phone }}</p>
    <p><strong>Address:</strong> {{ $student->address }}</p>
    <p>
        <strong>Date of Birth:</strong>
        {{ $student->date_of_birth?->format('d M Y') }}
    </p>
    <p>
        <strong>Age:</strong>
        {{ $student->age ?? 'Not available' }}
    </p>

    <a href="{{ route('students.edit', $student) }}">Edit Student</a>
    <br>
    <a href="{{ route('students.index') }}">Back to Students</a>
@endsection
```

**`resources/views/student/edit.blade.php`**

```
@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
    <h1>Edit Student</h1>

    @include('partials.errors')

    <form action="{{ route('students.update', $student) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="name">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $student->name) }}"
            >
        </div>
        <br>

        <div>
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $student->email) }}"
            >
        </div>
        <br>

        <div>
            <label for="phone">Phone</label>
            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old('phone', $student->phone) }}"
            >
        </div>
        <br>

        <div>
            <label for="address">Address</label>
            <textarea
                id="address"
                name="address"
            >{{ old('address', $student->address) }}</textarea>
        </div>
        <br>

        <div>
            <label for="date_of_birth">Date of Birth</label>
            <input
                type="date"
                id="date_of_birth"
                name="date_of_birth"
                value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
            >
        </div>
        <br>

        <button type="submit">Update Student</button>
    </form>

    <br>
    <a href="{{ route('students.index') }}">Back to Students</a>
@endsection
```

**Things to understand here:**

- The views above already include the changes from Step 9 (named routes) and Step 12 (date format).
- `@extends('layouts.app')` means: use `resources/views/layouts/app.blade.php` as the outer page.
- `@yield('content')` in the layout is a placeholder. `@section('content') ... @endsection` in a page fills it.
- `@include('partials.errors')` inserts another Blade file. Now the error block exists in one file only.
- The success message is shown by the layout, so it works on every page that uses the layout.
- A dot in a view name is a folder separator: `partials.errors` means `partials/errors.blade.php`.

---

# Step 14: Apply MVC to Courses

Now repeat the same process for the Course resource. You already have the `courses` table and the `Course` model from Week 1. This time, let Artisan generate most of the structure:

```bash
php artisan make:controller CourseController --resource --model=Course --requests
```

This single command creates:

- `app/Http/Controllers/CourseController.php` with the seven resource methods, already type-hinted with `Course $course`
- `app/Http/Requests/StoreCourseRequest.php`
- `app/Http/Requests/UpdateCourseRequest.php`

The methods are empty. Fill them in by following the `StudentController` from Step 11. The differences are listed below.

## 14.1 Route

```php
use App\Http\Controllers\CourseController;

Route::resource('courses', CourseController::class);
```

## 14.2 Model

**`app/Models/Course.php`**

```php
protected $fillable = [
    'name',
    'description',
    'duration',
    'fee',
    'difficulty',
    'is_active',
];

protected function casts(): array
{
    return [
        'is_active' => 'boolean',
        'fee'       => 'decimal:2',
    ];
}
```

## 14.3 Validation rules

Use these rules in both `StoreCourseRequest` and `UpdateCourseRequest`. Course names do not have to be unique in this workshop, so there is no `Rule::unique` here.

| Field | Rule |
| --- | --- |
| name | `required|string|max:255` |
| description | `nullable|string` |
| duration | `required|integer|min:1` |
| fee | `required|numeric|min:0` |
| difficulty | `required` and `Rule::in(['Easy', 'Medium', 'Hard'])` |
| is_active | `boolean` |

**Note:**

- The values in `Rule::in(...)` must match the values of the `difficulty` enum in your Week 1 migration.
- Remember to change `authorize()` to return `true` in both request classes.

## 14.4 Views

Create the Course views in `resources/views/course/` using the same layout and partial:

```
resources/views/course/list.blade.php
resources/views/course/create.blade.php
resources/views/course/detail.blade.php
resources/views/course/edit.blade.php
```

Each view should start with `@extends('layouts.app')`. The list page should show name, duration, fee, difficulty and whether the course is active. Two form fields are different from the Student form:

**Difficulty** is a fixed set of choices, so use a `<select>`:

```
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
```

**Is active** is a boolean, so use a checkbox with a hidden field:

```
<input type="hidden" name="is_active" value="0">
<input
    type="checkbox"
    id="is_active"
    name="is_active"
    value="1"
    {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}
>
```

**Things to understand here:**

- A browser does **not** send an unchecked checkbox at all. The hidden input with value `0` is always sent, so an unchecked box arrives as `0` and a checked box arrives as `1` (the later input with the same name wins).
- `$course->difficulty ?? ''` works on the create page too, where `$course` does not exist. The `??` operator does not raise an error for undefined values.
- To show the fee nicely, use `{{ number_format($course->fee, 2) }}`.

## 14.5 Navigation

Add a link to Courses in `resources/views/layouts/app.blade.php`:

```
<nav>
    <a href="{{ route('students.index') }}">Students</a> |
    <a href="{{ route('courses.index') }}">Courses</a>
</nav>
```

**Checkpoint:** Test all five Course operations: list, create, view, edit and delete, including the validation errors.

---

# Step 15: Test the Refactored Application

Because this was a refactor, the goal is that **everything still works exactly as in Week 1**. Restart the server and go through this list:

| Test | How | Expected result |
| --- | --- | --- |
| List | Open `/students` | Students are shown, or "No students found." |
| Create (valid) | Fill the form correctly | Redirect to the list with a success message |
| Create (invalid) | Submit an empty form | Errors shown, old input kept |
| Duplicate email | Create two students with the same email | Validation error, no database exception |
| View | Click View | Details and age are shown |
| Edit | Change a name, keep the email | Update succeeds (no duplicate email error) |
| Delete | Click Delete | Student removed, success message shown |
| Missing record | Open `/students/9999` | 404 page |
| Courses | Repeat the same tests on `/courses` | Same behaviour |

Then inspect the routes Laravel knows about:

```bash
php artisan route:list --except-vendor
```

You should see at least 14 routes for `students.*` and `courses.*` (7 each).

---

# Step 16: Push the Code to GitHub

Check the project status:

```bash
git status
```

Add the files and commit:

```bash
git add .
git commit -m "Complete Workshop 2 MVC refactor"
```

Push the branch:

```bash
git push origin Workshop2
```

Use the branch name specified in the **GitHub Guideline** if your repository uses a different naming convention. Verify your code on GitHub.

---

---

# Final Project Structure

At the end of Workshop 2, your project should contain at least:

```
workshop-project/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── StudentController.php
│   │   │   └── CourseController.php
│   │   └── Requests/
│   │       ├── StoreStudentRequest.php
│   │       ├── UpdateStudentRequest.php
│   │       ├── StoreCourseRequest.php
│   │       └── UpdateCourseRequest.php
│   └── Models/
│       ├── Student.php
│       └── Course.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── partials/
│       │   └── errors.blade.php
│       ├── student/
│       │   ├── list.blade.php
│       │   ├── create.blade.php
│       │   ├── detail.blade.php
│       │   └── edit.blade.php
│       └── course/
│           ├── list.blade.php
│           ├── create.blade.php
│           ├── detail.blade.php
│           └── edit.blade.php
│
├── routes/
│   └── web.php      (now only a few lines)
│
├── .env
├── composer.json
└── artisan
```

Your final `routes/web.php` should look similar to:

```php
<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('students', StudentController::class);
Route::resource('courses', CourseController::class);
```

---

# What You Have Built

The application behaves exactly as it did at the end of Week 1, but the request flow is now organised:

```
Browser
   ↓
Route
   ↓
Form Request (validation)
   ↓
Controller
   ↓
Model
   ↓
MySQL
   ↓
Model
   ↓
Controller
   ↓
View (Blade)
   ↓
HTML Response
   ↓
Browser
```

# Workshop 2 Completion Criteria

You have completed Workshop 2 when:

- `StudentController` exists and contains all seven resource methods.
- `CourseController` exists and contains all seven resource methods.
- `routes/web.php` contains no closures for Students or Courses.
- Routes are registered with `Route::resource()`.
- All links and forms use `route()` with route names.
- Route model binding is used in `show`, `edit`, `update` and `destroy`.
- Validation is done in Form Request classes.
- The Student model has a `date_of_birth` cast and an `age` accessor.
- All views extend `layouts.app`.
- The error block is included from a partial.
- All Student and Course CRUD operations still work.
- Code has been committed and pushed to GitHub.
- Application has been deployed according to the Student Server Guideline.

# Key Concepts Learned

### Model

Represents the data and the rules about the data. In Laravel: an Eloquent class in `app/Models/`.

### View

Presents data to the user. In Laravel: Blade templates in `resources/views/`. Views display data but never query the database.

### Controller

Receives the request, asks the Model for data or asks it to save data, and returns a View or a redirect.

### Route

Maps an HTTP method and URL to a controller method. Named routes remove hard-coded URLs.

### Resource Controller

A controller that follows the standard seven CRUD methods, registered with one `Route::resource()` line.

### Route Model Binding

Laravel converts a route parameter such as `{student}` into a `Student` model automatically, and returns 404 if it does not exist.

### Form Request

A class whose only job is to authorize and validate a request, before the controller runs.

### Layouts and Partials

Reusable Blade files that remove repeated HTML.

### Refactoring

Changing the structure of code without changing its behavior.

---

# Common Errors and Fixes

| Error | Likely cause | Fix |
| --- | --- | --- |
| `Route [students.index] not defined` | Route not registered or not named | Check `Route::resource(...)` in `web.php`; run `php artisan route:list` |
| `Target class [StudentController] does not exist` | Missing import in `web.php` | Add `use App\Http\Controllers\StudentController;` |
| `/students/create` shows a 404 | Explicit routes: `{id}` route defined before `create` | Define `/students/create` before `/students/{id}` |
| `Class "Rule" not found` | Missing import | Add `use Illuminate\Validation\Rule;` |
| Date of birth is empty on the edit form | Cast added but view still prints the Carbon object | Use `$student->date_of_birth?->format('Y-m-d')` |
| `419 Page Expired` | Missing `@csrf` in a form | Add `@csrf` inside the form |
| `Add [name] to fillable property` | Field missing from `$fillable` | Add the field to the model |
| Edit always says email already taken | Ignore rule not applied | Use `Rule::unique(...)->ignore($this->route('student'))` in the update request |
| Old routes or views still appear | Cached data | Run `php artisan optimize:clear` |

---