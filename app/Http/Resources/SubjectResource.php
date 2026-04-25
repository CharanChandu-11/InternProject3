<?php
// app/Http/Resources/SubjectResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
            'type_badge' => $this->getTypeBadge(),
            'type_icon' => $this->getTypeIcon(),
            'description' => $this->description,
            
            // Relationships
            'teachers' => UserResource::collection($this->whenLoaded('teachers')),
            'classes' => ClassResource::collection($this->whenLoaded('classes')),
            'timetables' => TimetableResource::collection($this->whenLoaded('timetables')),
            'exam_schedules' => ExamScheduleResource::collection($this->whenLoaded('examSchedules')),
            'homework' => HomeworkResource::collection($this->whenLoaded('homework')),
            
            // Statistics
            'classes_count' => $this->whenCounted('classes'),
            'teachers_count' => $this->whenCounted('teachers'),
            'exams_count' => $this->whenCounted('examSchedules'),
            'homework_count' => $this->whenCounted('homework'),
            
            // Formatted dates
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('F j, Y, g:i A'),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'updated_at_formatted' => $this->updated_at?->format('F j, Y, g:i A'),
            'updated_at_human' => $this->updated_at?->diffForHumans(),
        ];
    }

    /**
     * Get human-readable subject type text.
     */
    private function getTypeText(): string
    {
        return match($this->type) {
            'core' => 'Core Subject',
            'elective' => 'Elective Subject',
            'language' => 'Language Subject',
            'practical' => 'Practical Subject',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get badge color for subject type.
     */
    private function getTypeBadge(): array
    {
        return match($this->type) {
            'core' => [
                'color' => 'primary',
                'background' => '#4361ee',
                'text' => 'white',
            ],
            'elective' => [
                'color' => 'info',
                'background' => '#4cc9f0',
                'text' => 'white',
            ],
            'language' => [
                'color' => 'success',
                'background' => '#06ffa5',
                'text' => 'dark',
            ],
            'practical' => [
                'color' => 'warning',
                'background' => '#ffd166',
                'text' => 'dark',
            ],
            default => [
                'color' => 'secondary',
                'background' => '#6c757d',
                'text' => 'white',
            ],
        };
    }

    /**
     * Get icon for subject type.
     */
    private function getTypeIcon(): string
    {
        return match($this->type) {
            'core' => 'fas fa-star',
            'elective' => 'fas fa-check-circle',
            'language' => 'fas fa-language',
            'practical' => 'fas fa-flask',
            default => 'fas fa-book',
        };
    }

    /**
     * Get additional data for detailed view.
     */
    public function withDetailedData(): array
    {
        return [
            'classes_detailed' => $this->whenLoaded('classes', function() {
                return $this->classes->map(function($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'full_name' => $class->full_name,
                        'academic_year' => $class->academicYear?->name,
                        'teacher' => $class->pivot->teacher ? [
                            'id' => $class->pivot->teacher->id,
                            'name' => $class->pivot->teacher->name,
                            'email' => $class->pivot->teacher->email,
                        ] : null,
                        'theory_marks' => $class->pivot->theory_marks,
                        'practical_marks' => $class->pivot->practical_marks,
                        'total_marks' => $class->pivot->theory_marks + $class->pivot->practical_marks,
                        'is_lab_required' => $class->pivot->is_lab_required ?? false,
                    ];
                });
            }),
            'teachers_detailed' => $this->whenLoaded('teachers', function() {
                return $this->teachers->map(function($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'profile_photo' => $teacher->profile_photo_url,
                        'designation' => $teacher->employee?->designation,
                        'department' => $teacher->employee?->department,
                    ];
                });
            }),
            'upcoming_exams' => $this->whenLoaded('examSchedules', function() {
                return $this->examSchedules
                    ->filter(function($schedule) {
                        return $schedule->exam_date >= now();
                    })
                    ->take(5)
                    ->map(function($schedule) {
                        return [
                            'id' => $schedule->id,
                            'exam_name' => $schedule->exam->name,
                            'exam_date' => $schedule->exam_date->format('Y-m-d'),
                            'exam_date_formatted' => $schedule->exam_date->format('F j, Y'),
                            'class' => $schedule->class->name,
                            'section' => $schedule->section->name,
                            'total_marks' => $schedule->total_marks,
                        ];
                    });
            }),
            'recent_homework' => $this->whenLoaded('homework', function() {
                return $this->homework
                    ->sortByDesc('created_at')
                    ->take(5)
                    ->map(function($homework) {
                        return [
                            'id' => $homework->id,
                            'title' => $homework->title,
                            'submission_date' => $homework->submission_date->format('Y-m-d'),
                            'submission_date_formatted' => $homework->submission_date->format('F j, Y'),
                            'class' => $homework->class->name,
                            'section' => $homework->section->name,
                        ];
                    });
            }),
        ];
    }

    /**
     * Get summary data for subject listings.
     */
    public function withSummaryData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
            'type_badge' => $this->getTypeBadge(),
            'classes_count' => $this->classes()->count(),
            'teachers_count' => $this->teachers()->count(),
            'total_exams' => $this->examSchedules()->count(),
            'total_homework' => $this->homework()->count(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }

    /**
     * Get minimal data for select dropdowns.
     */
    public function withMinimalData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'display_name' => $this->name . ' (' . $this->code . ')',
        ];
    }

    /**
     * Get performance data for a subject (for student view).
     */
    public function withPerformanceData($studentId = null): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
        ];

        if ($studentId) {
            $results = $this->examSchedules()
                ->whereHas('results', function($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                })
                ->with(['results' => function($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                }])
                ->get();

            $examResults = [];
            $totalMarks = 0;
            $totalObtained = 0;
            $totalPercentage = 0;

            foreach ($results as $schedule) {
                $result = $schedule->results->first();
                if ($result) {
                    $percentage = $result->percentage;
                    $examResults[] = [
                        'exam_id' => $schedule->exam_id,
                        'exam_name' => $schedule->exam->name,
                        'exam_date' => $schedule->exam_date->format('Y-m-d'),
                        'obtained_marks' => $result->total_marks_obtained,
                        'total_marks' => $schedule->total_marks + ($schedule->practical_marks ?? 0),
                        'percentage' => $percentage,
                        'grade' => $result->grade,
                    ];
                    $totalObtained += $result->total_marks_obtained;
                    $totalMarks += $schedule->total_marks + ($schedule->practical_marks ?? 0);
                    $totalPercentage += $percentage;
                }
            }

            $data['performance'] = [
                'exam_results' => $examResults,
                'total_exams' => count($examResults),
                'average_marks' => count($examResults) > 0 ? round($totalObtained / count($examResults), 2) : 0,
                'average_percentage' => count($examResults) > 0 ? round($totalPercentage / count($examResults), 2) : 0,
                'total_marks_obtained' => $totalObtained,
                'total_max_marks' => $totalMarks,
                'overall_percentage' => $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100, 2) : 0,
            ];
        }

        return $data;
    }

    /**
     * Get teacher's subject assignment details.
     */
    public function withTeacherData($teacherId = null): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
        ];

        if ($teacherId) {
            $classSubjects = \App\Models\ClassSubject::where('subject_id', $this->id)
                ->where('teacher_id', $teacherId)
                ->with(['class', 'section'])
                ->get();

            $data['assigned_classes'] = $classSubjects->map(function($cs) {
                return [
                    'class_id' => $cs->class_id,
                    'class_name' => $cs->class->name,
                    'section_id' => $cs->section_id,
                    'section_name' => $cs->section->name,
                    'theory_marks' => $cs->theory_marks,
                    'practical_marks' => $cs->practical_marks,
                    'total_marks' => $cs->theory_marks + $cs->practical_marks,
                    'is_lab_required' => $cs->is_lab_required,
                ];
            });

            $data['assigned_classes_count'] = $classSubjects->count();
        }

        return $data;
    }

    /**
     * Get subject statistics for dashboard.
     */
    public function withStatistics(): array
    {
        $totalStudents = 0;
        foreach ($this->classes as $class) {
            $totalStudents += $class->students()->count();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'total_classes' => $this->classes()->count(),
            'total_teachers' => $this->teachers()->count(),
            'total_students' => $totalStudents,
            'total_exams' => $this->examSchedules()->count(),
            'total_homework' => $this->homework()->count(),
            'average_marks' => $this->calculateAverageMarks(),
            'pass_percentage' => $this->calculatePassPercentage(),
        ];
    }

    /**
     * Calculate average marks across all exams for this subject.
     */
    private function calculateAverageMarks(): float
    {
        $results = \App\Models\ExamResult::whereHas('examSchedule', function($q) {
            $q->where('subject_id', $this->id);
        })->get();

        if ($results->isEmpty()) {
            return 0;
        }

        return round($results->avg('total_marks_obtained'), 2);
    }

    /**
     * Calculate pass percentage for this subject.
     */
    private function calculatePassPercentage(): float
    {
        $results = \App\Models\ExamResult::whereHas('examSchedule', function($q) {
            $q->where('subject_id', $this->id);
        })->get();

        if ($results->isEmpty()) {
            return 0;
        }

        $passed = $results->filter(function($result) {
            return $result->percentage >= 40;
        })->count();

        return round(($passed / $results->count()) * 100, 2);
    }
}