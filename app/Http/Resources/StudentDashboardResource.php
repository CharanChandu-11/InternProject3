<?php
// app/Http/Resources/StudentDashboardResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDashboardResource extends JsonResource
{
    public function toArray($request)
    {
        $student = $this;
        $user = $student->user;

        return [
            'student' => [
                'id' => $student->id,
                'name' => $user->name,
                'email' => $user->email,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class' => $student->class?->name,
                'section' => $student->section?->name,
                'profile_photo' => $user->profile_photo_url,
            ],
            'today_attendance' => [
                'status' => $this->whenLoaded('attendances', function() use ($student) {
                    $today = $student->attendances->firstWhere('attendance_date', now()->toDateString());
                    return $today ? $today->status : 'not_marked';
                }),
            ],
            'today_timetable' => TimetableResource::collection($this->whenLoaded('timetable')),
            'current_class' => $this->whenLoaded('currentClass'),
            'next_class' => $this->whenLoaded('nextClass'),
            'pending_homework' => HomeworkResource::collection($this->whenLoaded('pendingHomework')),
            'upcoming_exams' => $this->whenLoaded('upcomingExams'),
            'recent_results' => ExamResultResource::collection($this->whenLoaded('recentResults')),
            'fee_status' => StudentFeeResource::collection($this->whenLoaded('feeStatus')),
            'total_due' => $this->total_due,
            'library_books' => BookIssueResource::collection($this->whenLoaded('libraryBooks')),
            'transport' => new StudentTransportResource($this->whenLoaded('transport')),
            'hostel' => new HostelAllocationResource($this->whenLoaded('hostel')),
            'upcoming_events' => EventResource::collection($this->whenLoaded('upcomingEvents')),
            'recent_notifications' => NotificationResource::collection($this->whenLoaded('recentNotifications')),
            'announcements' => AnnouncementResource::collection($this->whenLoaded('announcements')),
            'attendance_stats' => $this->attendanceStats,
            'performance_stats' => $this->performanceStats,
            'quick_stats' => $this->quickStats,
            'progress_chart' => $this->progressData,
        ];
    }
}