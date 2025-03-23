<div class="modal fade" id="GlobalBadNameList" tabindex="-1" aria-labelledby="GlobalBadNameListLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="GlobalBadNameListLabel">Global bad name</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Description</th>
                                <th scope="col">Option</th>
                                <th scope="col">Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($globalBadNames as $globalBadName)
                                <tr>
                                    <td class="col-lg-5">{{$globalBadName->description}}</td>
                                    <td class="col-lg-2">
                                        @if($globalBadName->value_option == 1)
                                            Contains
                                        @else
                                            Regular Expression
                                        @endif
                                    </td>
                                    <td class="col-lg-5">{{$globalBadName->value}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
