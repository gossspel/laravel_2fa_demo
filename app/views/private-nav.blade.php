<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
                <li>{{ HTML::link('users/setting', 'Setting') }}</li>
                <li>{{ HTML::link('users/logout', 'Log Out') }}</li>
            </ul>
        </li>
    </ul>
</div>