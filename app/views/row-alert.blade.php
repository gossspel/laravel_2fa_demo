@if (Session::has('message'))
    <div class="row">
        <div class="alert {{ Session::get('message-level') }} alert-dismissable row-alert auto-hori-margin" id="alert-container">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ Session::get('message') }}
        </div>
    </div>
@endif