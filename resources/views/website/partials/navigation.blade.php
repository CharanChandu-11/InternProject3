<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('website.home') }}">
            @if(isset($school) && $school->logo)
                <img src="{{ Storage::url($school->logo) }}" alt="{{ $school->school_name }}" height="60">
            @else
                <h2 class="brand-text">Smart<span>School</span></h2>
            @endif
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}" 
                       href="{{ route('website.home') }}">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.about') ? 'active' : '' }}" 
                       href="{{ route('website.about') }}">
                        <i class="fas fa-info-circle"></i> About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.admissions') ? 'active' : '' }}" 
                       href="{{ route('website.admissions') }}">
                        <i class="fas fa-graduation-cap"></i> Admissions
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-book-open"></i> Academics
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('website.academics') }}">Overview</a></li>
                        <li><a class="dropdown-item" href="#">Curriculum</a></li>
                        <li><a class="dropdown-item" href="#">Calendar</a></li>
                        <li><a class="dropdown-item" href="#">Results</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.faculty') ? 'active' : '' }}" 
                       href="{{ route('website.faculty') }}">
                        <i class="fas fa-chalkboard-teacher"></i> Faculty
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.gallery') ? 'active' : '' }}" 
                       href="{{ route('website.gallery') }}">
                        <i class="fas fa-images"></i> Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.events') ? 'active' : '' }}" 
                       href="{{ route('website.events') }}">
                        <i class="fas fa-calendar-alt"></i> Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.news') ? 'active' : '' }}" 
                       href="{{ route('website.news') }}">
                        <i class="fas fa-newspaper"></i> News
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}" 
                       href="{{ route('website.contact') }}">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>