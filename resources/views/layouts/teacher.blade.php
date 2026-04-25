{{-- resources/views/layouts/teacher.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Teacher Panel') - Smart School ERP</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #06ffa5;
            --danger: #ef476f;
            --warning: #ffd166;
            --info: #4cc9f0;
            --dark: #1a1e2b;
            --light: #f8fafc;
            --gray: #64748b;
            --border: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--dark);
        }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #0f1222 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            margin-left: -280px;
        }
        
        .sidebar-header {
            padding: 30px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #fff 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
            margin: 5px 0 0;
        }
        
        .sidebar-menu {
            padding: 25px 0;
        }
        
        .sidebar-menu .menu-item {
            margin: 5px 0;
        }
        
        .sidebar-menu .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu .menu-item a i {
            width: 25px;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .sidebar-menu .menu-item a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar-menu .menu-item.active a {
            color: white;
            background: linear-gradient(90deg, rgba(67,97,238,0.2) 0%, transparent 100%);
            border-left: 3px solid var(--primary);
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Topbar */
        .topbar {
            background: white;
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 20px 25px;
            font-weight: 600;
            font-size: 18px;
        }
        
        .card-header i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .stat-icon i {
            font-size: 24px;
            color: white;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Class Cards */
        .class-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            border: 1px solid var(--border);
            cursor: pointer;
        }
        
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }
        
        /* Attendance Table */
        .attendance-table td {
            vertical-align: middle;
        }
        
        .attendance-status {
            width: 120px;
        }
        
        /* Buttons */
        .btn {
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67,97,238,0.3);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Smart School</h3>
            <p>Teacher Portal</p>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <a href="{{ route('teacher.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
                <a href="{{ route('teacher.classes') }}">
                    <i class="fas fa-chalkboard"></i>
                    <span>My Classes</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.timetable') ? 'active' : '' }}">
                <a href="{{ route('teacher.timetable') }}">
                    <i class="fas fa-clock"></i>
                    <span>My Timetable</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.calendar*') ? 'active' : '' }}">
                <a href="{{ route('teacher.calendar.index') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.syllabi*') ? 'active' : '' }}">
                <a href="{{ route('teacher.syllabi.index') }}">
                    <i class="fas fa-book-open"></i>
                    <span>Syllabi</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.attendance*') ? 'active' : '' }}">
                <a href="{{ route('teacher.attendance.index') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Attendance</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.homework*') ? 'active' : '' }}">
                <a href="{{ route('teacher.homework.index') }}">
                    <i class="fas fa-book"></i>
                    <span>Homework</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.exams*') ? 'active' : '' }}">
                <a href="{{ route('teacher.exams.index') }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Exams</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.students*') ? 'active' : '' }}">
                <a href="{{ route('teacher.students') }}">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
            </div>
            <div class="menu-item {{ request()->routeIs('teacher.parent-leave-requests*') ? 'active' : '' }}">
                <a href="{{ route('teacher.parent-leave-requests.index') }}">
                    <i class="fas fa-user-friends"></i>
                    <span>Parent Leave Requests</span>
                </a>
            </div>

            <div class="menu-item {{ request()->routeIs('teacher.leaves*') ? 'active' : '' }}">
                <a href="{{ route('teacher.leaves.index') }}">
                    <i class="fas fa-umbrella-beach"></i>
                    <span>Leave</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <button class="btn btn-link text-dark" id="sidebarToggle">
                <i class="fas fa-bars fs-4"></i>
            </button>
            
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            {{ \App\Models\Notification::where('is_read', false)->count() }}
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 300px;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            @foreach(\App\Models\Notification::latest()->take(5)->get() as $notif)
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $notif->title }}</h6>
                                        <small>{{ $notif->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 small">{{ Str::limit($notif->message, 50) }}</p>
                                </a>
                            @endforeach
                        </div>
                        <div class="p-2 text-center border-top">
                            <a href="#" class="text-decoration-none small">View all</a>
                        </div>
                    </div>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        
        $('#sidebarToggle').click(function() {
            $('#sidebar').toggleClass('active');
            $('#mainContent').toggleClass('expanded');
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // DataTables initialization
        $('.datatable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>