{{-- resources/views/admin/fee-structures/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Fee Structures')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-money-bill-wave me-2"></i> Fee Structures
            <div class="float-end">
                <a href="{{ route('admin.fee-structures.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Fee Structure
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.fee-structures.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class</th>
                            <th>Fee Category</th>
                            <th>Amount (₹)</th>
                            <th>Frequency</th>
                            <th>Optional</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feeStructures as $structure)
                        <tr>
                            <td>{{ $structure->id }}</td>
                            <td class="fw-bold">{{ $structure->class->name ?? 'N/A' }}</td>
                            <td>{{ $structure->feeCategory->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-success">₹ {{ number_format($structure->amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $structure->frequency)) }}</span>
                            </td>
                            <td>
                                @if($structure->is_optional)
                                    <span class="badge bg-warning">Optional</span>
                                @else
                                    <span class="badge bg-secondary">Mandatory</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.fee-structures.edit', $structure) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.fee-structures.destroy', $structure) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this fee structure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $feeStructures->links() }}
        </div>
    </div>
</div>
@endsection