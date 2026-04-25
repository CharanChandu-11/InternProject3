{{-- resources/views/teacher/syllabi/show.blade.php --}}
@extends('layouts.teacher')

@section('title', $syllabus->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-book-open me-2"></i> {{ $syllabus->subject->name }} Syllabus
            <div class="float-end">
                <a href="{{ route('teacher.syllabi.index') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>{{ $syllabus->title }}</h4>
                    <p class="text-muted">{{ $syllabus->description ?? 'No description provided.' }}</p>
                    
                    <hr class="my-4">
                    <h5><i class="fas fa-list me-2 text-primary"></i> Topics Covered</h5>
                    
                    @if($syllabus->topics->count() > 0)
                        <div class="accordion" id="topicsAccordion">
                            @foreach($syllabus->topics as $index => $topic)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $topic->id }}">
                                        <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $topic->id }}">
                                            <div>
                                                <strong>{{ $topic->title }}</strong>
                                                @if($topic->week_number)
                                                    <span class="badge bg-secondary ms-2">Week {{ $topic->week_number }}</span>
                                                @endif
                                                @if($topic->session_count)
                                                    <span class="badge bg-info ms-2">{{ $topic->session_count }} sessions</span>
                                                @endif
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $topic->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                         data-bs-parent="#topicsAccordion">
                                        <div class="accordion-body">
                                            @if($topic->description)
                                                <div class="mb-3">
                                                    <strong>Description:</strong>
                                                    <p>{{ $topic->description }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($topic->learning_objectives)
                                                <div class="mb-3">
                                                    <strong><i class="fas fa-bullseye me-1 text-primary"></i> Learning Objectives:</strong>
                                                    <p>{{ $topic->learning_objectives }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($topic->teaching_methods)
                                                <div class="mb-3">
                                                    <strong><i class="fas fa-chalkboard-user me-1 text-primary"></i> Teaching Methods:</strong>
                                                    <p>{{ $topic->teaching_methods }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($topic->assessment_methods)
                                                <div class="mb-3">
                                                    <strong><i class="fas fa-clipboard-list me-1 text-primary"></i> Assessment Methods:</strong>
                                                    <p>{{ $topic->assessment_methods }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($topic->resources->count() > 0)
                                                <div class="mt-3">
                                                    <strong><i class="fas fa-paperclip me-1 text-primary"></i> Resources:</strong>
                                                    <ul class="list-group mt-2">
                                                        @foreach($topic->resources as $resource)
                                                            <li class="list-group-item">
                                                                @if($resource->type == 'book')
                                                                    <i class="fas fa-book text-primary me-2"></i>
                                                                @elseif($resource->type == 'video')
                                                                    <i class="fas fa-video text-danger me-2"></i>
                                                                @elseif($resource->type == 'website')
                                                                    <i class="fas fa-globe text-success me-2"></i>
                                                                @else
                                                                    <i class="fas fa-paperclip text-muted me-2"></i>
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
                        <p class="text-muted">No topics defined for this syllabus.</p>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle me-2 text-primary"></i> Information</h6>
                            <hr>
                            <p><strong><i class="fas fa-building me-2"></i> Class:</strong><br>{{ $syllabus->class->name }}</p>
                            <p><strong><i class="fas fa-book me-2"></i> Subject:</strong><br>{{ $syllabus->subject->name }} ({{ $syllabus->subject->code }})</p>
                            <p><strong><i class="fas fa-calendar-alt me-2"></i> Academic Year:</strong><br>{{ $syllabus->academicYear->name }}</p>
                            <p><strong><i class="fas fa-user me-2"></i> Created By:</strong><br>{{ $syllabus->creator->name }}</p>
                            <p><strong><i class="fas fa-calendar-plus me-2"></i> Created At:</strong><br>{{ $syllabus->created_at->format('d-m-Y') }}</p>
                            @if($syllabus->publish_date)
                                <p><strong><i class="fas fa-calendar-check me-2"></i> Published On:</strong><br>{{ $syllabus->publish_date->format('d-m-Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    .list-group-item {
        transition: background-color 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush