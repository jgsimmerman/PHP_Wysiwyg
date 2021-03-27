<?php

namespace Altum\Controllers;

use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;

class Register extends Controller {

    public function index() {

        /* Check if Registration is enabled first */
        if(!$this->settings->register_is_enabled) {
            redirect();
        }

        Authentication::guard('guest');
        $method	= (isset($this->params[0])) ? $this->params[0] : false;
        $redirect = 'dashboard';
        if(isset($_GET['redirect']) && $redirect = $_GET['redirect']) {
            $redirect = Database::clean_string($redirect);
        }

        /* Default variables */
        $values = [
            'name' => '',
            'email' => '',
            'password' => ''
        ];

        /* Initiate captcha */
        $captcha = new Captcha([
            'recaptcha' => $this->settings->captcha->recaptcha_is_enabled,
            'recaptcha_public_key' => $this->settings->captcha->recaptcha_public_key,
            'recaptcha_private_key' => $this->settings->captcha->recaptcha_private_key
        ]);

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
                            $package_id                 = 'free';
                            $package_expiration_date    = \Altum\Date::get('', 5);
                            $package_settings           = json_encode($this->settings->package_free->settings);
                            $ip                         = get_ip();

                            /* Add the user to the database */
                            $stmt = Database::$database->prepare("INSERT INTO `users` (`password`, `email`, `name`, `facebook_id`, `package_id`, `package_expiration_date`, `package_settings`, `active`, `date`, `ip`, `last_user_agent`, `total_logins`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('ssssssssssss', $password, $email, $name, $facebook_user_id, $package_id, $package_expiration_date, $package_settings, $active, \Altum\Date::$date, $ip, $last_user_agent, $total_logins);
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
        }

        if(!empty($_POST)) {
            /* Clean some posted variables */
            $_POST['name']		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $_POST['email']		= filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            /* Default variables */
            $values['name'] = $_POST['name'];
            $values['email'] = $_POST['email'];
            $values['password'] = $_POST['password'];

            /* Define some variables */
            $fields = ['name', 'email' ,'password'];

            /* Check for any errors */
            foreach($_POST as $key => $value) {
                if(empty($value) && in_array($key, $fields) == true) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                    break 1;
                }
            }
            if(!$captcha->is_valid()) {
                $_SESSION['error'][] = $this->language->global->error_message->invalid_captcha;
            }
            if(strlen($_POST['name']) < 3 || strlen($_POST['name']) > 32) {
                $_SESSION['error'][] = $this->language->register->error_message->name_length;
            }
            if(Database::exists('user_id', 'users', ['email' => $_POST['email']])) {
                $_SESSION['error'][] = $this->language->register->error_message->email_exists;
            }
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'][] = $this->language->register->error_message->invalid_email;
            }
            if(strlen(trim($_POST['password'])) < 6) {
                $_SESSION['error'][] = $this->language->register->error_message->short_password;
            }

            /* If there are no errors continue the registering process */
            if(empty($_SESSION['error'])) {
                /* Define some needed variables */
                $password                   = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $active 	                = (int) !$this->settings->email_confirmation;
                $email_code                 = md5($_POST['email'] . microtime());
                $last_user_agent            = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
                $total_logins               = $active == '1' ? 1 : 0;
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
                $stmt = Database::$database->prepare("INSERT INTO `users` (`password`, `email`, `email_activation_code`, `name`, `package_id`, `package_expiration_date`, `package_settings`, `language`, `timezone`, `active`, `date`, `ip`, `last_user_agent`, `total_logins`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssssssssss', $password, $_POST['email'], $email_code, $_POST['name'], $package_id, $package_expiration_date, $package_settings, Language::$language, $timezone, $active, \Altum\Date::$date, $ip, $last_user_agent, $total_logins);
                $stmt->execute();
                $registered_user_id = $stmt->insert_id;
                $stmt->close();

                /* Log the action */
                Logger::users($registered_user_id, 'register.register');

                /* Send notification to admin if needed */
                if($this->settings->email_notifications->new_user && !empty($this->settings->email_notifications->emails)) {

                    send_mail(
                        $this->settings,
                        explode(',', $this->settings->email_notifications->emails),
                        $this->language->global->emails->admin_new_user_notification->subject,
                        sprintf($this->language->global->emails->admin_new_user_notification->body, $_POST['name'], $_POST['email'])
                    );


                }

                /* If active = 1 then login the user, else send the user an activation email */
                if($active == '1') {
                    $_SESSION['user_id'] = $registered_user_id;
                    $_SESSION['success'] = $this->language->register->success_message->login;

                    Logger::users($registered_user_id, 'login.success');

                    redirect($redirect);
                } else {

                    /* Prepare the email */
                    $email_template = get_email_template(
                        [
                            '{{NAME}}' => $_POST['name'],
                        ],
                        $this->language->global->emails->user_activation->subject,
                        [
                            '{{ACTIVATION_LINK}}' => url('activate-user/' . md5($_POST['email']) . '/' . $email_code . '?redirect=' . $redirect),
                            '{{NAME}}' => $_POST['name'],
                        ],
                        $this->language->global->emails->user_activation->body
                    );

                    send_mail($this->settings, $_POST['email'], $email_template->subject, $email_template->body);

                    $_SESSION['success'][] = $this->language->register->success_message->registration;
                }

            }
        }

        /* Main View */
        $data = [
            'values' => $values,
            'captcha' => $captcha,
            'google_login_url' => $google_login_url ?? null,
            'facebook_login_url' => $facebook_login_url ?? null,
        ];

        $view = new \Altum\Views\View('register/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
