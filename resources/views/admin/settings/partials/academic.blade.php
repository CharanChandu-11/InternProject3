{{-- resources/views/admin/settings/partials/academic.blade.php --}}
<form action="{{ route('admin.settings.academic') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Current Academic Year</label>
            <select name="current_academic_year_id" class="form-select">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                        {{ $year->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Default Passing Percentage</label>
            <input type="number" name="passing_percentage" class="form-control" 
                   value="{{ old('passing_percentage', $academicSettings['passing_percentage'] ?? 40) }}" min="0" max="100">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Marks per Subject</label>
            <input type="number" name="max_marks_per_subject" class="form-control" 
                   value="{{ old('max_marks_per_subject', $academicSettings['max_marks_per_subject'] ?? 100) }}" min="1">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Grading System</label>
            <select name="grading_system" class="form-select">
                <option value="percentage" {{ (old('grading_system', $academicSettings['grading_system'] ?? 'percentage') == 'percentage') ? 'selected' : '' }}>Percentage</option>
                <option value="cgpa" {{ (old('grading_system', $academicSettings['grading_system'] ?? 'percentage') == 'cgpa') ? 'selected' : '' }}>CGPA</option>
                <option value="letter_grade" {{ (old('grading_system', $academicSettings['grading_system'] ?? 'percentage') == 'letter_grade') ? 'selected' : '' }}>Letter Grade</option>
            </select>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">Grading Scale</label>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Minimum Percentage</th>
                            <th>Maximum Percentage</th>
                            <th>Grade Point</th>
                        </tr>
                    </thead>
                    <tbody id="gradingScaleTable">
                        @php
                            $gradingScale = $academicSettings['grading_scale'] ?? [
                                ['grade' => 'A+', 'min' => 90, 'max' => 100, 'point' => 10],
                                ['grade' => 'A', 'min' => 80, 'max' => 89, 'point' => 9],
                                ['grade' => 'B+', 'min' => 70, 'max' => 79, 'point' => 8],
                                ['grade' => 'B', 'min' => 60, 'max' => 69, 'point' => 7],
                                ['grade' => 'C+', 'min' => 50, 'max' => 59, 'point' => 6],
                                ['grade' => 'C', 'min' => 40, 'max' => 49, 'point' => 5],
                                ['grade' => 'D', 'min' => 33, 'max' => 39, 'point' => 4],
                                ['grade' => 'F', 'min' => 0, 'max' => 32, 'point' => 0],
                            ];
                        @endphp
                        @foreach($gradingScale as $index => $grade)
                        <tr>
                            <td><input type="text" name="grading_scale[{{ $index }}][grade]" class="form-control" value="{{ $grade['grade'] }}" required></td>
                            <td><input type="number" name="grading_scale[{{ $index }}][min]" class="form-control" value="{{ $grade['min'] }}" required></td>
                            <td><input type="number" name="grading_scale[{{ $index }}][max]" class="form-control" value="{{ $grade['max'] }}" required></td>
                            <td><input type="number" name="grading_scale[{{ $index }}][point]" class="form-control" value="{{ $grade['point'] }}" required step="0.1"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-grade">×</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            <button type="button" id="addGradeBtn" class="btn btn-sm btn-secondary">Add Grade</button>
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Academic Settings
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let gradeIndex = {{ count($gradingScale) }};
    
    $('#addGradeBtn').click(function() {
        let newRow = `
            <tr>
                <td><input type="text" name="grading_scale[${gradeIndex}][grade]" class="form-control" placeholder="Grade" required></td>
                <td><input type="number" name="grading_scale[${gradeIndex}][min]" class="form-control" placeholder="Min %" required></td>
                <td><input type="number" name="grading_scale[${gradeIndex}][max]" class="form-control" placeholder="Max %" required></td>
                <td><input type="number" name="grading_scale[${gradeIndex}][point]" class="form-control" placeholder="Grade Point" required step="0.1"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-grade">×</button></td>
            </tr>
        `;
        $('#gradingScaleTable').append(newRow);
        gradeIndex++;
    });
    
    $(document).on('click', '.remove-grade', function() {
        if ($('#gradingScaleTable tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('At least one grade is required');
        }
    });
</script>
@endpush