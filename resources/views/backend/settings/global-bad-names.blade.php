@extends('template')

@section('site-title')

@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Global Bad Name List</h1>
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
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Beschreibung</th>
                            <th scope="col">Option</th>
                            <th scope="col">Wert</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($badNames as $badName)
                            <tr>
                                <td class="col-lg-5">{{$badName->description}}</td>
                                <td class="col-lg-2">
                                    @if($badName->value_option == 1)
                                        Enthält
                                    @else
                                        Regular Expression
                                    @endif
                                </td>
                                <td class="col-lg-5">{{$badName->value}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection