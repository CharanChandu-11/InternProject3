{{-- resources/views/admin/settings/partials/general.blade.php --}}
<form action="{{ route('admin.settings.general') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">School Name <span class="text-danger">*</span></label>
            <input type="text" name="school_name" class="form-control" 
                   value="{{ old('school_name', $settings->school_name ?? '') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">School Code <span class="text-danger">*</span></label>
            <input type="text" name="school_code" class="form-control" 
                   value="{{ old('school_code', $settings->school_code ?? '') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Affiliation Number</label>
            <input type="text" name="affiliation_number" class="form-control" 
                   value="{{ old('affiliation_number', $settings->affiliation_number ?? '') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Board <span class="text-danger">*</span></label>
            <select name="board" class="form-select" required>
                <option value="CBSE" {{ (old('board', $settings->board ?? '') == 'CBSE') ? 'selected' : '' }}>CBSE</option>
                <option value="ICSE" {{ (old('board', $settings->board ?? '') == 'ICSE') ? 'selected' : '' }}>ICSE</option>
                <option value="IB" {{ (old('board', $settings->board ?? '') == 'IB') ? 'selected' : '' }}>IB</option>
                <option value="State Board" {{ (old('board', $settings->board ?? '') == 'State Board') ? 'selected' : '' }}>State Board</option>
            </select>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">Address <span class="text-danger">*</span></label>
            <textarea name="address" class="form-control" rows="2" required>{{ old('address', $settings->address ?? '') }}</textarea>
        </div>
        
        <div class="col-md-4 mb-3">
            <label class="form-label">City <span class="text-danger">*</span></label>
            <input type="text" name="city" class="form-control" 
                   value="{{ old('city', $settings->city ?? '') }}" required>
        </div>
        
        <div class="col-md-4 mb-3">
            <label class="form-label">State <span class="text-danger">*</span></label>
            <input type="text" name="state" class="form-control" 
                   value="{{ old('state', $settings->state ?? '') }}" required>
        </div>
        
        <div class="col-md-4 mb-3">
            <label class="form-label">Pincode <span class="text-danger">*</span></label>
            <input type="text" name="pincode" class="form-control" 
                   value="{{ old('pincode', $settings->pincode ?? '') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Phone <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" 
                   value="{{ old('phone', $settings->phone ?? '') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" 
                   value="{{ old('email', $settings->email ?? '') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Website</label>
            <input type="url" name="website" class="form-control" 
                   value="{{ old('website', $settings->website ?? '') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Established Year</label>
            <input type="number" name="established_year" class="form-control" 
                   value="{{ old('established_year', $settings->established_year ?? '') }}" min="1900" max="{{ date('Y') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Principal Name</label>
            <input type="text" name="principal_name" class="form-control" 
                   value="{{ old('principal_name', $settings->principal_name ?? '') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Principal Message</label>
            <textarea name="principal_message" class="form-control" rows="2">{{ old('principal_message', $settings->principal_message ?? '') }}</textarea>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">School Logo</label>
            @if(isset($settings) && $settings->logo)
                <div class="mb-2">
                    <img src="{{ Storage::url($settings->logo) }}" alt="School Logo" style="height: 80px;">
                </div>
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
            <small class="text-muted">Recommended size: 200x200px</small>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">About School</label>
            <textarea name="about_school" class="form-control" rows="3">{{ old('about_school', $settings->about_school ?? '') }}</textarea>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">Mission Statement</label>
            <textarea name="mission_statement" class="form-control" rows="2">{{ old('mission_statement', $settings->mission_statement ?? '') }}</textarea>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">Vision Statement</label>
            <textarea name="vision_statement" class="form-control" rows="2">{{ old('vision_statement', $settings->vision_statement ?? '') }}</textarea>
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save General Settings
            </button>
        </div>
    </div>
</form>