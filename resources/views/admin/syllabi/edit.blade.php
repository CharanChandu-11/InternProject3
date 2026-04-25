{{-- resources/views/admin/syllabi/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Syllabus - ' . $syllabus->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Syllabus: {{ $syllabus->title }}
            <div class="float-end">
                <a href="{{ route('admin.syllabi.show', $syllabus) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye me-1"></i> View
                </a>
                <a href="{{ route('admin.syllabi.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.syllabi.update', $syllabus) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $syllabus->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            @foreach(\App\Models\Syllabus::getStatuses() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $syllabus->status) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $syllabus->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" id="subjectSelect" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $syllabus->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $syllabus->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control" 
                               value="{{ old('publish_date', $syllabus->publish_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $syllabus->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Syllabus
                    </button>
                    <a href="{{ route('admin.syllabi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Topics Section -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-list me-2"></i> Syllabus Topics
            <button type="button" class="btn btn-sm btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addTopicModal">
                <i class="fas fa-plus me-1"></i> Add Topic
            </button>
        </div>
        <div class="card-body">
            @if($syllabus->topics->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="sortable-table">
                        <thead>
                            <tr>
                                <th style="width: 40px">Sort</th>
                                <th>Topic Title</th>
                                <th>Week</th>
                                <th>Sessions</th>
                                <th>Resources</th>
                                <th style="width: 150px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-list">
                            @foreach($syllabus->topics as $topic)
                            <tr data-id="{{ $topic->id }}">
                                <td class="handle text-center">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </td>
                                <td>
                                    <strong>{{ $topic->title }}</strong>
                                    @if($topic->description)
                                        <br><small class="text-muted">{{ Str::limit($topic->description, 60) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $topic->week_number ?? '-' }}</td>
                                <td class="text-center">{{ $topic->session_count }}</td>
                                <td>{{ $topic->resources->count() }} resources</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-topic" 
                                            data-id="{{ $topic->id }}"
                                            data-title="{{ $topic->title }}"
                                            data-description="{{ $topic->description }}"
                                            data-week="{{ $topic->week_number }}"
                                            data-sessions="{{ $topic->session_count }}"
                                            data-objectives="{{ $topic->learning_objectives }}"
                                            data-teaching="{{ $topic->teaching_methods }}"
                                            data-assessment="{{ $topic->assessment_methods }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info resources-btn" 
                                            data-topic-id="{{ $topic->id }}"
                                            data-topic-title="{{ $topic->title }}">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <form action="{{ route('admin.syllabi.topics.destroy', $topic) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No topics added yet. Click "Add Topic" to create one.</p>
            @endif
        </div>
    </div>
</div>

<!-- Add Topic Modal -->
<div class="modal fade" id="addTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.syllabi.topics.store', $syllabus) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Topic Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Week Number</label>
                            <input type="number" name="week_number" class="form-control" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Count</label>
                            <input type="number" name="session_count" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teaching Methods</label>
                        <textarea name="teaching_methods" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assessment Methods</label>
                        <textarea name="assessment_methods" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Topic</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Topic Modal -->
<div class="modal fade" id="editTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTopicForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Topic Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Week Number</label>
                            <input type="number" name="week_number" id="edit_week" class="form-control" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Count</label>
                            <input type="number" name="session_count" id="edit_sessions" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" id="edit_objectives" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teaching Methods</label>
                        <textarea name="teaching_methods" id="edit_teaching" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assessment Methods</label>
                        <textarea name="assessment_methods" id="edit_assessment" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Topic</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resources Modal -->
<div class="modal fade" id="resourcesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resources for <span id="resourceTopicTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resourcesList" class="mb-4">
                    <!-- Resources will be loaded here -->
                </div>
                <hr>
                <h6>Add New Resource</h6>
                <form id="addResourceForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="topic_id" id="resourceTopicId">
                    <div class="mb-3">
                        <label class="form-label">Resource Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="book">Book</option>
                            <option value="article">Article</option>
                            <option value="video">Video</option>
                            <option value="website">Website</option>
                            <option value="document">Document</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL (Optional)</label>
                        <input type="url" name="url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File (Optional)</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Resource</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.css">
<style>
    .handle {
        cursor: move;
    }
    .sortable-placeholder {
        background: #f0f0f0;
        border: 1px dashed #ccc;
        height: 60px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        // Sortable topics
        $("#sortable-list").sortable({
            handle: ".handle",
            placeholder: "sortable-placeholder",
            update: function(event, ui) {
                let orders = [];
                $('#sortable-list tr').each(function(index) {
                    orders.push({
                        id: $(this).data('id'),
                        sort_order: index + 1
                    });
                });
                $.ajax({
                    url: '{{ route("admin.syllabi.topics.reorder", $syllabus) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        topics: orders
                    },
                    success: function(response) {
                        // Optional: show success message
                    }
                });
            }
        });

        // Edit Topic Modal
        $('.edit-topic').click(function() {
            let id = $(this).data('id');
            let title = $(this).data('title');
            let description = $(this).data('description') || '';
            let week = $(this).data('week') || '';
            let sessions = $(this).data('sessions') || 1;
            let objectives = $(this).data('objectives') || '';
            let teaching = $(this).data('teaching') || '';
            let assessment = $(this).data('assessment') || '';

            $('#edit_title').val(title);
            $('#edit_description').val(description);
            $('#edit_week').val(week);
            $('#edit_sessions').val(sessions);
            $('#edit_objectives').val(objectives);
            $('#edit_teaching').val(teaching);
            $('#edit_assessment').val(assessment);
            $('#editTopicForm').attr('action', '{{ url("admin/syllabi/topics") }}/' + id);
            $('#editTopicModal').modal('show');
        });

        // Resources Modal
        $('.resources-btn').click(function() {
            let topicId = $(this).data('topic-id');
            let topicTitle = $(this).data('topic-title');
            $('#resourceTopicId').val(topicId);
            $('#resourceTopicTitle').text(topicTitle);
            $('#addResourceForm').attr('action', '{{ url("admin/syllabi/topics") }}/' + topicId + '/resources');
            
            // Load existing resources
            $.ajax({
                url: '{{ url("admin/syllabi/topics") }}/' + topicId + '/resources/json',
                type: 'GET',
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        html = '<h6>Existing Resources</h6><ul class="list-group">';
                        data.forEach(function(resource) {
                            let resourceLink = '';
                            if (resource.url) {
                                resourceLink = '<a href="' + resource.url + '" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
                            } else if (resource.file_path) {
                                resourceLink = '<a href="/storage/' + resource.file_path + '" target="_blank"><i class="fas fa-download"></i></a>';
                            }
                            html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                                '<div><strong>' + resource.title + '</strong><br><small>' + resource.type + '</small></div>' +
                                '<div>' + resourceLink +
                                '<form action="{{ url("admin/syllabi/resources") }}/' + resource.id + '" method="POST" class="d-inline ms-2">' +
                                '@csrf @method("DELETE")<button class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></button></form></div></li>';
                        });
                        html += '</ul>';
                    } else {
                        html = '<p class="text-muted">No resources yet.</p>';
                    }
                    $('#resourcesList').html(html);
                }
            });
            
            $('#resourcesModal').modal('show');
        });
    });
</script>
@endpush