{{-- resources/views/admin/announcements/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bullhorn me-2"></i> Announcement Management
            <div class="float-end">
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Announcement
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by title or content..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Publish Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                        <tr>
                            <td class="fw-bold">{{ Str::limit($announcement->title, 50) }}</td>
                            <td>
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
                                <span class="badge bg-info">{{ $audienceLabels[$announcement->audience] ?? ucfirst($announcement->audience) }}</span>
                                @if($announcement->audience == 'specific_classes' && $announcement->specific_classes)
                                    <br><small class="text-muted">{{ count($announcement->specific_classes) }} class(es)</small>
                                @endif
                            </td>
                            <td>{{ $announcement->publish_date->format('M d, Y') }}</td>
                            <td>{{ $announcement->expiry_date ? $announcement->expiry_date->format('M d, Y') : 'Never' }}</td>
                            <td>
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
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td>{{ $announcement->creator?->name ?? 'System' }}</td>
                            <td>
                                <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No announcements found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection