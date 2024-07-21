@if(session()->has('success'))
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        </div>
    </div>
@endif
