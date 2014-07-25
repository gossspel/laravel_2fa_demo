<?php

// TODO: Too many static variables and methods, think of migrating lots of them to a helper class
// TODO: Unit testing on all methods

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

    // Validation Rules
    public static $rules = array(
        'first_name'=>'required|alpha_num|min:2',
        'last_name'=>'required|alpha|min:2',
        'email'=>'required|email|unique:users',
        'password'=>'required|alpha_num|between:6,12|confirmed',
        'password_confirmation'=>'required|alpha_num|between:6,12'
    );

    // Static variables - flash messages used only once
    protected static $getRegisterInfo = 'Please log out before you register for another account.';
    protected static $getLoginInfo = 'You are logged in already!';
    protected static $getLoginVerifyInfo = "Oops, we couldn't find what you're looking for.";
    protected static $getLogoutDanger = 'You can only log out if you were logged in!';
    protected static $getLogoutSuccess = 'You are now logged out!';
    protected static $postCreateSuccess = 'Thanks for registering!';
    protected static $postCreateDanger = 'Unsuccessful form submit, please check the errors below.';
    protected static $postLoginDanger = 'Your username/password combination was incorrect.';
    protected static $postLoginInfo = 'Please enter the six digits code from Google Authenticator.';
    protected static $postLoginVerifyInfo = 'Session has expired, please try again.';
    protected static $postToggleAuthenticatorSuccess = "Two factor authentication has been turned %s successfully!";

    // Static variables - flash messages used multiple times
    protected static $loggedInRedirect = 'You are now logged in!';
    protected static $sixDigitCodeError = 'The six digits code you entered is invalid/expired, please try again.';

    // Static variable - paths
    protected static $loginURI = 'users/login';
    protected static $loginVerifyURI = 'users/login-verify';
    protected static $mainURI = '/';
    protected static $registerURI = 'users/register';
    protected static $settingURI = 'users/setting';

    // Static variable - keys
    protected static $keyCreatedAt = 'created_at';
    protected static $keyEmail = 'email';
    protected static $keyFirstName = 'first_name';
    protected static $keyId = 'id';
    protected static $keyLastName = 'last_name';
    protected static $keyLoginVerifyEmail = 'login_verify_email';
    protected static $keyMessage = 'message';
    protected static $keyMessageLevel = 'message_level';
    protected static $keyMode = 'mode';
    protected static $keyPass = 'pass';
    protected static $keyPassword = 'password';
    protected static $keyQrLink = 'qr_link';
    protected static $keyRedirect = 'redirect';
    protected static $keyTwoFactorMode = 'two_factor_mode';
    protected static $keyTwoFactorSecret = 'two_factor_secret';
    protected static $keyUpdatedAt = 'updated_at';
    protected static $keyURI = 'uri';
    protected static $keyUserOtp = 'user_otp';
    protected static $keyValidationError = 'validation_error';

    // Static variable - values
    protected static $alertDanger = 'alert-danger';
    protected static $alertInfo = 'alert-info';
    protected static $alertSuccess = 'alert-success';
    protected static $databaseTable = 'users';
    protected static $false = false;
    protected static $true = true;
    protected static $privateNav = 'private-nav';
    protected static $publicNav = 'public-nav';
    protected static $redirectModeTo = 'to';
    protected static $redirectModeIntended = 'intended';
    protected static $sleepSeconds = 2;
    protected static $twoFactorOff = 'off';
    protected static $twoFactorOn = 'on';
    protected static $twoFactorWindow = 30; // Google Authenticator locked the key valid time period to be 30 seconds.

    // Instance variables - keys
    protected $key_modular_form = 'modular_form';
    protected $key_qr_link = 'qr_link';

    // Instance variables - values
    protected $brand = 'Laravel2FA';
    protected $enable_authenticator = 'enable-authenticator';
    protected $disable_authenticator = 'disable-authenticator';

    // Instance variables - Laravel default
    protected $table = 'users';
    protected $hidden = array('password', 'remember_token');

    // Static methods - utility
    protected static function getAccountCacheKey($email) {
        return "{$email}_cache";
    }

    public static function getAuthCheck() {
        if (Auth::check() == true) {
            return true;
        } else {
            return false;
        }
    }
    public static function getAuthCheckNav() {
        if (User::getAuthCheck() == false) {
            $navMode = User::getPublicNav();
        } else {
            $navMode = User::getPrivateNav();
        }

        return $navMode;
    }

    public static function getPrivateNav() {
        return User::$privateNav;
    }

    public static function getPublicNav() {
        return User::$publicNav;
    }

    protected static function getUserByEmail($email) {
        return DB::table(User::$databaseTable)->where(User::$keyEmail, $email)->first();
    }

    public static function setRedirectParam($URI, $message, $messageLevel) {
        $param = array(self::$keyURI => $URI,
            self::$keyMessage => $message,
            self::$keyMessageLevel => $messageLevel
        );
        return $param;
    }

    public static function validateOneTimePassword($twoFactorSecret) {
        $inputOTP = Input::get(User::$keyUserOtp);
        $databaseOTP = GoogleAuthenticator::generate($twoFactorSecret, User::$twoFactorWindow);

        if ($inputOTP == $databaseOTP) {
            return User::$true;
        } else {
            return User::$false;
        }
    }

    // Static controller related methods
    public static function getLogin() {
        if (User::getAuthCheck() == true) {
            $param = self::setRedirectParam(self::$settingURI, self::$getLoginInfo, self::$alertInfo);
            $param[self::$keyRedirect] = self::$true;
        } else {
            $param = array(self::$keyRedirect => self::$false);
        }
        return $param;
    }

    public static function getLoginVerify() {
        if (!Session::has(User::$keyLoginVerifyEmail)) {
            $param = self::setRedirectParam(self::$mainURI, self::$getLoginVerifyInfo, self::$alertInfo);
            $param[self::$keyRedirect] = self::$true;
        } else {
            $param = array(self::$keyRedirect => self::$false);
        }
        return $param;
    }

    public static function getLogout() {
        if (User::getAuthCheck() == true) {
            $message = User::$getLogoutSuccess;
            $messageLevel = User::$alertSuccess;
        } else {
            $message = User::$getLogoutDanger;
            $messageLevel = User::$alertDanger;
        }
        Auth::logout();
        $param = self::setRedirectParam(self::$mainURI, $message, $messageLevel);
        return $param;
    }

    public static function getRegister() {
        if (User::getAuthCheck() == true) {
            $param = self::setRedirectParam(self::$settingURI, self::$getRegisterInfo, self::$alertInfo);
            $param[self::$keyRedirect] = self::$true;
        } else {
            $param = array(self::$keyRedirect => self::$false);
        }
        return $param;
    }

    public static function postCreate() {
        $validator = Validator::make(Input::all(), User::$rules);

        if ($validator->passes()) {
            // Create Google Two Factor Authentication Secret
            $twoFactorSecret = GoogleAuthenticator::userRandomKey();

            // Saving user data in MySQL
            $user = new User;
            $user->first_name = Input::get(User::$keyFirstName);
            $user->last_name = Input::get(User::$keyLastName);
            $user->email = Input::get(User::$keyEmail);
            $user->password = Hash::make(Input::get(User::$keyPassword));
            $user->two_factor_mode = User::$twoFactorOff; // Two Factor Authentication is turned off by default.
            $user->two_factor_secret = $twoFactorSecret;
            $user->save();

            // Saving User permanent cache in Redis
            $redis = Redis::connection();
            $accountCacheKey = User::getAccountCacheKey($user->email);
            $redis->hset($accountCacheKey, User::$keyTwoFactorMode, User::$twoFactorOff);
            $redis->hset($accountCacheKey, User::$keyTwoFactorSecret, $twoFactorSecret);
            $redis->hset($accountCacheKey, User::$keyId, $user->id);
            $redis->bgsave();

            // Making Redirect Param for successful registration
            $param = self::setRedirectParam(self::$loginURI, self::$postCreateSuccess, self::$alertSuccess);
            $param[self::$keyPass] = self::$true;
        } else {
            // Making Redirect Param for failed registration
            $param = self::setRedirectParam(self::$registerURI, self::$postCreateDanger, self::$alertDanger);
            $param[self::$keyPass] = self::$false;
            $param[self::$keyValidationError] = $validator;
        }
        return $param;
    }

    public static function postLogin() {
        $email = Input::get(User::$keyEmail);
        $password = Input::get(User::$keyPassword);

        if (!Auth::validate(array(User::$keyEmail => $email, User::$keyPassword => $password))) {
            sleep(User::$sleepSeconds); // Simple approach to slow down brute-force login
            $param = self::setRedirectParam(self::$loginURI, self::$postLoginDanger, self::$alertDanger);
            $param[self::$keyMode] = self::$redirectModeTo;
            return $param;
        }

        $accountCacheKey = User::getAccountCacheKey($email);
        $redis = Redis::connection();

        if ($redis->exists($accountCacheKey)) {
            // Always use Redis for getting 2FA setting information.
            $mode = $redis->hget($accountCacheKey, User::$keyTwoFactorMode);
        } else {
            // MySQL fallback
            $user = User::getUserByEmail($email);
            $mode = $user->two_factor_mode;
        }

        if ($mode == User::$twoFactorOff) {
            // 2FA is disabled, log in the user
            Auth::attempt(array(User::$keyEmail => $email, User::$keyPassword => $password));
            $param = self::setRedirectParam(self::$settingURI, self::$loggedInRedirect, self::$alertSuccess);
            $param[self::$keyMode] = self::$redirectModeIntended;
        } else {
            // 2FA is enabled, prompt for 6 digits code
            Session::set(User::$keyLoginVerifyEmail, $email);
            $param = self::setRedirectParam(self::$loginVerifyURI, self::$postLoginInfo, self::$alertInfo);
            $param[self::$keyMode] = self::$redirectModeTo;
        }
        return $param;
    }

    public static function postLoginVerify() {
        if (!Session::has(User::$keyLoginVerifyEmail)) {
            $param = self::setRedirectParam(self::$loginURI, self::$postLoginVerifyInfo, self::$alertInfo);
            return $param;
        }

        $email = Session::get(User::$keyLoginVerifyEmail);
        $accountCacheKey = User::getAccountCacheKey($email);
        $redis = Redis::connection();

        if ($redis->exists($accountCacheKey)) {
            // Always use Redis for getting 2FA setting information.
            $twoFactorSecret = $redis->hget($accountCacheKey, User::$keyTwoFactorSecret);
            $userId = $redis->hget($accountCacheKey, User::$keyId);
        } else {
            // MySQL fallback
            $user = User::getUserByEmail($email);
            $twoFactorSecret = $user->two_factor_secret;
            $userId = $user->id;
        }

        $OTPResult = User::validateOneTimePassword($twoFactorSecret);

        if ($OTPResult == User::$true) {
            Auth::loginUsingId($userId);
            Session::forget(User::$keyLoginVerifyEmail);
            $param = self::setRedirectParam(self::$settingURI, self::$loggedInRedirect, self::$alertSuccess);
            $param[self::$keyMode] = self::$redirectModeIntended;
        } else {
            Session::set(User::$keyLoginVerifyEmail, $email);
            $param = self::setRedirectParam(self::$loginVerifyURI, self::$sixDigitCodeError, self::$alertDanger);
            $param[self::$keyMode] = self::$redirectModeTo;
        }

        return $param;
    }

    // Instance method - controller related
    public function getSetting() {
        $settingArray = array(User::$keyEmail => $this->email,
            User::$keyTwoFactorMode => $this->two_factor_mode,
            User::$keyTwoFactorSecret => $this->two_factor_secret,
            User::$keyFirstName => $this->first_name,
            User::$keyLastName => $this->last_name,
            User::$keyCreatedAt => $this->created_at,
            User::$keyUpdatedAt => $this->updated_at
        );

        if ($this->two_factor_mode == User::$twoFactorOff) {
            // Mode for 2FA is 'off', allow user to turn it on, generate QR link
            $modularForm = $this->enable_authenticator;
            $qrLink = GoogleAuthenticator::getQRcodeURL($this->email, $this->brand, $this->two_factor_secret);
            $settingArray[User::$keyQrLink] = $qrLink;
        } else {
            // Mode for 2FA is 'on', allow user to turn if off.
            $modularForm = $this->disable_authenticator;
        }

        $settingArray[$this->key_modular_form] = $modularForm;
        return $settingArray;
    }

    public function postToggleAuthenticator() {
        // Form Fields Validation
        $twoFactorMode = $this->two_factor_mode;
        $twoFactorSecret = $this->two_factor_secret;

        // Toggling process
        if ($twoFactorMode == User::$twoFactorOff) {
            $twoFactorMode = User::$twoFactorOn;
        } else {
            $twoFactorMode = User::$twoFactorOff;
        }

        $OTPResult = User::validateOneTimePassword($twoFactorSecret);

        if ($OTPResult == User::$true) {
            // Saving data in MySQL
            DB::table(User::$databaseTable)->where(User::$keyId, $this->id)
                ->update(array(User::$keyTwoFactorMode => $twoFactorMode));

            // Saving permanent cache data in Redis
            $redis = Redis::connection();
            $accountCacheKey = User::getAccountCacheKey($this->email);
            $redis->hset($accountCacheKey, User::$keyTwoFactorMode, $twoFactorMode);

            $message = sprintf(User::$postToggleAuthenticatorSuccess, $twoFactorMode);
            $messageLevel = User::$alertSuccess;
        } else {
            $message = User::$sixDigitCodeError;
            $messageLevel = User::$alertDanger;
        }

        $param = self::setRedirectParam(self::$settingURI, $message, $messageLevel);
        return $param;
    }

}
