<!-- Include Alert Message here, better than showing it in the layout to more easily control css -->
@include('row-alert')

<div class="row">
    <div class="main-title auto-hori-margin">
        Account Setting
    </div>

    <ul class="list-group auto-hori-margin">
        <li class="list-group-item">
            <span class="list-span">
                <b>First Name:</b>
            </span>
            <span class="list-span">
                {{ $user_array['first_name'] }}
            </span>
        </li>
        <li class="list-group-item">
            <span class="list-span">
                <b>Last Name:</b>
            </span>
            <span class="list-span">
                {{ $user_array['last_name'] }}
            </span>
        </li>
        <li class="list-group-item">
            <span class="list-span">
                <b>Email:</b>
            </span>
            <span class="list-span">
                {{ $user_array['email'] }}
            </span>
        </li>
        <li class="list-group-item">
            <span class="list-span">
                <b>2FA:</b>
            </span>
            <span class="list-span">
                {{ $user_array['two_factor_mode'] }}
            </span>
        </li>
        <li class="list-group-item">
            <span class="list-span">
                <b>Created At:</b>
            </span>
            <span class="list-span">
                {{ $user_array['created_at'] }}
            </span>
        </li>
        <li class="list-group-item">
            <span class="list-span">
                <b>Updated At:</b>
            </span>
            <span class="list-span">
                {{ $user_array['updated_at'] }}
            </span>
        </li>
    </ul>

    <!-- Insert modular form here: disable-authenticator.blade.php or enable-authenticator.blade.php -->
    @include($modular_form)
</div>