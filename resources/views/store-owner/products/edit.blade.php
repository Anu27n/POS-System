@extends('layouts.store-owner')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<form action="{{ route('store-owner.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Main Product Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Product Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                    name="sku" value="{{ old('sku', $product->sku) }}" placeholder="e.g., PROD-001">
                                @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control @error('barcode') is-invalid @enderror"
                                    name="barcode" value="{{ old('barcode', $product->barcode) }}">
                                @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Pricing</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                        name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                        name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0">
                                    @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Leave empty if not on sale</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cost Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('cost_price') is-invalid @enderror"
                                        name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0">
                                    @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror"
                                    name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}" step="0.01" min="0" max="100">
                                @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Inventory</h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input type="hidden" name="track_stock" value="0">
                        <input type="checkbox" class="form-check-input" id="trackStock"
                            name="track_stock" value="1" {{ old('track_stock', $product->track_inventory) ? 'checked' : '' }}>
                        <label class="form-check-label" for="trackStock">Track stock quantity</label>
                    </div>

                    <div id="stockFields" class="row" style="{{ old('track_stock', $product->track_inventory) ? '' : 'display: none;' }}">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                    name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                                @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                    name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0">
                                @error('low_stock_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Variants -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Variants (Optional)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Available Sizes</label>
                                <input type="text" class="form-control @error('sizes') is-invalid @enderror"
                                    name="sizes" value="{{ old('sizes', is_array($product->sizes) ? implode(', ', $product->sizes) : $product->sizes) }}"
                                    placeholder="e.g., S, M, L, XL">
                                @error('sizes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Available Colors</label>
                                <input type="text" class="form-control @error('colors') is-invalid @enderror"
                                    name="colors" value="{{ old('colors', is_array($product->colors) ? implode(', ', $product->colors) : $product->colors) }}"
                                    placeholder="e.g., Red, Blue, Black">
                                @error('colors')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror"
                                    name="unit" value="{{ old('unit', $product->unit) }}"
                                    placeholder="e.g., kg, piece, pack">
                                @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                    name="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0">
                                @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" id="isActive"
                            name="is_active" value="1" {{ old('is_active', $product->status === 'available') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" class="form-check-input" id="isFeatured"
                            name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">Featured Product</label>
                    </div>
                </div>
            </div>

            <!-- Category -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Category</h6>
                </div>
                <div class="card-body">
                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                        <option value="">No Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Product Image -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Product Image</h6>
                </div>
                <div class="card-body">
                    @if($product->image)
                    <div class="mb-3 text-center">
                        <img src="{{ asset('storage/' . $product->image) }}"
                            alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    @endif
                    <div class="mb-3">
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                            name="image" accept="image/*" id="imageInput">
                        @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty to keep current image. Max 2MB.</small>
                    </div>
                    <div id="imagePreview" class="text-center" style="display: none;">
                        <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="{{ route('store-owner.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById('trackStock').addEventListener('change', function() {
        document.getElementById('stockFields').style.display = this.checked ? 'flex' : 'none';
    });

    document.getElementById('imageInput').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>
@endsection