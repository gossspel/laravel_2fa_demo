<!-- Include Alert Message here, better than showing it in the layout to more easily control css -->
@include('row-alert')

<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default auto-hori-margin">
            <div class="panel-heading div-form-title">
                Account Registration
            </div>
            <div class="panel-body">
                {{ Form::open(array('url'=>'users/create', 'class'=>'form-signup')) }}

                <div class="form-group">
                    {{ Form::label('first_name', 'First Name') }}
                    {{ Form::text('first_name', null, array('class'=>'form-control', 'placeholder'=>'First Name')) }}
                </div>
                @if (!($errors->isEmpty()) && !empty($errors->first('first_name')))
                    <div class="popover bottom">
                        <div class="arrow"></div>
                        <h3 class="popover-title">First Name Error</h3>
                        <div class="popover-content">
                            <p>{{ $errors->first('first_name') }}</p>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    {{ Form::label('last_name', 'Last Name') }}
                    {{ Form::text('last_name', null, array('class'=>'form-control', 'placeholder'=>'Last Name')) }}
                </div>
                @if (!($errors->isEmpty()) && !empty($errors->first('last_name')))
                    <div class="popover bottom">
                        <div class="arrow"></div>
                        <h3 class="popover-title">Last Name Error</h3>
                        <div class="popover-content">
                            <p>{{ $errors->first('last_name') }}</p>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    {{ Form::label('email', 'Email') }}
                    {{ Form::text('email', null, array('class'=>'form-control', 'placeholder'=>'Email')) }}
                </div>
                @if (!($errors->isEmpty()) && !empty($errors->first('email')))
                    <div class="popover bottom">
                        <div class="arrow"></div>
                        <h3 class="popover-title">Email Error</h3>
                        <div class="popover-content">
                            <p>{{ $errors->first('email') }}</p>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    {{ Form::label('password', 'Password') }}
                    {{ Form::password('password', array('class'=>'form-control', 'placeholder'=>'Password')) }}
                </div>
                @if (!($errors->isEmpty()) && !empty($errors->first('password')))
                    <div class="popover bottom">
                        <div class="arrow"></div>
                        <h3 class="popover-title">Password Error</h3>
                        <div class="popover-content">
                            <p>{{ $errors->first('password') }}</p>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    {{ Form::label('password_confirmation', 'Password Confirmation') }}
                    {{ Form::password('password_confirmation', array('class'=>'form-control', 'placeholder'=>'Confirm Password')) }}
                </div>
                @if (!($errors->isEmpty()) && !empty($errors->first('password_confirmation')))
                    <div class="popover bottom">
                        <div class="arrow"></div>
                        <h3 class="popover-title">Password Confirmation Error</h3>
                        <div class="popover-content">
                            <p>{{ $errors->first('password_confirmation') }}</p>
                        </div>
                    </div>
                @endif

                {{ Form::submit('Register', array('class'=>'btn btn-success btn-block'))}}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>