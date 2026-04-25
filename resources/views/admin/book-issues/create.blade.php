{{-- resources/views/admin/book-issues/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Issue Book')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-hand-holding-heart me-2"></i> Issue New Book
        </div>
        <div class="card-body">
            <form action="{{ route('admin.book-issues.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="book_id" class="form-label">Select Book <span class="text-danger">*</span></label>
                        <select name="book_id" id="book_id" class="form-select @error('book_id') is-invalid @enderror" required>
                            <option value="">Select a book</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                                    {{ $book->title }} ({{ $book->author }}) - Available: {{ $book->available_quantity }}
                                </option>
                            @endforeach
                        </select>
                        @error('book_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="issuable_type" class="form-label">Issuer Type <span class="text-danger">*</span></label>
                        <select name="issuable_type" id="issuable_type" class="form-select @error('issuable_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="student" {{ old('issuable_type', $selectedType) == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('issuable_type', $selectedType) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="employee" {{ old('issuable_type', $selectedType) == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                        @error('issuable_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="issuable_id" class="form-label">Select Issuer <span class="text-danger">*</span></label>
                        <div id="issuer_select_container">
                            <select name="issuable_id" id="issuable_id" class="form-select @error('issuable_id') is-invalid @enderror" required>
                                <option value="">First select issuer type</option>
                            </select>
                        </div>
                        @error('issuable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" value="{{ old('remarks') }}">
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                
                <div id="issuer_info" class="alert alert-info mt-3" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="issuer_details"></span>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Issue Book</button>
                    <a href="{{ route('admin.book-issues.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load issuers based on selected type
    $('#issuable_type').change(function() {
        var type = $(this).val();
        var select = $('#issuable_id');
        
        if (type) {
            $.ajax({
                url: '{{ route("admin.book-issues.get-issuers") }}',
                type: 'GET',
                data: { type: type },
                success: function(data) {
                    select.empty();
                    select.append('<option value="">Select ' + type.charAt(0).toUpperCase() + type.slice(1) + '</option>');
                    $.each(data, function(key, value) {
                        select.append('<option value="' + value.id + '">' + value.name + ' (' + value.email + ')</option>');
                    });
                    $('#issuer_info').hide();
                }
            });
        } else {
            select.empty();
            select.append('<option value="">First select issuer type</option>');
            $('#issuer_info').hide();
        }
    });
    
    // Load issuer details on selection
    $('#issuable_id').change(function() {
        var type = $('#issuable_type').val();
        var id = $(this).val();
        
        if (type && id) {
            $.ajax({
                url: '{{ route("admin.book-issues.get-issuer") }}',
                type: 'GET',
                data: { type: type, id: id },
                success: function(data) {
                    $('#issuer_details').html('<strong>' + data.name + '</strong><br>' + data.email + ' | ' + data.phone + '<br>' + data.address);
                    $('#issuer_info').show();
                }
            });
        }
    });
    
    // Preload if type and ID are preselected
    @if($selectedType && $selectedId)
        $('#issuable_type').val('{{ $selectedType }}').trigger('change');
        setTimeout(function() {
            $('#issuable_id').val('{{ $selectedId }}').trigger('change');
        }, 500);
    @endif
});
</script>
@endpush