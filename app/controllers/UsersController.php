<?php

class UsersController extends BaseController {

    protected $layout = "layouts.main";

    public function __construct() {
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('auth', array('on' => array('getSetting')));
    }

    public function getFront() {
        $auth_check = Auth::check();

        if ($auth_check == false) {
            $this->layout->nav_mode = 'public-nav';
        } else {
            $this->layout->nav_mode = 'private-nav';
        }

        $this->layout->content = View::make('users.front');
    }

    public function getLogin() {
        if (Auth::check() == true) {
            return Redirect::to('users/setting')
                ->with('message', 'Your are logged in already!')
                ->with('message-level', 'alert-info');
        }

        $this->layout->nav_mode = 'public-nav';
        $this->layout->content = View::make('users.login');
    }

    public function getLoginVerify() {
        if (!Session::has('login_verify_email')) {
            return Redirect::to('/')
                ->with('message', "Oops, we couldn't find what you're looking for.")
                ->with('message-level', 'alert-info');
        } else {
            $this->layout->nav_mode = 'public-nav';
            $this->layout->content = View::make('users.login-verify');
        }
    }

    public function getLogout() {
        Auth::logout();
        return Redirect::to('users/login')
            ->with('message', 'Your are now logged out!')
            ->with('message-level', 'alert-info');
    }

    public function getRegister() {
        if (Auth::check() == true) {
            return Redirect::to('users/setting')
                ->with('message', 'Your are logged in already! Please log out before you register for another account.')
                ->with('message-level', 'alert-info');
        }

        $this->layout->nav_mode = 'public-nav';
        $this->layout->content = View::make('users.register');
    }

    public function getSetting() {
        $user = Auth::user();
        $email = $user->email;
        $mode = $user->two_factor_mode;
        $secret = $user->two_factor_secret;
        $user_array = array('email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'two_factor_mode' => $user->two_factor_mode
        );

        if ($mode == 'off') {
            // Mode for 2FA is 'off', allow user to turn it on.

            $modular_form = 'enable-authenticator';
        } else {
            // Mode for 2FA is 'on', allow user to turn if off.

            $modular_form = 'disable-authenticator';
        }

        $qr_link = GoogleAuthenticator::getQRcodeURL($email, "Laravel2FA", $secret);

        $this->layout->nav_mode = 'private-nav';
        $this->layout->content = View::make('users.setting')
            ->with('user_array', $user_array)
            ->with('secret', $secret)
            ->with('modular_form', $modular_form)
            ->with('qr_link', $qr_link);
    }

    public function postCreate() {
        $validator = Validator::make(Input::all(), User::$rules);

        if ($validator->passes()) {
            // validation has passed, save user

            // Create Google Two Factor Authentication Secret
            $secret = GoogleAuthenticator::userRandomKey();

            // Saving user data in MySQL
            $user = new User;
            $user->first_name = Input::get('first_name');
            $user->last_name = Input::get('last_name');
            $user->email = Input::get('email');
            $user->password = Hash::make(Input::get('password'));
            $user->two_factor_mode = 'off'; // Two Factor Authentication is turned off by default.
            $user->two_factor_secret = $secret;
            $user->save();

            // Saving User permanent cache in Redis
            $redis = Redis::connection();
            $account_cache_key = $user->email . '_cache';
            $redis->hset($account_cache_key, 'two_factor_mode', 'off');
            $redis->hset($account_cache_key, 'two_factor_secret', $secret);
            $redis->hset($account_cache_key, 'id', $user->id);
            $redis->bgsave();

            return Redirect::to('users/login')
                ->with('message', 'Thanks for registering!')
                ->with('message-level', 'alert-success');
        } else {
            // validation has failed, display error messages

            return Redirect::to('users/register')
                ->with('message', 'Unsuccessful form submit, please check the errors below.')
                ->with('message-level', 'alert-danger')
                ->withErrors($validator)
                ->withInput();
        }
    }

    public function postDisableAuthenticator() {
        // Form Fields Validation
        $user = Auth::user();
        $secret = $user->two_factor_secret;
        $user_otp= Input::get('user_otp');
        $otp = GoogleAuthenticator::generate($secret, 30);

        if ($user_otp == $otp) {
            // Saving data in MySQL
            DB::table('users')->where('id', $user->id)->update(array('two_factor_mode' => 'off'));

            // Saving permanent cache data in Redis
            $redis = Redis::connection();
            $account_cache_key = $user->email . '_cache';
            $redis->hset($account_cache_key, 'two_factor_mode', 'off');

            $message = 'Two factor authentication has been turned off successfully!';
            return Redirect::to('users/setting')->with('message', $message)
                ->with('message-level', 'alert-success');
        } else {
            $message = 'The 6 digits code you entered is invalid/expired (it expires in 30 seconds), please try again.';
            return Redirect::to('users/setting')->with('message', $message)
                ->with('message-level', 'alert-danger');
        }
    }

