{{-- resources/views/admin/syllabi/show.blade.php --}}
@extends('layouts.admin')

@section('title', $syllabus->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Syllabus Details: {{ $syllabus->title }}
            <div class="float-end">
                <a href="{{ route('admin.syllabi.edit', $syllabus) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.syllabi.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>{{ $syllabus->title }}</h4>
                    <p>{{ $syllabus->description ?? 'No description provided.' }}</p>

                    <hr class="my-4">
                    <h5>Topics</h5>

                    @if($syllabus->topics->count() > 0)
                        <div class="accordion" id="topicsAccordion">
                            @foreach($syllabus->topics as $index => $topic)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $topic->id }}">
                                        <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $topic->id }}">
                                            <strong>{{ $topic->title }}</strong>
                                            @if($topic->week_number)
                                                <span class="badge bg-secondary ms-2">Week {{ $topic->week_number }}</span>
                                            @endif
                                            @if($topic->session_count)
                                                <span class="badge bg-info ms-2">{{ $topic->session_count }} sessions</span>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $topic->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                         data-bs-parent="#topicsAccordion">
                                        <div class="accordion-body">
                                            @if($topic->description)
                                                <p><strong>Description:</strong> {{ $topic->description }}</p>
                                            @endif

                                            @if($topic->learning_objectives)
                                                <div class="mb-3">
                                                    <strong>Learning Objectives:</strong>
                                                    <p>{{ $topic->learning_objectives }}</p>
                                                </div>
                                            @endif

                                            @if($topic->teaching_methods)
                                                <div class="mb-3">
                                                    <strong>Teaching Methods:</strong>
                                                    <p>{{ $topic->teaching_methods }}</p>
                                                </div>
                                            @endif

                                            @if($topic->assessment_methods)
                                                <div class="mb-3">
                                                    <strong>Assessment Methods:</strong>
                                                    <p>{{ $topic->assessment_methods }}</p>
                                                </div>
                                            @endif

                                            @if($topic->resources->count() > 0)
                                                <div class="mt-3">
                                                    <strong>Resources:</strong>
                                                    <ul>
                                                        @foreach($topic->resources as $resource)
                                                            <li>
                                                                @if($resource->type == 'book')
                                                                    <i class="fas fa-book text-primary"></i>
                                                                @elseif($resource->type == 'video')
                                                                    <i class="fas fa-video text-danger"></i>
                                                                @elseif($resource->type == 'website')
                                                                    <i class="fas fa-globe text-success"></i>
                                                                @else
                                                                    <i class="fas fa-paperclip"></i>
                                                                @endif

                                                                {{ $resource->title }}

                                                                @if($resource->url)
                                                                    <a href="{{ $resource->url }}" target="_blank" class="ms-2">
                                                                        <i class="fas fa-external-link-alt"></i>
                                                                    </a>
                                                                @endif

                                                                @if($resource->file_path)
                                                                    <a href="{{ Storage::url($resource->file_path) }}" target="_blank" class="ms-2">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                @endif

                                                                @if($resource->description)
                                                                    <br><small class="text-muted">{{ $resource->description }}</small>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No topics added yet.</p>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Information</h6>
                            <hr>
                            <p><strong>Class:</strong> {{ $syllabus->class->name }}</p>
                            <p><strong>Subject:</strong> {{ $syllabus->subject->name }} ({{ $syllabus->subject->code }})</p>
                            <p><strong>Academic Year:</strong> {{ $syllabus->academicYear->name }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $syllabus->status == 'published' ? 'success' : ($syllabus->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($syllabus->status) }}
                                </span>
                            </p>
                            @if($syllabus->publish_date)
                                <p><strong>Publish Date:</strong> {{ $syllabus->publish_date->format('d-m-Y') }}</p>
                            @endif
                            <p><strong>Created By:</strong> {{ $syllabus->creator->name }}</p>
                            <p><strong>Created At:</strong> {{ $syllabus->created_at->format('d-m-Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection