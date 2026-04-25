<?php
// app/Http/Requests/Api/Teacher/MarkAttendanceRequest.php

namespace App\Http\Requests\Api\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class MarkAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware and controller logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'section_id.required' => 'The section is required.',
            'section_id.exists' => 'The selected section does not exist.',
            'date.required' => 'The attendance date is required.',
            'date.date' => 'Please provide a valid date.',
            'attendance.required' => 'Attendance data is required.',
            'attendance.array' => 'Attendance data must be an array.',
            'attendance.*.student_id.required' => 'Each attendance entry must have a student ID.',
            'attendance.*.student_id.exists' => 'One or more student IDs are invalid.',
            'attendance.*.status.required' => 'Each attendance entry must have a status.',
            'attendance.*.status.in' => 'Invalid status value. Allowed: present, absent, late, half_day.',
        ];
    }
}