    public function postEnableAuthenticator() {
        // Form Fields Validation
        $user = Auth::user();
        $secret = $user->two_factor_secret;
        $user_otp = Input::get('user_otp');
        $otp = GoogleAuthenticator::generate($secret, 30);

        if ($user_otp == $otp) {
            // Saving data in MySQL
            DB::table('users')->where('id', $user->id)->update(array('two_factor_mode' => 'on'));

            // Saving permanent cache data in Redis
            $redis = Redis::connection();
            $account_cache_key = $user->email . '_cache';
            $redis->hset($account_cache_key, 'two_factor_mode', 'on');

            $message = 'Two factor authentication has been turned on successfully!';
            return Redirect::to('users/setting')->with('message', $message)
                ->with('message-level', 'alert-success');
        } else {
            $message = 'The 6 digits code you entered is invalid/expired (it expires in 30 seconds), please try again.';
            return Redirect::to('users/setting')->with('message', $message)
                ->with('message-level', 'alert-danger');
        }
    }

    public function postLogin() {
        $email = Input::get('email');
        $password = Input::get('password');

        if (!Auth::validate(array('email' => $email, 'password' => $password))) {
            sleep(2); // Simple approach to slow down brute-force login
            return Redirect::to('users/login')
                ->with('message', 'Your username/password combination was incorrect.')
                ->with('message-level', 'alert-danger')
                ->withInput();
        }

        $account_cache_key = $email . '_cache';
        $redis = Redis::connection();

        if ($redis->exists($account_cache_key)) {
            // Always use Redis for getting 2FA setting information.

            $mode = $redis->hget($account_cache_key, 'two_factor_mode');
        } else {
            /*
             * Only use MySQL for getting 2FA setting information if the Redis cache does not exist, which shouldn't
             * happen under normal circumstances.
             */

            $user = DB::table('users')->where('email', $email)->first();
            $mode = $user->two_factor_mode;
        }

        if ($mode == 'off') {
            // 2FA is disabled, log in the user

            Auth::attempt(array('email' => $email, 'password' => $password));
            $message = 'You are now logged in!';
            return Redirect::intended('users/setting')
                ->with('message', $message)
                ->with('message-level', 'alert-success');
        } else {
            // 2FA is enabled, prompt for 6 digits code

            Session::set('login_verify_email', $email);
            $message = '2FA is enabled, please enter the 6 digits code generated by Google Authenticator to continue.';
            return Redirect::to('users/login-verify')
                ->with('message', $message)
                ->with('message-level', 'alert-info');
        }
    }

    public function postLoginVerify() {
        if (!Session::has('login_verify_email')) {
            return Redirect::to('users/login')
                ->with('message', "Session has expired, please try again.")
                ->with('message-level', 'alert-info');
        }

        $email = Session::get('login_verify_email');
        $account_cache_key = $email . '_cache';
        $redis = Redis::connection();

        if ($redis->exists($account_cache_key)) {
            // Always use Redis for getting 2FA setting information.

            $secret = $redis->hget($account_cache_key, 'two_factor_secret');
            $user_id = $redis->hget($account_cache_key, 'id');
        } else {
            /*
             * Only use MySQL for getting 2FA setting information if the Redis cache does not exist, which shouldn't
             * happen under normal circumstances.
             */

            $user = DB::table('users')->where('email', $email)->first();
            $secret = $user->two_factor_secret;
            $user_id = $user->id;
        }

        $user_otp = Input::get('user_otp');
        $otp = GoogleAuthenticator::generate($secret, 30);

        if ($user_otp == $otp) {
            Auth::loginUsingId($user_id);
            Session::forget('login_verify_email');
            $message = 'You are now logged in!';
            return Redirect::intended('users/setting')->with('message', $message)
                ->with('message-level', 'alert-success');
        } else {
            Session::set('login_verify_email', $email);
            $message = 'The 6 digits code you entered is invalid/expired (it expires in 30 seconds), please try again.';
            return Redirect::to('users/login-verify')->with('message', $message)
                ->with('message-level', 'alert-danger');
        }
    }

}