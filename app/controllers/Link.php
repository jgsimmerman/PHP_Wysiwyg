<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Authentication;
use Altum\Models\Domain;
use Altum\Title;
use Altum\Middlewares\Csrf;
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;

class Link extends Controller {
    public $link;

    public function index() {

        Authentication::guard();

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : false;
        $method = isset($this->params[1]) && in_array($this->params[1], ['settings', 'statistics']) ? $this->params[1] : 'settings';

        /* Make sure the link exists and is accessible to the user */
        if(!$this->link = Database::get('*', 'links', ['link_id' => $link_id, 'user_id' => $this->user->user_id])) {
            redirect('dashboard');
        }

        $this->link->settings = json_decode($this->link->settings);

        /* Get the current domain if needed */
        $this->link->domain = $this->link->domain_id ? (new Domain())->get_domain($this->link->domain_id) : null;

        /* Determine the actual full url */
        $this->link->full_url = $this->link->domain ? $this->link->domain->url . $this->link->url : url($this->link->url);

        /*  Get the current affiliate url  */
        $affiliate_id = Database::simple_get('affiliate_id', 'users', ['user_id' => $this->user->user_id]);

        if($affiliate_id == null) {

            $affiliate_url = '';
        } else {

            $affiliate_url = "https://linkinbio.xyz/?free=" .$affiliate_id;
        }
        
        $this->link->affiliate_url = $affiliate_url;

        /* Handle code for different parts of the page */
        switch($method) {
            
            case 'settings':

                if($this->link->type == 'biolink') {
                    
                    $subtype_instagramfeed = 'instagramfeed';
                    $linkid_test = $this->link->link_id;
                    $userid_test = $this->user->user_id;

                    $link_id_db = Database::simple_get('link_id', 'links', ['biolink_id' => $this->link->link_id, 'subtype' => $subtype_instagramfeed, 'user_id' => $this->user->user_id]);

                    /* Get the links available for the biolink */
                    $link_links_result = $this->database->query("SELECT * FROM `links` WHERE `biolink_id` = {$this->link->link_id} AND `subtype` != 'tawkchat' ORDER BY `order` ASC");
                    $tawkchat_result = $this->database->query("SELECT * FROM `links` WHERE `biolink_id` = {$this->link->link_id} AND `subtype` = 'tawkchat'");

                    if($tawkchat_result->num_rows > 0) {

                        $row = $tawkchat_result->fetch_object();
                        $widget_code = $row->settings;

                        $pattern1 = 'var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();';
                        $pattern2 = 'var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];';
                        $pattern3 = 's1.async=true;';
                        $pattern4 = "s1.charset='UTF-8';";
                        $pattern5 = "s1.setAttribute('crossorigin','*');";
                        $pattern6 = "s0.parentNode.insertBefore(s1,s0);";

                        if(strpos($widget_code, $pattern1) && strpos($widget_code, $pattern2) && strpos($widget_code, $pattern3) && strpos($widget_code, $pattern4) && strpos($widget_code, $pattern5) && strpos($widget_code, $pattern6)) {

                            $is_validate = 'is_validate';
                        } else {
                            $is_validate = 'not_validate';
                        }

                    } else {
                        $is_validate = 'empty_widget_code';
                    }
                    
                    if($link_id_db) {
                        
                        $shopify_products = Database::simple_get('products', 'shopify_tokens', ['shopify_link_id' => $link_id_db, 'user_id' => $this->user->user_id]);

                        $tagged_products_result = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $link_id_db, 'user_id' => $this->user->user_id]);

                    } else {

                        $shopify_products = '';
                        $tagged_products_result = '';
                    }

                    
                    $biolink_link_types = require APP_PATH . 'includes/biolink_link_types.php';

                    $instagram = new InstagramBasicDisplay([
                        'appId'         => '3537622526297343',
                        'appSecret'     => '6435e7c3d1c883c7b129f11b9226810b',
                        'redirectUri'   => url(). 'instagramfeed'
                    ]);
                    $instagram_redirecturi = $instagram->getLoginUrl();

                    /* Add the modals for creating the links inside the biolink */
                    foreach($biolink_link_types as $key) {
                        $data = [
                            'link'                      => $this->link,
                            'affiliate_url'             => $affiliate_url,
                            'instagram_redirecturi'     => $instagram_redirecturi
                        ];
                        $view = new \Altum\Views\View('link/settings/create_' . $key . '_modal.settings.biolink.method', (array) $this);
                        \Altum\Event::add_content($view->run($data), 'modals');
                    }

                    if($this->link->subtype != 'base') {
                        redirect('link/' . $this->link->biolink_id);
                    }
                }

