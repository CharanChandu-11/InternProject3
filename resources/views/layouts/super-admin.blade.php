{{-- resources/views/layouts/super-admin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Panel') - Smart School ERP</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    
    <style>
        /* Same styles as admin layout */
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
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }
        
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
        
        .sidebar.collapsed { margin-left: -280px; }
        
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
        
        .sidebar-header p { font-size: 12px; opacity: 0.7; margin: 5px 0 0; }
        
        .sidebar-menu { padding: 25px 0; }
        .sidebar-menu .menu-item { margin: 5px 0; }
        .sidebar-menu .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu .menu-item a i { width: 25px; margin-right: 12px; font-size: 18px; }
        .sidebar-menu .menu-item a:hover { color: white; background: rgba(255,255,255,0.1); }
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
        .main-content.expanded { margin-left: 0; }
        
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
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 20px 25px;
            font-weight: 600;
            font-size: 18px;
        }
        .card-header i { margin-right: 10px; color: var(--primary); }
        .card-body { padding: 25px; }
        
        /* Buttons */
        .btn { border-radius: 12px; padding: 10px 24px; font-weight: 500; transition: all 0.3s; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(67,97,238,0.3); }
        
        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out; }
        
        @media (max-width: 768px) {
            .sidebar { margin-left: -280px; }
            .sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
    
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Smart School</h3>
            <p>Super Admin Portal</p>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('super-admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.users.index') }}"><i class="fas fa-users"></i> User Management</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.students.index') }}"><i class="fas fa-graduation-cap"></i> Students</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.teachers.index') }}"><i class="fas fa-chalkboard-user"></i> Teachers</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.employees.index') }}"><i class="fas fa-user-tie"></i> Employees</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.parents.index') }}"><i class="fas fa-users"></i> Parents</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.academic-years.index') }}"><i class="fas fa-calendar-alt"></i> Academic Years</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.classes.index') }}"><i class="fas fa-building"></i> Classes</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.subjects.index') }}"><i class="fas fa-book"></i> Subjects</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.timetable.index') }}"><i class="fas fa-clock"></i> Timetable</a>
            </div>
            <div class="menu-item">
                <a href="{{ route('super-admin.attendance.index') }}"><i class="fas fa-calendar-check"></i> Attendance</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-file-alt"></i> Exams</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-rupee-sign"></i> Fees</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-book-open"></i> Library</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-bus"></i> Transport</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-hotel"></i> Hostel</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
            </div>
            <div class="menu-item">
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
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
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">0</span>
                    </button>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 4000
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: `{!! implode('<br>', $errors->all()) !!}`,
                    showConfirmButton: false,
                    timer: 5000
                });
            @endif

        });


        $('.select2').select2();

        $('#sidebarToggle').click(function() {
            $('#sidebar').toggleClass('active');
            $('#mainContent').toggleClass('expanded');
        });
        
        setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>