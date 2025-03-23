<div class="modal fade" id="CreateBadName" tabindex="-1" aria-labelledby="CreateBadNameLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="CreateBadNameLabel">Add Bad Name</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{Route('worker.create.newBadName')}}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="description">Description</label>
                        <input class="form-control" type="text" id="description" name="description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="value_option">Option</label>
                        <select class="form-select" id="value_option" name="value_option">
                            <option selected disabled>Please choose</option>
                            <option value="1">Contains</option>
                            <option value="2">Regular Expression</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="value">Value</label>
                        <input class="form-control" type="text" id="value" name="value">
                        <div id="emailHelp" class="form-text">
                            Example regular expression: /[a@4]dm[i!1]n[i!1](s|2|s|22|ß)tr[a@4]t[o0]/i
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Add</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