                /* Get the available domains to use */
                $domains = (new Domain())->get_domains($this->user);

                /* Prepare variables for the view */
                $data = [
                    'link'                      => $this->link,
                    'affiliate_url'             => $affiliate_url,
                    'method'                    => $method,
                    'link_links_result'         => $link_links_result ?? null,
                    'domains'                   => $domains,
                    'tawkchat_result'           => $is_validate,
                    'shopify_products_result'   => $shopify_products,
                    'tagged_products_result'    => $tagged_products_result
                ];

                break;

            case 'statistics':

                $type = isset($this->params[2]) && in_array($this->params[2], ['lastactivity', 'referrers', 'countries', 'operatingsystems', 'browsers', 'devices', 'browserlanguages']) ? Database::clean_string($this->params[2]) : 'lastactivity';
                $start_date = isset($_GET['start_date']) ? Database::clean_string($_GET['start_date']) : null;
                $end_date = isset($_GET['end_date']) ? Database::clean_string($_GET['end_date']) : null;

                $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

                /* Get data needed for statistics from the database */
                $logs = [];
                $logs_chart = [];

                $logs_result = Database::$database->query("
                    SELECT
                        COUNT(`count`) AS `uniques`,
						SUM(`count`) AS `impressions`,
                        DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`
                    FROM
                         `track_links`
                    WHERE
                        `link_id` = {$this->link->link_id}
                        AND (`date` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
                    GROUP BY
                        `formatted_date`
                    ORDER BY
                        `formatted_date`
                ");

                /* Generate the raw chart data and save logs for later usage */
                while($row = $logs_result->fetch_object()) {
                    $logs[] = $row;

                    $row->formatted_date = \Altum\Date::get($row->formatted_date, 4);

                    $logs_chart[$row->formatted_date] = [
                        'impressions'        => $row->impressions,
                        'uniques'            => $row->uniques,
                    ];
                }

                $logs_chart = get_chart_data($logs_chart);

                /* Get data based on what statistics are needed */
                switch($type) {
                    case 'lastactivity':

                        $result = Database::$database->query("
                            SELECT
                                `dynamic_id`,
                                `referrer`,
                                `country_code`,
                                `os_name`,
                                `browser_name`,
                                `browser_language`,
                                `device_type`,
                                `last_date`
                            FROM
                                `track_links`
                            WHERE
                                `link_id` = {$this->link->link_id}
                                AND (`date` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
                            ORDER BY
                                `last_date` DESC
                            LIMIT 25
                        ");

                    break;

                    case 'referrers':
                    case 'countries':
                    case 'operatingsystems':
                    case 'browsers':
                    case 'devices':
                    case 'browserlanguages':

                        $columns = [
                            'referrers' => 'referrer',
                            'countries' => 'country_code',
                            'operatingsystems' => 'os_name',
                            'browsers' => 'browser_name',
                            'devices' => 'device_type',
                            'browserlanguages' => 'browser_language'
                        ];

                        $result = Database::$database->query("
                            SELECT
                                `{$columns[$type]}`,
                                COUNT({$columns[$type]}) AS `total`
                            FROM
                                 `track_links`
                            WHERE
                                `link_id` = {$this->link->link_id}
                                AND (`date` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
                            GROUP BY
                                `{$columns[$type]}`
                            ORDER BY
                                `total` DESC
                            LIMIT 250
                        ");

                        break;
                }

                $statistics_rows = [];

                while($row = $result->fetch_object()) {
                    $statistics_rows[] = $row;
                }

                /* Prepare the statistics method View */
                $data = [
                    'rows' => $statistics_rows
                ];

                $view = new \Altum\Views\View('link/statistics/' . $type . '.statistics.method', (array) $this);
                $this->add_view_content('statistics.method', $view->run($data));

                /* Prepare variables for the view */
                $data = [
                    'link'              => $this->link,
                    'method'            => $method,
                    'type'              => $type,
                    'date'              => $date,
                    'logs'              => $logs,
                    'logs_chart'        => $logs_chart
                ];

                break;
        }

        /* Prepare the method View */
        $view = new \Altum\Views\View('link/' . $method . '.method', (array) $this);
        $this->add_view_content('method', $view->run($data));

        /* Prepare the View */
        $data = [
            'link'      => $this->link,
            'method'    => $method
        ];

        $view = new \Altum\Views\View('link/index', (array) $this);
        $this->add_view_content('content', $view->run($data));

        /* Set a custom title */
        Title::set(sprintf($this->language->link->title, $this->link->url));

    }

}
