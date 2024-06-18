@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <h4><i class="icon fa fa-ban"></i> Error!</h4>
    {{ session('error') }}
</div>
@endif

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    {{ session('success') }}
</div>
@endif
