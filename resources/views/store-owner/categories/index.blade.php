@extends('layouts.store-owner')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <!-- Search/Filter placeholder -->
    </div>
    <div class="d-flex justify-content-end w-100 w-md-auto">
        <a href="{{ route('store-owner.categories.create') }}" class="btn btn-primary text-nowrap">
            <i class="bi bi-plus-lg me-1"></i>Add Category
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="80">Image</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" 
                                     alt="{{ $category->name }}" class="rounded" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-folder text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $category->name }}</span>
                            @if($category->description)
                                <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $category->products_count ?? 0 }}</span>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('store-owner.categories.edit', $category) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('store-owner.categories.destroy', $category) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-folder fs-1 d-block mb-2"></i>
                                No categories yet
                            </div>
                            <a href="{{ route('store-owner.categories.create') }}" class="btn btn-primary btn-sm mt-2">
                                Create your first category
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($categories->hasPages())
    <div class="card-footer">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
