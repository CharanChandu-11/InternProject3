@extends('website.layouts.app')

@section('title', 'News & Announcements - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>News & Announcements</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">News</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="news-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    @forelse($announcements as $announcement)
                        <div class="news-card mb-4" data-aos="fade-up">
                            <div class="news-date">
                                <span class="day">{{ $announcement->publish_date->format('d') }}</span>
                                <span class="month">{{ $announcement->publish_date->format('M') }}</span>
                                <span class="year">{{ $announcement->publish_date->format('Y') }}</span>
                            </div>
                            <div class="news-content">
                                <h3>{{ $announcement->title }}</h3>
                                <div class="news-meta">
                                    <span><i class="fas fa-user"></i> Admin</span>
                                    <span><i class="fas fa-eye"></i> {{ $announcement->views_count ?? 0 }} views</span>
                                </div>
                                <p>{{ Str::limit(strip_tags($announcement->content), 200) }}</p>
                                <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No announcements available.</p>
                    @endforelse

                    <div class="d-flex justify-content-center mt-4">
                        {{ $announcements->links() }}
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="sidebar-widget">
                        <h4>Recent Posts</h4>
                        <ul class="recent-posts">
                            @foreach($announcements as $announcement)
                                <li>
                                    <a href="#">{{ $announcement->title }}</a>
                                    <span class="date">{{ $announcement->publish_date->format('M d, Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="sidebar-widget">
                        <h4>Categories</h4>
                        <ul class="categories">
                            <li><a href="#">School News</a> ({{ $announcements->count() }})</li>
                            <li><a href="#">Exam Updates</a> (0)</li>
                            <li><a href="#">Holidays</a> (0)</li>
                            <li><a href="#">Achievements</a> (0)</li>
                        </ul>
                    </div>
                    <div class="sidebar-widget">
                        <h4>Newsletter</h4>
                        <form class="newsletter-form">
                            @csrf
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .news-card {
        display: flex;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .news-date {
        text-align: center;
        min-width: 80px;
        margin-right: 20px;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
    }
    .news-date .day {
        font-size: 32px;
        font-weight: bold;
        color: #007bff;
        display: block;
        line-height: 1;
    }
    .news-date .month {
        font-size: 14px;
        text-transform: uppercase;
        color: #6c757d;
    }
    .news-date .year {
        font-size: 12px;
        color: #6c757d;
    }
    .news-content {
        flex: 1;
    }
    .news-content h3 {
        font-size: 22px;
        margin-bottom: 10px;
    }
    .news-meta {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .news-meta span {
        margin-right: 15px;
    }
    .read-more {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        display: inline-block;
        margin-top: 10px;
    }
    .read-more:hover {
        text-decoration: underline;
    }
    .sidebar-widget {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    .sidebar-widget h4 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }
    .recent-posts {
        list-style: none;
        padding: 0;
    }
    .recent-posts li {
        margin-bottom: 12px;
    }
    .recent-posts a {
        color: #333;
        text-decoration: none;
        display: block;
        font-weight: 500;
    }
    .recent-posts a:hover {
        color: #007bff;
    }
    .recent-posts .date {
        font-size: 12px;
        color: #6c757d;
    }
    .categories {
        list-style: none;
        padding: 0;
    }
    .categories li {
        margin-bottom: 8px;
    }
    .categories a {
        color: #333;
        text-decoration: none;
    }
    .categories a:hover {
        color: #007bff;
    }
</style>
@endpush