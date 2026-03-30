@extends('layouts.admin')
@section('title', 'Categories - Admin')
@section('page-title', 'Service Categories')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Organize your services into categories. Categories are shown on the public storefront.</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="fa-solid fa-plus me-1"></i> Add Category
    </button>
</div>

@include('partials.flash')

<div class="row g-4">
    @forelse($categories as $category)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <i class="{{ $category->icon ?? 'fa fa-globe' }} fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ $category->name }}</h6>
                                <small class="text-muted">{{ $category->services_count ?? $category->services->count() }} services</small>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.categories') }}" class="d-flex gap-2 flex-fill">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $category->name }}">
                            <input type="hidden" name="id" value="{{ $category->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-save"></i></button>
                        </form>
                        @if($category->id > 0)
                            <form method="POST" action="{{ route('admin.categories.delete', $category->id) }}"
                                  onsubmit="return confirm('Delete this category? Services will be uncategorized.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No categories yet. Create your first category to organize services.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.categories') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-1"></i> Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Instagram Followers">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Icon (FontAwesome class)</label>
                        <input type="text" name="icon" class="form-control" value="fa fa-globe" placeholder="e.g. fab fa-instagram">
                        <small class="text-muted">Use FontAwesome 6 class names</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
