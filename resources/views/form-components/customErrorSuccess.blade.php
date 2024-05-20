@if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
@endif

@if(session()->has('customError'))
    <div class="alert alert-danger">
        {{ session()->get('customError') }}
    </div>
@endif