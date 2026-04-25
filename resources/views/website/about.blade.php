@extends('website.layouts.app')

@section('title', 'About Us - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>About Us</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">About Us</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="about-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <img src="{{ asset('images/about-large.jpg') }}" alt="About School" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2>Welcome to Smart School</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>
                    
                    <div class="mission-vision mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mission-box">
                                    <i class="fas fa-bullseye"></i>
                                    <h4>Our Mission</h4>
                                    <p>To provide quality education and nurture young minds for a better future.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="vision-box">
                                    <i class="fas fa-eye"></i>
                                    <h4>Our Vision</h4>
                                    <p>To be a center of excellence in education and character building.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="leadership-section py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Our Leadership</h2>
            <div class="row">
                @forelse($leadership as $leader)
                    <div class="col-lg-3 col-md-6">
                        <div class="leader-card">
                            <img src="{{ $leader->user->profile_photo_url }}" alt="{{ $leader->user->name }}">
                            <h4>{{ $leader->user->name }}</h4>
                            <p>{{ $leader->designation }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center">No leadership information available.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
    }
    .page-header h1 {
        font-size: 48px;
        margin-bottom: 15px;
    }
    .page-header .breadcrumb {
        background: transparent;
        justify-content: center;
    }
    .page-header .breadcrumb-item,
    .page-header .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
    }
    .page-header .breadcrumb-item.active {
        color: white;
    }
    .mission-box, .vision-box {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        height: 100%;
    }
    .mission-box i, .vision-box i {
        font-size: 40px;
        color: #007bff;
        margin-bottom: 15px;
    }
    .leader-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .leader-card img {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }
    .leader-card h4 {
        margin: 15px 0 5px;
    }
    .leader-card p {
        color: #6c757d;
        margin-bottom: 20px;
    }
</style>
@endpush