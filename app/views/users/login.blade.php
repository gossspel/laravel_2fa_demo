<!-- Include Alert Message here, better than showing it in the layout to more easily control css -->
@include('row-alert')

<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default div-form-top">
            <div class="panel-heading div-form-title">
                Account Login
            </div>
            <div class="panel-body">
                {{ Form::open(array('url'=>'users/login', 'class'=>'form-signin', 'role'=>'form')) }}
                <div class="form-group">
                    {{ Form::label('email', 'E-Mail Address') }}
                    {{ Form::text('email', null, array('class'=>'form-control', 'placeholder'=>'Email Address')) }}
                </div>
                <div class="form-group">
                    {{ Form::label('password', 'Password') }}
                    {{ Form::password('password', array('class'=>'form-control', 'placeholder'=>'Password')) }}
                </div>
                {{ Form::submit('Login', array('class'=>'btn btn-success btn-block'))}}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>