@extends('template')

@section('site-title')

@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Bad Name List</h1>
            </div>
        </div>
            <div class="row">
                <div class="col-lg-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateBadName">Hinzufügen</button>
                </div>
                <div class="col-lg-auto">
                    <a href="{{Route('backend.view.globalBadNames',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}" class="btn btn-primary">Global Bad Name List</a>
                </div>
            </div>
        <hr>
        @include('form-components.alertCustomError')
        <div class="row">
            <div class="col-lg-12">
                @if($badNames->count() == 0)
                    <div class="alert alert-primary" role="alert">
                        Du hast noch keine Bad Names hinzugefügt
                    </div>
                @else
                    <form method="post" action="{{Route('backend.delete.badName',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">
                        @csrf
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Beschreibung</th>
                                <th scope="col">Option</th>
                                <th scope="col"></th>
                                <th scope="col">Wert</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($badNames as $badName)
                                <tr>
                                    <td class="col-lg-3">{{$badName->description}}</td>
                                    <td class="col-lg-2">
                                        @if($badName->value_option == 1)
                                            Enthält
                                        @else
                                            Regular Expression
                                        @endif
                                    </td>
                                    <td class="col-lg-1">
                                        @if($badName->failed == true)
                                            <span class="badge text-bg-danger">Wert ungültig</span>
                                        @endif
                                    </td>
                                    <td class="col-lg-5">{{$badName->value}}</td>
                                    <td class="col-lg-1"><button class="btn btn-link text-danger p-0" name="DeleteBadNameID" value="{{$badName->id}}">Löschen</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @include('inc.bad-name.create-bad-name')
@endsection