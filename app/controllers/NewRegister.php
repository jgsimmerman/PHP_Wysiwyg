<?php

namespace Altum\Controllers;

use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;
use MaxMind\Db\Reader;

class NewRegister extends Controller {

    public function index() {

        /* Check if Registration is enabled first */
        // if(!$this->settings->register_is_enabled) {
        //     redirect();
        // }

        $status = $_GET['status'];
        $username = $_GET['username'];

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

        if(!empty($_POST)) {

            /* Clean some posted variables */
            $_POST['name']		= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $_POST['email']		= filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            /* Default variables */
            $values['name'] = $_POST['name'];
            $values['email'] = $_POST['email'];
            $values['password'] = $_POST['password'];
            $values['status'] = $_POST['status'];

            /* Define some variables */
            $fields = ['name', 'email' ,'password'];

            /* Check for any errors */
            foreach($_POST as $key => $value) {
                if(empty($value) && in_array($key, $fields) == true) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                    break 1;
                }
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
                $active 	                = 1;
                $email_code                 = md5($_POST['email'] . microtime());
                $last_user_agent            = Database::clean_string($_SERVER['HTTP_USER_AGENT']);
                $total_logins               = $active == '1' ? 1 : 0;
                $ip                         = get_ip();
                $timezone                   = $this->settings->default_timezone;

                /* Determine what package is set by default */
                $package_id                    = 'free';
                $package_settings              = json_encode($this->settings->package_free->settings);
                $package_expiration_date       = \Altum\Date::get('', 8);

                /* When only the trial package is available make that the default one */
  
                Database::update(
                    'users',
                    [
                        'email' => $_POST['email'],
                        'password' => $password,
                        'email_activation_code'=> $email_code,
                        'package_expiration_date' => $package_expiration_date,
                        'package_settings' => $package_settings,
                        'package_id' => $package_id,
                        'timezone' => $timezone,
                        'active' => $active,
                        'date' => \Altum\Date::$date,
                        'ip' => $ip,
                        'last_user_agent' => $last_user_agent,
                        'total_logins' => $total_logins,
                        'temp_status' => 0
                    ],
                    [
                        'name' => $_POST['name']
                    ]
                );

                $registered_user_id = Database::simple_get('user_id', 'users', ['name' => $_POST['name']]);

                /* Log the action */
                Logger::users($registered_user_id, 'register.register');


                /* Clean email and encrypt the password */
                $email = $_POST['email'];
                $password = $_POST['password'];

                /* Check for any errors */
                if(empty($_POST['email'])) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                }
    
                /* Try to get the user from the database */
                $result = Database::$database->query("SELECT `user_id`, `email`, `active`, `password`, `token_code`, `total_logins`, `twofa_secret` FROM `users` WHERE `email` = '{$email}'");
                $login_account = $result->num_rows ? $result->fetch_object() : false;
                
                // var_dump($login_account);
                // exit(1);
                
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
    
                    Database::update(
                        'links',
                        [
                            'active' => 1
                        ],
                        [
                            'user_id' => $login_account->user_id
                        ]
                    );

                    /* Call Create Order Woocommerce Rest API */
                    
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    
                    $curl = curl_init();
                    curl_setopt_array($curl, array(

                        CURLOPT_URL => "https://linkinbio.xyz/wp-json/wc/v3/orders",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS =>"{\n  \"payment_method\": \"stripe\",\n  \"payment_method_title\": \"Direct Bank Transfer\",\n  \"set_paid\": true,\n  \"billing\": {\n    \"first_name\": \"{$name}\",\n    \"last_name\": \"linkin\",\n    \"address_1\": \"969 Market\",\n    \"address_2\": \"\",\n    \"city\": \"San Francisco\",\n    \"state\": \"CA\",\n    \"postcode\": \"94103\",\n    \"country\": \"US\",\n    \"email\": \"{$email}\",\n    \"phone\": \"18186666181\"\n  },\n\n  \"line_items\": [\n    {\n      \"product_id\": 22,\n      \"variation_id\": 23,\n      \"quantity\": 1\n    }\n  ],\n  \"shipping_lines\": [\n    {\n      \"method_id\": \"flat_rate\",\n      \"method_title\": \"Flat Rate\",\n      \"total\": \"0\"\n    }\n  ]\n}",
                        CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/json",
                            "Authorization: Basic Y2tfMjg3ZGVlN2M5NjFiMGE4NTBkOTIxMTliYTY1N2JhMDYwN2M1ZjgzZDpjc18wOGE3ZTM5MzAyNzEyYWYxZTQ0MmVhZmVhYzdhNWFjYTk5N2U2YTg0",
                            "Cookie: __cfduid=d88c8bcd2f333e0fae2d89cb91eda91941601109992; wfwaf-authcookie-2a41a978c8cede0496fa625c7a4912d9=1%7Cadministrator%7Cf2bacd970074804c43c4d9de33ef96617be54be340ae88863c00b0f11e7debed"
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    /* Call Create User Woocommerce Rest API */

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        
                        CURLOPT_URL => "https://linkinbio.xyz/wp-json/wc/v3/customers",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS =>"{\n  \"email\": \"{$email}\",\n  \"first_name\": \"{$name}\",\n  \"last_name\": \"linkin\",\n  \"username\": \"{$name}\",\n  \"roles\": [\"Subscriber\"],\n  \"billing\": {\n    \"first_name\": \"{$name}\",\n    \"last_name\": \"linkin\",\n    \"company\": \"kuttech\",\n    \"address_1\": \"969 Market\",\n    \"address_2\": \"\",\n    \"city\": \"San Francisco\",\n    \"state\": \"CA\",\n    \"postcode\": \"94103\",\n    \"country\": \"US\",\n    \"email\": \"{$email}\",\n    \"phone\": \"18186666181\"\n  },\n  \"shipping\": {\n    \"first_name\": \"{$name}\",\n    \"last_name\": \"linkin\",\n    \"company\": \"\",\n    \"address_1\": \"969 Market\",\n    \"address_2\": \"\",\n    \"city\": \"San Francisco\",\n    \"state\": \"CA\",\n    \"postcode\": \"94103\",\n    \"country\": \"US\"\n  }\n}",
                        CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/json",
                            "Authorization: Basic Y2tfMjg3ZGVlN2M5NjFiMGE4NTBkOTIxMTliYTY1N2JhMDYwN2M1ZjgzZDpjc18wOGE3ZTM5MzAyNzEyYWYxZTQ0MmVhZmVhYzdhNWFjYTk5N2U2YTg0",
                            "Cookie: __cfduid=d88c8bcd2f333e0fae2d89cb91eda91941601109992; wfwaf-authcookie-2a41a978c8cede0496fa625c7a4912d9=1%7Cadministrator%7Cc48b6860b179425d76205f712866d23dcb2644e8bfcf4748c9d603367899ee49; gtm4wp_user_registered=1"
                        ),
                    ));

                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    $user_id = $response->id;

                    curl_close($curl);

                    /* Call Getting Affiliate ID api */

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://linkinbio.xyz/wp-json/affwp/v1/affiliates?user_id=" .$user_id,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Basic MGRjMWZhN2E0NGZhMmZjOTBjZGFjZDU0OWY2YjE4MjM6MjhjODZjYmE4YTg3NDM3MjdjOTVlZmEwZTk5MTZiZmU=",
                        "Cookie: __cfduid=d88c8bcd2f333e0fae2d89cb91eda91941601109992; gtm4wp_user_registered=1; wfwaf-authcookie-2a41a978c8cede0496fa625c7a4912d9=1%7Cadministrator%7C4e764d3a8bff4e818ec21333c407c43a36a9cc5317d1c40fa92ec606321c8728"
                    ),
                    ));

                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    $affiliate_id = $response[0]->affiliate_id;
                    
                    curl_close($curl);

                    Database::update(
                        'users',
                        [
                            'affiliate_id' => $affiliate_id,
                            'userwp_id' => $user_id
                        ],
                        [
                            'email' => $email
                        ]
                    );

                    $_SESSION['info'][] = $this->language->login->info_message->logged_in;
                    redirect($redirect);
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

        $view = new \Altum\Views\View('register2/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
