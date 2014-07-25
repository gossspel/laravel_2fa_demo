<?php

// TODO: Unit Testing for all methods
// TODO: Make a new StaticPages Controller and Views for the Front action

class UsersController extends BaseController {
    // Instance keys
    protected $key_message = 'message';
    protected $key_message_level = 'message_level';
    protected $key_mode = 'mode';
    protected $key_pass = 'pass';
    protected $key_redirect = 'redirect';
    protected $key_URI = 'uri';
    protected $key_setting_array = 'setting_array';
    protected $key_validation_error = 'validation_error';
    
    // Instance values
    protected $layout = "layouts.main";
    protected $auth_filter_exception = array('getFront', 'getLogin', 'getLoginVerify', 'getLogout', 'getRegister',
    'postCreate', 'postLogin', 'postLoginVerify');

    // Utility methods - Notice that Redirect must be done in controller to preserve layout variables
    protected function redirectTo($param) {
        return Redirect::to($param[$this->key_URI])
            ->with($this->key_message, $param[$this->key_message])
            ->with($this->key_message_level, $param[$this->key_message_level]);
    }

    protected function redirectMode($param) {
        return Redirect::$param[$this->key_mode]($param[$this->key_URI])
            ->with($this->key_message, $param[$this->key_message])
            ->with($this->key_message_level, $param[$this->key_message_level]);
    }

    // Constructor methods
    public function __construct() {
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('auth', array('except' => $this->auth_filter_exception));
    }


    // Controller methods
    public function getFront() {
        $this->layout->nav_mode = User::getAuthCheckNav();
        $this->layout->content = View::make('users.front');
    }

    public function getLogin() {
        $param = User::getLogin();
        if ($param[$this->key_redirect]) {
            return $this->redirectTo($param);
        }
        $this->layout->nav_mode = User::getPublicNav();
        $this->layout->content = View::make('users.login');
    }

    public function getLoginVerify() {
        $param = User::getLoginVerify();
        if ($param[$this->key_redirect]) {
            return $this->redirectTo($param);
        }
        $this->layout->nav_mode = User::getAuthCheckNav();
        $this->layout->content = View::make('users.login-verify');
    }

    public function getLogout() {
        $param = User::getLogout();
        return $this->redirectTo($param);
    }

    public function getRegister() {
        $param = User::getRegister();
        if ($param[$this->key_redirect]) {
            return $this->redirectTo($param);
        }
        $this->layout->nav_mode = User::getPublicNav();
        $this->layout->content = View::make('users.register');
    }

    public function getSetting() {
        $user = Auth::user();
        $settingArray = $user->getSetting();
        $this->layout->nav_mode = User::getPrivateNav();
        $this->layout->content = View::make('users.setting')->with($this->key_setting_array, $settingArray);
    }

    public function postCreate() {
        $param = User::postCreate();
        $redirect = $this->redirectTo($param);
        if (!$param[$this->key_pass]) {
            return $redirect->withErrors($param[$this->key_validation_error])->withInput();
        } else {
            return $redirect;
        }
    }

    public function postToggleAuthenticator() {
        $param = Auth::user()->postToggleAuthenticator();
        return $this->redirectTo($param);

    }

    public function postLogin() {
        $param = User::postLogin();
        return $this->redirectMode($param);
    }

    public function postLoginVerify() {
        $param = User::postLoginVerify();
        return $this->redirectMode($param);
    }

}