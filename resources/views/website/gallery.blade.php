@extends('website.layouts.app')

@section('title', 'Gallery - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Photo Gallery</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Gallery</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="gallery-content py-5">
        <div class="container">
            <div class="category-filters text-center mb-4">
                <button class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
                @foreach($categories as $cat)
                    <button class="btn btn-outline-primary filter-btn" data-filter="{{ $cat }}">{{ ucwords(str_replace('_', ' ', $cat)) }}</button>
                @endforeach
            </div>

            <div class="row gallery-grid">
                @forelse($images as $image)
                    <div class="col-lg-3 col-md-4 col-sm-6 gallery-item" data-category="{{ $image->category }}">
                        <a href="{{ $image->image_url }}" data-lightbox="gallery" data-title="{{ $image->title }}">
                            <img src="{{ $image->thumbnail_url }}" alt="{{ $image->title }}" class="img-fluid">
                            <div class="gallery-overlay">
                                <h5>{{ $image->title }}</h5>
                                <p>{{ $image->description }}</p>
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No images available in the gallery.</p>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $images->links() }}
            </div>
        </div>
    </section>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<style>
    .category-filters .btn {
        margin: 5px;
        border-radius: 30px;
        padding: 8px 20px;
        transition: all 0.3s;
    }
    .category-filters .btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        margin-bottom: 20px;
        cursor: pointer;
    }
    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .gallery-item:hover img {
        transform: scale(1.1);
    }
    .gallery-overlay {
        position: absolute;
        bottom: -100%;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
        padding: 15px;
        transition: bottom 0.3s;
        text-align: center;
    }
    .gallery-item:hover .gallery-overlay {
        bottom: 0;
    }
    .gallery-overlay h5 {
        font-size: 14px;
        margin-bottom: 5px;
    }
    .gallery-overlay p {
        font-size: 12px;
        margin-bottom: 10px;
    }
    .gallery-overlay i {
        font-size: 20px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
    $(document).ready(function() {
        $('.filter-btn').click(function() {
            var filter = $(this).data('filter');
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            if (filter === 'all') {
                $('.gallery-item').show();
            } else {
                $('.gallery-item').hide();
                $('.gallery-item[data-category="' + filter + '"]').show();
            }
        });
    });
</script>
@endpush