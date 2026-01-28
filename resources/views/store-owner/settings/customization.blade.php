@extends('layouts.store-owner')

@section('title', 'Store Customization')
@section('page-title', 'Store Customization')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('store-owner.customization.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Store Logo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Store Logo</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if($store->logo)
                                <img src="{{ asset('storage/' . $store->logo) }}" 
                                     alt="Store Logo" class="img-thumbnail" style="max-height: 100px;">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px;">
                                    <i class="bi bi-shop text-muted fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <div class="mb-2">
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Recommended: Square image, at least 200x200px. Max 2MB.</small>
                            @if($store->logo)
                                <div class="mt-2">
                                    <a href="{{ route('store-owner.customization.remove-logo') }}" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Remove current logo?')">
                                        <i class="bi bi-trash me-1"></i> Remove Logo
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Color Scheme -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Color Scheme</h6>
                    <a href="{{ route('store-owner.customization.reset-colors') }}" 
                       class="btn btn-sm btn-outline-secondary"
                       onclick="return confirm('Reset all colors to default?')">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Customize the colors of your online ordering page to match your brand identity.
                    </p>

                    <div class="row g-4">
                        <!-- Primary Color -->
                        <div class="col-md-4">
                            <label class="form-label">Primary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" 
                                       id="primary_color" name="primary_color" 
                                       value="{{ old('primary_color', $store->primary_color ?? '#4F46E5') }}"
                                       style="min-width: 60px;">
                                <input type="text" class="form-control" 
                                       value="{{ old('primary_color', $store->primary_color ?? '#4F46E5') }}"
                                       id="primary_color_text" maxlength="7"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            <small class="text-muted">Used for buttons, links, and headers</small>
                        </div>

                        <!-- Secondary Color -->
                        <div class="col-md-4">
                            <label class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" 
                                       id="secondary_color" name="secondary_color" 
                                       value="{{ old('secondary_color', $store->secondary_color ?? '#1E293B') }}"
                                       style="min-width: 60px;">
                                <input type="text" class="form-control" 
                                       value="{{ old('secondary_color', $store->secondary_color ?? '#1E293B') }}"
                                       id="secondary_color_text" maxlength="7"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            <small class="text-muted">Used for text and backgrounds</small>
                        </div>

                        <!-- Accent Color -->
                        <div class="col-md-4">
                            <label class="form-label">Accent Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" 
                                       id="accent_color" name="accent_color" 
                                       value="{{ old('accent_color', $store->accent_color ?? '#10B981') }}"
                                       style="min-width: 60px;">
                                <input type="text" class="form-control" 
                                       value="{{ old('accent_color', $store->accent_color ?? '#10B981') }}"
                                       id="accent_color_text" maxlength="7"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            <small class="text-muted">Used for success states and highlights</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Font Family -->
                    <div class="mb-3">
                        <label class="form-label">Font Family</label>
                        <select class="form-select @error('font_family') is-invalid @enderror" name="font_family">
                            <option value="">Default (System Font)</option>
                            <option value="Inter" {{ ($store->font_family ?? '') == 'Inter' ? 'selected' : '' }}>Inter</option>
                            <option value="Roboto" {{ ($store->font_family ?? '') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                            <option value="Open Sans" {{ ($store->font_family ?? '') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                            <option value="Lato" {{ ($store->font_family ?? '') == 'Lato' ? 'selected' : '' }}>Lato</option>
                            <option value="Poppins" {{ ($store->font_family ?? '') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                            <option value="Montserrat" {{ ($store->font_family ?? '') == 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                            <option value="Nunito" {{ ($store->font_family ?? '') == 'Nunito' ? 'selected' : '' }}>Nunito</option>
                        </select>
                        @error('font_family')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Choose a font for your store page</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('store-owner.settings.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Live Preview -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 1rem;">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Live Preview</h6>
            </div>
            <div class="card-body p-0">
                <div id="preview-container" class="p-3" style="background: #f8fafc;">
                    <!-- Preview Header -->
                    <div id="preview-header" class="rounded-top p-3 text-white mb-3" 
                         style="background: {{ $store->primary_color ?? '#4F46E5' }};">
                        <div class="d-flex align-items-center">
                            @if($store->logo)
                                <img src="{{ asset('storage/' . $store->logo) }}" 
                                     alt="Logo" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-shop"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0" id="preview-store-name">{{ $store->name }}</h6>
                                <small class="opacity-75">{{ ucfirst($store->type) }} Store</small>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Content -->
                    <div class="bg-white rounded p-3" id="preview-content">
                        <h6 id="preview-title" style="color: {{ $store->secondary_color ?? '#1E293B' }};">Featured Products</h6>
                        
                        <!-- Sample Product Card -->
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex">
                                <div class="bg-light rounded me-2" style="width: 50px; height: 50px;"></div>
                                <div class="flex-grow-1">
                                    <small class="fw-semibold" style="color: {{ $store->secondary_color ?? '#1E293B' }};">Sample Product</small>
                                    <div class="small" id="preview-price" style="color: {{ $store->primary_color ?? '#4F46E5' }};">$19.99</div>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Button -->
                        <button type="button" class="btn btn-sm w-100" id="preview-button"
                                style="background: {{ $store->primary_color ?? '#4F46E5' }}; color: white;">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>

                        <!-- Sample Success -->
                        <div class="mt-2 p-2 rounded small" id="preview-success"
                             style="background: {{ $store->accent_color ?? '#10B981' }}20; color: {{ $store->accent_color ?? '#10B981' }};">
                            <i class="bi bi-check-circle me-1"></i> Added to cart!
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View Live Store
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Sync color pickers with text inputs
    function syncColorInputs(colorId, textId) {
        const colorInput = document.getElementById(colorId);
        const textInput = document.getElementById(textId);
        
        if (colorInput && textInput) {
            colorInput.addEventListener('input', function() {
                textInput.value = this.value.toUpperCase();
                updatePreview();
            });
            
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    colorInput.value = this.value;
                    updatePreview();
                }
            });
        }
    }

    syncColorInputs('primary_color', 'primary_color_text');
    syncColorInputs('secondary_color', 'secondary_color_text');
    syncColorInputs('accent_color', 'accent_color_text');

    function updatePreview() {
        const primary = document.getElementById('primary_color').value;
        const secondary = document.getElementById('secondary_color').value;
        const accent = document.getElementById('accent_color').value;

        document.getElementById('preview-header').style.background = primary;
        document.getElementById('preview-button').style.background = primary;
        document.getElementById('preview-price').style.color = primary;
        document.getElementById('preview-title').style.color = secondary;
        document.getElementById('preview-success').style.background = accent + '20';
        document.getElementById('preview-success').style.color = accent;
    }
</script>
@endpush
@endsection
