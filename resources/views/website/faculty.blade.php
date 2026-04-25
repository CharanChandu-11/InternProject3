@extends('website.layouts.app')

@section('title', 'Faculty - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Our Faculty</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Faculty</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="faculty-content py-5">
        <div class="container">
            <div class="row">
                @forelse($teachers as $teacher)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}">
                            </div>
                            <div class="faculty-info">
                                <h4>{{ $teacher->name }}</h4>
                                <p class="designation">{{ $teacher->employee?->designation ?? 'Teacher' }}</p>
                                <p class="qualification">{{ $teacher->profile?->qualification ?? '' }}</p>
                                <div class="faculty-subjects">
                                    @php
                                        $subjects = \App\Models\ClassSubject::where('teacher_id', $teacher->id)
                                            ->with('subject')
                                            ->get()
                                            ->pluck('subject.name')
                                            ->unique()
                                            ->take(3);
                                    @endphp
                                    @foreach($subjects as $subject)
                                        <span class="badge bg-primary">{{ $subject }}</span>
                                    @endforeach
                                </div>
                                <div class="social-links mt-2">
                                    <a href="#" class="text-primary"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No faculty information available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .faculty-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }
    .faculty-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .faculty-image {
        height: 250px;
        overflow: hidden;
    }
    .faculty-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .faculty-card:hover .faculty-image img {
        transform: scale(1.05);
    }
    .faculty-info {
        padding: 20px;
        text-align: center;
    }
    .faculty-info h4 {
        font-size: 18px;
        margin-bottom: 5px;
        color: #333;
    }
    .designation {
        color: #007bff;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .qualification {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .faculty-subjects .badge {
        font-size: 11px;
        margin: 2px;
    }
    .social-links a {
        display: inline-block;
        margin: 0 5px;
        font-size: 14px;
        transition: color 0.3s;
    }
    .social-links a:hover {
        color: #0056b3 !important;
    }
</style>
@endpush