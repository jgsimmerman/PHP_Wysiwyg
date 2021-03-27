<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;

    class Apiwp extends Controller {

        public function index() {

            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);
        
            $package_id = $rawData->package_id;
            $userwp_id = $rawData->user_id;
            $email = $rawData->email;
            $password_origin = $rawData->password;
            $name = $rawData->name;
            $payment_id = $rawData->payment_subscription_id;
            $ip_addr = $rawData->ip;
            $country = $rawData->country;
            $last_user_agent = $rawData->last_user_agent;

            // file_put_contents('./password.log', $password_origin , FILE_APPEND | LOCK_EX);

            if(!empty($rawData)) {

                $password = password_hash($password_origin, PASSWORD_DEFAULT);
                $email_code = md5($email . microtime());
                $active = '1';
                $total_logins = $active == '1' ? 1 : 0;
                $language = Language::$language;
                $date = \Altum\Date::$date;
                $timezone = 'UTC';
                $expiration_date = $rawData->expiration_date;
                $date = \Altum\Date::$date;
                $time_expirateion_date = strtotime($expiration_date);
                $time_date = strtotime($date);
                $expiration_days = round(($time_expirateion_date - $time_date) / 86400) ;
                $package_expiration_date = \Altum\Date::get('', 6, '', $expiration_days);
                if($package_id == 'free') {
                    $package_id = 1;
                }
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

                $affiliate_id = Database::simple_get('affiliate_id', 'affiliate_info', ['userwp_id' => $userwp_id]);

                if(Database::exists('user_id', 'users', ['email' => $email])) {
                    
                    Database::update(
                        'users',
                        [
                            'package_id' => $package_id,
                            'package_expiration_date' => $package_expiration_date,
                            'package_settings' => $settings,
                            'payment_subscription_id' => $payment_id
                        ],
                        [
                            'email' => $email
                        ]
                    );

                    print_r("user package update success");
                    exit(1);

                }
                
                Database::insert_wp('users', [
                    'email' => $email,
                    'password' => $password,
                    'name' => $name,
                    'email_activation_code' => $email_code,
                    'active' => $active,
                    'package_id' => $package_id,
                    'package_expiration_date' => $package_expiration_date,
                    'package_settings' => $settings,
                    'payment_subscription_id' => $payment_id,
                    'language' => $language,
                    'timezone' => $timezone,
                    'date' => $date,
                    'ip' => $ip_addr,
                    'country' => $country,
                    'last_user_agent' => $last_user_agent,
                    'total_logins' => $total_logins,
                    'affiliate_id' => $affiliate_id,
                    'userwp_id' => $userwp_id
                ]);

                exit(1);
            }

        }
    }
    
?>

