<?php
// app/Http/Controllers/Api/SearchController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ClassResource;
use App\Http\Resources\BookResource;
use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseController
{
    /**
     * Global search across all modules
     */
    public function global(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'modules' => 'nullable|array',
            'modules.*' => 'in:students,teachers,classes,books,employees,parents',
        ]);

        $query = $request->query;
        $modules = $request->modules ?? ['students', 'teachers', 'classes', 'books', 'employees', 'parents'];
        
        $results = [];

        if (in_array('students', $modules)) {
            $results['students'] = $this->searchStudents($query);
        }
        
        if (in_array('teachers', $modules)) {
            $results['teachers'] = $this->searchTeachers($query);
        }
        
        if (in_array('employees', $modules)) {
            $results['employees'] = $this->searchEmployees($query);
        }
        
        if (in_array('parents', $modules)) {
            $results['parents'] = $this->searchParents($query);
        }
        
        if (in_array('classes', $modules)) {
            $results['classes'] = $this->searchClasses($query);
        }
        
        if (in_array('books', $modules)) {
            $results['books'] = $this->searchBooks($query);
        }

        return $this->sendResponse($results, 'Search results retrieved successfully');
    }

    /**
     * Search students
     */
    public function students(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'nullable|in:active,inactive,all',
        ]);

        $students = $this->searchStudents($request->query, $request);

        return $this->sendResponse([
            'students' => StudentResource::collection($students),
            'total' => $students->count(),
        ], 'Students search results');
    }

    /**
     * Search teachers
     */
    public function teachers(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'department' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,all',
        ]);

        $teachers = $this->searchTeachers($request->query, $request);

        return $this->sendResponse([
            'teachers' => UserResource::collection($teachers),
            'total' => $teachers->count(),
        ], 'Teachers search results');
    }

    /**
     * Search classes
     */
    public function classes(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $classes = $this->searchClasses($request->query);

        return $this->sendResponse([
            'classes' => ClassResource::collection($classes),
            'total' => $classes->count(),
        ], 'Classes search results');
    }

    /**
     * Search books
     */
    public function books(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'category' => 'nullable|string',
        ]);

        $books = $this->searchBooks($request->query, $request);

        return $this->sendResponse([
            'books' => BookResource::collection($books),
            'total' => $books->count(),
        ], 'Books search results');
    }

    /**
     * Advanced search with filters
     */
    public function advanced(Request $request)
    {
        $request->validate([
            'type' => 'required|in:students,teachers,employees,parents',
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'admission_number' => 'nullable|string',
            'employee_id' => 'nullable|string',
            'department' => 'nullable|string',
            'status' => 'nullable|boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $type = $request->type;
        $results = [];

        switch ($type) {
            case 'students':
                $results = $this->advancedSearchStudents($request);
                break;
            case 'teachers':
                $results = $this->advancedSearchTeachers($request);
                break;
            case 'employees':
                $results = $this->advancedSearchEmployees($request);
                break;
            case 'parents':
                $results = $this->advancedSearchParents($request);
                break;
        }

        return $this->sendResponse($results, 'Advanced search results');
    }

    /**
     * Search students helper
     */
    private function searchStudents($query, $filters = null)
    {
        $searchQuery = Student::with(['user', 'class', 'section']);
        
        // Search by name, admission number, roll number
        $searchQuery->where(function($q) use ($query) {
            $q->where('admission_number', 'like', "%{$query}%")
              ->orWhere('roll_number', 'like', "%{$query}%")
              ->orWhereHas('user', function($uq) use ($query) {
                  $uq->where('name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->orWhere('phone', 'like', "%{$query}%");
              });
        });

        if ($filters) {
            if ($filters->has('class_id')) {
                $searchQuery->where('class_id', $filters->class_id);
            }
            if ($filters->has('section_id')) {
                $searchQuery->where('section_id', $filters->section_id);
            }
            if ($filters->has('status')) {
                $status = $filters->status;
                if ($status === 'active') {
                    $searchQuery->whereHas('user', function($q) {
                        $q->where('is_active', true);
                    });
                } elseif ($status === 'inactive') {
                    $searchQuery->whereHas('user', function($q) {
                        $q->where('is_active', false);
                    });
                }
            }
        }

        return $searchQuery->limit(50)->get();
    }

    /**
     * Search teachers helper
     */
    private function searchTeachers($query, $filters = null)
    {
        $searchQuery = User::where('user_type', 'teacher')
            ->with(['profile', 'employee']);

        $searchQuery->where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhereHas('profile', function($pq) use ($query) {
                  $pq->where('qualification', 'like', "%{$query}%");
              })
              ->orWhereHas('employee', function($eq) use ($query) {
                  $eq->where('employee_id', 'like', "%{$query}%")
                     ->orWhere('designation', 'like', "%{$query}%")
                     ->orWhere('department', 'like', "%{$query}%");
              });
        });

        if ($filters && $filters->has('department')) {
            $searchQuery->whereHas('employee', function($q) use ($filters) {
                $q->where('department', $filters->department);
            });
        }

        if ($filters && $filters->has('status')) {
            $status = $filters->status;
            if ($status === 'active') {
                $searchQuery->where('is_active', true);
            } elseif ($status === 'inactive') {
                $searchQuery->where('is_active', false);
            }
        }

        return $searchQuery->limit(50)->get();
    }

    /**
     * Search employees helper
     */
    private function searchEmployees($query, $filters = null)
    {
        $searchQuery = User::where('user_type', 'employee')
            ->with(['profile', 'employee']);

        $searchQuery->where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhereHas('employee', function($eq) use ($query) {
                  $eq->where('employee_id', 'like', "%{$query}%")
                     ->orWhere('designation', 'like', "%{$query}%")
                     ->orWhere('department', 'like', "%{$query}%");
              });
        });

        if ($filters && $filters->has('department')) {
            $searchQuery->whereHas('employee', function($q) use ($filters) {
                $q->where('department', $filters->department);
            });
        }

        return $searchQuery->limit(50)->get();
    }

    /**
     * Search parents helper
     */
    private function searchParents($query, $filters = null)
    {
        $searchQuery = User::where('user_type', 'parent')
            ->with(['profile', 'parent', 'parent.children']);

        $searchQuery->where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhereHas('parent', function($pq) use ($query) {
                  $pq->where('occupation', 'like', "%{$query}%");
              })
              ->orWhereHas('parent.children', function($cq) use ($query) {
                  $cq->whereHas('user', function($uq) use ($query) {
                      $uq->where('name', 'like', "%{$query}%");
                  });
              });
        });

        return $searchQuery->limit(50)->get();
    }

    /**
     * Search classes helper
     */
    private function searchClasses($query)
    {
        return Classes::with(['academicYear', 'sections'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('numeric_name', 'like', "%{$query}%")
            ->limit(50)
            ->get();
    }

    /**
     * Search books helper
     */
    private function searchBooks($query, $filters = null)
    {
        $searchQuery = Book::query();

        $searchQuery->where(function($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('isbn', 'like', "%{$query}%")
              ->orWhere('author', 'like', "%{$query}%")
              ->orWhere('publisher', 'like', "%{$query}%")
              ->orWhere('category', 'like', "%{$query}%");
        });

        if ($filters && $filters->has('category')) {
            $searchQuery->where('category', $filters->category);
        }

        return $searchQuery->limit(50)->get();
    }

    /**
     * Advanced search for students
     */
    private function advancedSearchStudents($request)
    {
        $query = Student::with(['user', 'class', 'section']);

        if ($request->has('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->name}%");
            });
        }

        if ($request->has('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', "%{$request->email}%");
            });
        }

        if ($request->has('phone')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('phone', 'like', "%{$request->phone}%");
            });
        }

        if ($request->has('admission_number')) {
            $query->where('admission_number', 'like', "%{$request->admission_number}%");
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->has('status')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('is_active', $request->status);
            });
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('admission_date', [$request->date_from, $request->date_to]);
        } elseif ($request->has('date_from')) {
            $query->where('admission_date', '>=', $request->date_from);
        } elseif ($request->has('date_to')) {
            $query->where('admission_date', '<=', $request->date_to);
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Advanced search for teachers
     */
    private function advancedSearchTeachers($request)
    {
        $query = User::where('user_type', 'teacher')
            ->with(['profile', 'employee']);

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        if ($request->has('employee_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employee_id', 'like', "%{$request->employee_id}%");
            });
        }

        if ($request->has('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->has('qualification')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('qualification', 'like', "%{$request->qualification}%");
            });
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Advanced search for employees
     */
    private function advancedSearchEmployees($request)
    {
        $query = User::where('user_type', 'employee')
            ->with(['profile', 'employee']);

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        if ($request->has('employee_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employee_id', 'like', "%{$request->employee_id}%");
            });
        }

        if ($request->has('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->has('designation')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('designation', 'like', "%{$request->designation}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Advanced search for parents
     */
    private function advancedSearchParents($request)
    {
        $query = User::where('user_type', 'parent')
            ->with(['profile', 'parent', 'parent.children']);

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        if ($request->has('occupation')) {
            $query->whereHas('parent', function($q) use ($request) {
                $q->where('occupation', 'like', "%{$request->occupation}%");
            });
        }

        if ($request->has('student_name')) {
            $query->whereHas('parent.children', function($q) use ($request) {
                $q->whereHas('user', function($uq) use ($request) {
                    $uq->where('name', 'like', "%{$request->student_name}%");
                });
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'type' => 'nullable|in:students,teachers,classes,books',
        ]);

        $query = $request->query;
        $type = $request->type ?? 'all';
        $suggestions = [];

        if ($type === 'all' || $type === 'students') {
            $students = Student::with('user')
                ->whereHas('user', function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get()
                ->map(function($student) {
                    return [
                        'type' => 'student',
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'class' => $student->class_name,
                    ];
                });
            $suggestions = array_merge($suggestions, $students->toArray());
        }

        if ($type === 'all' || $type === 'teachers') {
            $teachers = User::where('user_type', 'teacher')
                ->where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function($teacher) {
                    return [
                        'type' => 'teacher',
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'designation' => $teacher->employee?->designation,
                    ];
                });
            $suggestions = array_merge($suggestions, $teachers->toArray());
        }

        if ($type === 'all' || $type === 'classes') {
            $classes = Classes::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function($class) {
                    return [
                        'type' => 'class',
                        'id' => $class->id,
                        'name' => $class->full_name,
                        'students_count' => $class->students()->count(),
                    ];
                });
            $suggestions = array_merge($suggestions, $classes->toArray());
        }

        if ($type === 'all' || $type === 'books') {
            $books = Book::where('title', 'like', "%{$query}%")
                ->orWhere('author', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function($book) {
                    return [
                        'type' => 'book',
                        'id' => $book->id,
                        'title' => $book->title,
                        'author' => $book->author,
                        'isbn' => $book->isbn,
                    ];
                });
            $suggestions = array_merge($suggestions, $books->toArray());
        }

        return $this->sendResponse($suggestions, 'Search suggestions retrieved');
    }
}