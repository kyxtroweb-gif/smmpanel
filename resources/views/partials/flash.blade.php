@if(session('success'))
<div class="container mt-3"><div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
@endif
@if(session('error'))
<div class="container mt-3"><div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
@endif
@if(session('warning'))
<div class="container mt-3"><div class="alert alert-warning alert-dismissible fade show"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
@endif
@if(session('info'))
<div class="container mt-3"><div class="alert alert-info alert-dismissible fade show"><i class="fa-solid fa-circle-info me-1"></i> {{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
@endif
@if($errors->any())
<div class="container mt-3">
<div class="alert alert-danger alert-dismissible fade show">
  <strong><i class="fa-solid fa-xmark-circle me-1"></i> Please fix the following errors:</strong>
  <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
</div>
@endif
