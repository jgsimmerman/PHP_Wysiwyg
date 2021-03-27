<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;
    use MaxMind\Db\Reader;
    use Altum\Middlewares\Csrf;
    use Altum\Response;
    use Altum\Routing\Router;

    class TempRegister extends Controller {

        public function index() {
            
            /* Create a new temp user part */

            if($_POST) {
                
                $domain = $_POST['domain'];
                $filtered_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                $name = str_replace(' ', '', $filtered_name);

            }

            Database::insert_wp('test_reg', [
                'domain' => $domain,
                'name' => $name
            ]);
            
            $pure_domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $domain );
            $domain_id = Database::simple_get('domain_id', 'domains', ['host' => $pure_domain]);
            
                /* Get the form data from webhook url

                    $content = file_get_contents('php://input');
                    parse_str($content, $form1);
                    $form = json_encode($form1);
                    $data = json_decode($form, true);
                    $domain = $data['fields']['name']['value'];
                    $name = $data['fields']['email']['value'];
                
                */

                /*  local test part
                
                    $domain_id = 0;
                    $name = "ikramtest";

                    Database::insert_wp('test_reg', [
                        'domain' => $domain_id,
                        'name' => $name
                    ]);
                */

            $email = 'temp' .$name .'@gmail.com';
            $password_origin = 'temppass';
            $package_id = 1;
            $active = '1';
            $redirect = 'dashboard';

            $password = password_hash($password_origin, PASSWORD_DEFAULT);
            $email_code = md5($email . microtime());
            $total_logins = $active == '1' ? 1 : 0;
            $language = Language::$language;
            $date = \Altum\Date::$date;
            $timezone = 'UTC';
            $date = \Altum\Date::$date;
            $package_expiration_date = \Altum\Date::get('', 7, '');
            $temp_status = 1;

            $package_settings = Database::simple_get('settings', 'packages', ['package_id' => $package_id]);
            $package_settings = json_decode($package_settings);
            $settings = json_encode([

                'additional_global_domains' => $package_settings->additional_global_domains ? $package_settings->additional_global_domains: null,
                'custom_url' => $package_settings->custom_url ?  $package_settings->custom_url: null,
                'deep_links' => $package_settings->deep_links ? $package_settings->deep_links: null,
                'no_ads' => $package_settings->no_ads ? $package_settings->no_ads: null,
                'removable_branding' => $package_settings->removable_branding ? $package_settings->removable_branding: null,
                'custom_branding' => $package_settings->custom_branding ? $package_settings->custom_branding: null,
                'custom_colored_links' => $package_settings->custom_colored_links ? $package_settings->custom_colored_links: null,
                'statistics' => $package_settings->statistics ? $package_settings->statistics: null,
                'google_analytics' => $package_settings->google_analytics ? $package_settings->google_analytics: null,
                'facebook_pixel' => $package_settings->facebook_pixel ? $package_settings->facebook_pixel: null,
                'custom_backgrounds' => $package_settings->custom_backgrounds ? $package_settings->custom_backgrounds: null,
                'verified' => $package_settings->verified ? $package_settings->verified: null,
                'scheduling' => $package_settings->scheduling ? $package_settings->scheduling: null,
                'seo' => $package_settings->seo ? $package_settings->seo: null,
                'utm' => $package_settings->utm ? $package_settings->utm: null,
                'socials' => $package_settings->socials ? $package_settings->socials: null,
                'fonts' => $package_settings->fonts ? $package_settings->fonts: null,
                'projects_limit' => $package_settings->projects_limit ? $package_settings->projects_limit: null,
                'biolinks_limit' => $package_settings->biolinks_limit ? $package_settings->biolinks_limit: null,
                'links_limit' => $package_settings->links_limit ? $package_settings->links_limit: null,
                'domains_limit' => $package_settings->domains_limit ? $package_settings->domains_limit: null,

            ]);
            if(Database::exists('user_id', 'users', ['name' => $name])) {

                $prefix = string_generate(3);
                $name = $name .$prefix;
                $email = 'temp' .$name .'@gmail.com';
                $user_repeat = 1;
                $stmt = Database::$database->prepare("INSERT INTO `users` (`email`, `password`, `name`, `email_activation_code`, `active`,  `package_id`, `package_expiration_date`, `package_settings`, `language`, `timezone`, `date`, `total_logins`, `temp_status`, `user_repeat`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssssssssss', $email, $password, $name, $email_code, $active, $package_id, $package_expiration_date, $settings, $language, $timezone, $date, $total_logins, $temp_status, $user_repeat);
                $stmt->execute();
                $registered_user_id = $stmt->insert_id;
                $stmt->close();

            } else {
                
                $stmt = Database::$database->prepare("INSERT INTO `users` (`email`, `password`, `name`, `email_activation_code`, `active`,  `package_id`, `package_expiration_date`, `package_settings`, `language`, `timezone`, `date`, `total_logins`, `temp_status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssssssssss', $email, $password, $name, $email_code, $active, $package_id, $package_expiration_date, $settings, $language, $timezone, $date, $total_logins, $temp_status);
                $stmt->execute();
                $registered_user_id = $stmt->insert_id;
                $stmt->close();
            }
            
            /* Clean email and encrypt the password */
            $password = 'temppass';
            /* Check for any errors */
            if(empty($email)) {
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
                $user_id = $login_account->user_id;
                
            }
            
            $user_id = $login_account->user_id;
            $project_name = "demoproject_" .$name;



            /* Create a new temp project part */

            if(empty($project_name)) {
                $errors[] = $this->language->global->error_message->empty_fields;
            }

            if(empty($errors)) {

                /* Insert to database */
                Database::insert_wp('projects', [
                    'user_id' => $user_id,
                    'name' => $project_name,
                    'date' => \Altum\Date::$date
                ]);

            }

            
            /* Create a new temp biolink part */

            $project_id = Database::simple_get('project_id', 'projects', ['name' => $project_name]);
            $url = 'demolink_' .$name;
            // $domain_id = 0;


            if(!Database::exists('project_id', 'projects', ['user_id' => $user_id, 'project_id' => $project_id])) {
                die();
            }
            
            /* Check for duplicate url if needed */
            if($url) {
                if(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
                    Response::json($this->language->create_biolink_modal->error_message->url_exists, 'error');
                }
            }

            /* Start the creation process */
            $url = $url ? $url : string_generate(10);
            $type = 'biolink';
            $subtype = 'base';
            $affiliate_url = "https://linkinbio.xyz";
            $affiliate_name = "https://linkinbio.xyz";
            
            $settings = json_encode([
                'title' => $this->language->link->biolink->title_default_exception,
                'description' => $this->language->link->biolink->description_default,
                'display_verified' => false,
                'image' => '',
                'background_type' => 'preset',
                'background' => 'one',
                'text_color' => 'white',
                'socials_color' => 'white',
                'google_analytics' => '',
                'facebook_pixel' => '',
                'display_branding' => true,
                'branding' => [
                    'url' => $affiliate_url,
                    'name' => $affiliate_name
                ],
                'seo' => [
                    'title' => '',
                    'meta_description' => ''
                ],
                'utm' => [
                    'medium' => '',
                    'source' => '',
                ],
                'socials' => [],
                'font' => null
            ]);

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
                $url = string_generate(10);
            }

            $this->check_url($url);

            $username = Database::simple_get('name', 'users', ['user_id' => $user_id]);
            $name = 'Get your LinkinBio';
            $temp_url = string_generate(10);
 
            Database::insert_wp('links', [
                'project_id' => $project_id,
                'user_id' => $user_id,
                'domain_id' => $domain_id,
                'type' => $type,
                'subtype' => $subtype,
                'url' => $url,
                'settings' => $settings,
                'date' => \Altum\Date::$date,
                'active' => 0,
                'templink' => $temp_url
            ]);
            $link_id = Database::simple_get('link_id', 'links', ['url' => $url, 'user_id' => $user_id, 'project_id' => $project_id]);

            /* Insert a first biolink link */
            $url = string_generate(10);
            $location_url = $affiliate_url;
            $type = 'biolink';
            $subtype = 'link';
            $settings = json_encode([
                'name' => $name,
                'text_color' => 'black',
                'background_color' => 'white',
                'outline' => false,
                'border_radius' => 'rounded',
                'animation' => false,
                'icon' => ''
            ]);

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url])) {
                $url = string_generate(10);
            }
            Database::insert_wp('links', [
                'project_id' => $project_id,
                'biolink_id'=> $link_id,
                'user_id' => $user_id,
                'type' => $type,
                'subtype' => $subtype,
                'url' => $url,
                'location_url' => $location_url,
                'settings' => $settings,
                'date' => \Altum\Date::$date,
                'active' => 0,
                'templink' => $temp_url
            ]);
            
            
            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $user_id);
            
            redirect('link/' .$link_id);
    
        }

        private function check_url($url) {

            if($url) {

                /* Make sure the url alias is not blocked by a route of the product */
                if(array_key_exists($url, Router::$routes[''])) {
                    Response::json($this->language->link->error_message->blacklisted_url, 'error');
                }
    
                /* Make sure the custom url is not blacklisted */
                if(in_array($url, explode(',', $this->settings->links->blacklisted_keywords))) {
                    Response::json($this->language->link->error_message->blacklisted_keyword, 'error');
                }
            }
        }
    
    }
?>
