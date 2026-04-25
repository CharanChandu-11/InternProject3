{{-- resources/views/admin/announcements/show.blade.php --}}
@extends('layouts.admin')

@section('title', $announcement->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-file-alt me-2"></i> Announcement Details
            <div class="float-end">
                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2 class="mb-3">{{ $announcement->title }}</h2>
                    <div class="d-flex gap-3 text-muted mb-3">
                        <span><i class="fas fa-user me-1"></i> {{ $announcement->creator?->name ?? 'System' }}</span>
                        <span><i class="fas fa-calendar me-1"></i> Created: {{ $announcement->created_at->format('M d, Y H:i') }}</span>
                        <span><i class="fas fa-calendar-check me-1"></i> Updated: {{ $announcement->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    @php
                        $now = now();
                        $status = 'draft';
                        $statusColor = 'secondary';
                        
                        if ($announcement->is_published) {
                            if ($announcement->publish_date > $now) {
                                $status = 'scheduled';
                                $statusColor = 'warning';
                            } elseif ($announcement->expiry_date && $announcement->expiry_date < $now) {
                                $status = 'expired';
                                $statusColor = 'dark';
                            } else {
                                $status = 'published';
                                $statusColor = 'success';
                            }
                        }
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">{{ ucfirst($status) }}</span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-box p-3 bg-light rounded">
                        <h6><i class="fas fa-users me-2 text-primary"></i> Audience</h6>
                        @php
                            $audienceLabels = [
                                'all' => 'Everyone',
                                'students' => 'Students Only',
                                'parents' => 'Parents Only',
                                'teachers' => 'Teachers Only',
                                'employees' => 'Employees Only',
                                'specific_classes' => 'Specific Classes'
                            ];
                        @endphp
                        <p class="mb-0">{{ $audienceLabels[$announcement->audience] ?? ucfirst($announcement->audience) }}</p>
                        @if($announcement->audience == 'specific_classes' && $announcement->specific_classes)
                            <small class="text-muted">{{ count($announcement->specific_classes) }} class(es) selected</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box p-3 bg-light rounded">
                        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Schedule</h6>
                        <p class="mb-0"><strong>Publish:</strong> {{ $announcement->publish_date->format('M d, Y H:i') }}</p>
                        @if($announcement->expiry_date)
                            <p class="mb-0"><strong>Expiry:</strong> {{ $announcement->expiry_date->format('M d, Y H:i') }}</p>
                        @else
                            <p class="mb-0 text-muted"><em>No expiry date set</em></p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Content</h6>
                </div>
                <div class="card-body">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this announcement?')">
                        <i class="fas fa-trash me-1"></i> Delete Announcement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection