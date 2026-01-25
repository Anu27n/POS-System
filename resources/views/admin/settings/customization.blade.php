@extends('layouts.admin')

@section('title', 'Customization Settings')
@section('page-title', 'App Customization')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.settings.customization.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">App Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                               name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required>
                        @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">This name will appear in the header and page titles.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tagline</label>
                        <input type="text" class="form-control @error('app_tagline') is-invalid @enderror" 
                               name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline']) }}"
                               placeholder="Your business tagline">
                        @error('app_tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Footer Text</label>
                        <textarea class="form-control @error('footer_text') is-invalid @enderror" 
                                  name="footer_text" rows="2"
                                  placeholder="Copyright text or additional info">{{ old('footer_text', $settings['footer_text']) }}</textarea>
                        @error('footer_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control @error('app_phone') is-invalid @enderror" 
                                           name="app_phone" value="{{ old('app_phone', $settings['app_phone']) }}"
                                           placeholder="+1 234 567 890">
                                </div>
                                @error('app_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control @error('app_email') is-invalid @enderror" 
                                           name="app_email" value="{{ old('app_email', $settings['app_email']) }}"
                                           placeholder="contact@example.com">
                                </div>
                                @error('app_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <textarea class="form-control @error('app_address') is-invalid @enderror" 
                                      name="app_address" rows="2"
                                      placeholder="123 Business Street, City, Country">{{ old('app_address', $settings['app_address']) }}</textarea>
                        </div>
                        @error('app_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Branding -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Branding</h6>
                </div>
                <div class="card-body">
                    <!-- Logo -->
                    <div class="mb-4">
                        <label class="form-label">App Logo</label>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($settings['app_logo'])
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/' . $settings['app_logo']) }}" 
                                             alt="App Logo" class="img-thumbnail" style="max-height: 80px;">
                                    </div>
                                @else
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 80px;">
                                        <i class="bi bi-image text-muted fs-3"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <input type="file" class="form-control @error('app_logo') is-invalid @enderror" 
                                       name="app_logo" accept="image/*">
                                @error('app_logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 200x50px, PNG or SVG. Max 2MB.</small>
                                @if($settings['app_logo'])
                                    <div class="mt-2">
                                        <a href="{{ route('admin.settings.customization.remove-logo') }}" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Remove current logo?')">
                                            <i class="bi bi-trash me-1"></i> Remove Logo
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div class="mb-3">
                        <label class="form-label">Favicon</label>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($settings['app_favicon'])
                                    <img src="{{ asset('storage/' . $settings['app_favicon']) }}" 
                                         alt="Favicon" class="img-thumbnail" style="max-height: 48px;">
                                @else
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                         style="width: 48px; height: 48px;">
                                        <i class="bi bi-app text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <input type="file" class="form-control @error('app_favicon') is-invalid @enderror" 
                                       name="app_favicon" accept="image/x-icon,image/png,image/jpeg,image/gif">
                                @error('app_favicon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 32x32px or 16x16px, ICO or PNG. Max 512KB.</small>
                                @if($settings['app_favicon'])
                                    <div class="mt-2">
                                        <a href="{{ route('admin.settings.customization.remove-favicon') }}" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Remove current favicon?')">
                                            <i class="bi bi-trash me-1"></i> Remove Favicon
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Sidebar -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 1rem;">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Current Settings</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">App Name:</td>
                        <td class="fw-semibold">{{ $settings['app_name'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone:</td>
                        <td>{{ $settings['app_phone'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>{{ $settings['app_email'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address:</td>
                        <td>{{ $settings['app_address'] ?: '-' }}</td>
                    </tr>
                </table>

                <hr>

                <h6 class="mb-3">Logo Preview</h6>
                @if($settings['app_logo'])
                    <img src="{{ asset('storage/' . $settings['app_logo']) }}" 
                         alt="Logo" class="img-fluid mb-3" style="max-height: 60px;">
                @else
                    <p class="text-muted mb-3"><i class="bi bi-image me-1"></i> No logo uploaded</p>
                @endif

                <h6 class="mb-3">Favicon Preview</h6>
                @if($settings['app_favicon'])
                    <img src="{{ asset('storage/' . $settings['app_favicon']) }}" 
                         alt="Favicon" style="width: 32px; height: 32px;">
                @else
                    <p class="text-muted"><i class="bi bi-app me-1"></i> No favicon uploaded</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
