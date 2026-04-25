<?php
// app/Http/Resources/ExamResultResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'exam_schedule' => new ExamScheduleResource($this->whenLoaded('examSchedule')),
            'student' => new StudentResource($this->whenLoaded('student')),
            'theory_marks_obtained' => $this->theory_marks_obtained,
            'practical_marks_obtained' => $this->practical_marks_obtained,
            'total_marks_obtained' => $this->total_marks_obtained,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'result_status' => $this->result_status,
            'result_status_color' => $this->result_status === 'Pass' ? 'success' : 'danger',
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}