@extends('layouts.admin')

@section('title', 'Create Store')
@section('page-title', 'Create New Store')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.stores.store') }}" method="POST">
                    @csrf
                    
                    <h6 class="mb-3">Store Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Store Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Store Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>General</option>
                                <option value="grocery" {{ old('type') === 'grocery' ? 'selected' : '' }}>Grocery</option>
                                <option value="clothing" {{ old('type') === 'clothing' ? 'selected' : '' }}>Clothing</option>
                                <option value="department" {{ old('type') === 'department' ? 'selected' : '' }}>Department</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="2">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Store Owner</h6>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create_new_owner" name="create_new_owner" value="1"
                                   {{ old('create_new_owner') ? 'checked' : '' }}>
                            <label class="form-check-label" for="create_new_owner">
                                Create new store owner account
                            </label>
                        </div>
                    </div>

                    <div id="existing-owner-section" class="{{ old('create_new_owner') ? 'd-none' : '' }}">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select Existing Store Owner</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                <option value="">No owner (assign later)</option>
                                @foreach($storeOwners as $owner)
                                    <option value="{{ $owner->id }}" {{ old('user_id') == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }} ({{ $owner->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="new-owner-section" class="{{ old('create_new_owner') ? '' : 'd-none' }}">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="owner_name" class="form-label">Owner Name</label>
                                <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                                       id="owner_name" name="owner_name" value="{{ old('owner_name') }}">
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="owner_email" class="form-label">Owner Email</label>
                                <input type="email" class="form-control @error('owner_email') is-invalid @enderror" 
                                       id="owner_email" name="owner_email" value="{{ old('owner_email') }}">
                                @error('owner_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="owner_password" class="form-label">Owner Password</label>
                            <input type="password" class="form-control @error('owner_password') is-invalid @enderror" 
                                   id="owner_password" name="owner_password">
                            @error('owner_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Store</button>
                        <a href="{{ route('admin.stores.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('create_new_owner').addEventListener('change', function() {
        document.getElementById('existing-owner-section').classList.toggle('d-none', this.checked);
        document.getElementById('new-owner-section').classList.toggle('d-none', !this.checked);
    });
</script>
@endpush
@endsection
