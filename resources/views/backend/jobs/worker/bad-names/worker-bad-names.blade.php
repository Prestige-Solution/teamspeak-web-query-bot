@extends('template')

@section('site-title')
    Bad Name List | {{ config('app.project') }}
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Bad Name List | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <button type="button"
                                class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                                data-bs-toggle="modal"
                                data-bs-target="#CreateBadName">
                            <i class="fa-solid fa-circle-plus"></i> Add new bad name
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <button type="button"
                                class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                                data-bs-toggle="modal"
                                data-bs-target="#GlobalBadNameList">
                            <i class="fa-solid fa-eye"></i> Show global bad name list
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        <div class="row">
            <div class="col-lg-12">
                @if($badNames->count() == 0)
                    <div class="alert alert-primary" role="alert">
                        You have not added any bad names yet
                    </div>
                @else
                    <form method="post" action="{{Route('worker.delete.badName')}}">
                        @csrf
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Description</th>
                                <th scope="col">Option</th>
                                <th scope="col"></th>
                                <th scope="col">Value</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($badNames as $badName)
                                <tr>
                                    <td class="col-lg-3">{{$badName->description}}</td>
                                    <td class="col-lg-2">
                                        @if($badName->value_option == 1)
                                            Contains
                                        @else
                                            Regular Expression
                                        @endif
                                    </td>
                                    <td class="col-lg-1">
                                        @if($badName->failed == true)
                                            <span class="badge text-bg-danger">Value invalid</span>
                                        @endif
                                    </td>
                                    <td class="col-lg-5">{{$badName->value}}</td>
                                    <td class="col-lg-1"><button class="btn btn-link text-danger p-0" name="id" value="{{$badName->id}}">Delete</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @include('backend.jobs.worker.bad-names.inc.create-bad-name')
    @include('backend.jobs.worker.bad-names.inc.view-global-bad-names')
@endsection
