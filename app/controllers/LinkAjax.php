<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Date;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Response;
use Altum\Routing\Router;

class LinkAjax extends Controller {

    public function index() {

        /* Mail subscriber form submission check check */
        if($_POST['request_type'] !== 'mail') {
            Authentication::guard();
        } else {
            $this->mail();
        }

        if(!empty($_POST) && (Csrf::check('token') || Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Status toggle */
                case 'is_enabled_toggle': $this->is_enabled_toggle(); break;

                /* Duplicate link */
                case 'duplicate': $this->duplicate(); break;

                /* Order links */
                case 'order': $this->order(); break;

                /* Create */
                case 'create': $this->create(); break;

                /* Update */
                case 'update': $this->update(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

                case 'delete_shopify_tag': $this->delete_shopify_tag(); break;

            }

        }

        /* Update user repeat status */

        if($_POST['request_type'] === "update_repeat") {

            $this->update_user_repeat();
        }

        die($_POST['request_type']);
    }

    private function is_enabled_toggle() {
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get the current status */
        $link = Database::get(['link_id', 'biolink_id', 'is_enabled'], 'links', ['link_id' => $_POST['link_id']]);

        if($link) {
            $new_is_enabled = (int) !$link->is_enabled;

            Database::$database->query("UPDATE `links` SET `is_enabled` = {$new_is_enabled} WHERE `user_id` = {$this->user->user_id} AND `link_id` = {$link->link_id}");

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success');
        }
    }

    private function duplicate() {
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get the link data */
        $link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id, 'type' => 'biolink', 'subtype' => 'link']);

        if($link) {
            $link->settings = json_decode($link->settings);

            $url = string_generate(10);
            $settings = json_encode([
                'name' => $link->settings->name,
                'text_color' => $link->settings->text_color,
                'background_color' => $link->settings->background_color,
                'outline' => $link->settings->outline,
                'border_radius' => $link->settings->border_radius,
                'animation' => $link->settings->animation,
                'icon' => $link->settings->icon
            ]);

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url])) {
                $url = string_generate(10);
            }

            $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssssss', $link->project_id, $link->biolink_id, $this->user->user_id, $link->type, $link->subtype, $url, $link->location_url, $settings, $link->start_date, $link->end_date, \Altum\Date::$date);
            $stmt->execute();
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success', ['url' => url('link/' . $link->biolink_id . '?tab=links')]);

        }
    }

    private function order() {

        if(isset($_POST['links']) && is_array($_POST['links'])) {
            foreach($_POST['links'] as $link) {
                $link['link_id'] = (int) $link['link_id'];
                $link['order'] = (int) $link['order'];

                /* Update the link order */
                $stmt = $this->database->prepare("UPDATE `links` SET `order` = ? WHERE `link_id` = ? AND `user_id` = ?");
                $stmt->bind_param('sss', $link['order'], $link['link_id'], $this->user->user_id);
                $stmt->execute();
                $stmt->close();

            }
        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success');
    }

    private function create() {
        $_POST['type'] = trim(Database::clean_string($_POST['type']));

        /* Check for possible errors */
        if(!in_array($_POST['type'], ['link', 'biolink'])) {
            die();
        }

        switch($_POST['type']) {
            case 'link':

                $this->create_link();

                break;

            case 'biolink':

                $biolink_link_types = require APP_PATH . 'includes/biolink_link_types.php';

                /* Check for subtype */
                if(isset($_POST['subtype']) && in_array($_POST['subtype'], $biolink_link_types)) {
      
                    $_POST['subtype'] = trim(Database::clean_string($_POST['subtype']));

                    if($_POST['subtype'] == 'link') {
                        $this->create_biolink_link();
                    } else if($_POST['subtype'] == 'mail') {
                        $this->create_biolink_mail();
                    } else if($_POST['subtype'] == 'text') {
                        $this->create_biolink_text();
                    } else if($_POST['subtype'] == 'link_affiliate') {
                        $this->create_link_affiliate();
                    } else if($_POST['subtype'] == 'pdf') {
                        $this->create_biolink_pdf();
                    } else if($_POST['subtype'] == 'tawkchat') {
                        $this->create_biolink_tawkchat();
                    } else if($_POST['subtype'] == 'youtube_live') {
                        $this->create_biolink_youtube_live();
                    } else {
                        $this->create_biolink_other($_POST['subtype']);
                    }

                } else {
                    /* Base biolink */
                    $this->create_biolink();
                }

                break;
        }

        die();
    }

    private function create_link() {

        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        if(!Database::exists('project_id', 'projects', ['user_id' => $this->user->user_id, 'project_id' => $_POST['project_id']])) {
            die();
        }

        if(empty($_POST['location_url'])) {
            Response::json($this->language->global->error_message->empty_fields, 'error');
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        /* Make sure that the user didn't exceed the limit */
        $user_total_links = Database::$database->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'link'")->fetch_object()->total;
        if($this->user->package_settings->links_limit != -1 && $user_total_links >= $this->user->package_settings->links_limit) {
            Response::json($this->language->create_link_modal->error_message->links_limit, 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {

            if(Database::exists('link_id', 'links', ['url' => $_POST['url'], 'domain_id' => $domain_id])) {
                Response::json($this->language->create_link_modal->error_message->url_exists, 'error');
            }

        }

        if(empty($errors)) {
            $url = $_POST['url'] ? $_POST['url'] : string_generate(10);
            $type = 'link';
            $subtype = '';
            $settings = '';

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
                $url = string_generate(10);
            }

            /* Insert to database */
            $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $_POST['project_id'], $this->user->user_id, $domain_id, $type, $subtype, $url, $_POST['location_url'], $settings, \Altum\Date::$date);
            $stmt->execute();
            $link_id = $stmt->insert_id;
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success', ['url' => url('link/' . $link_id)]);
        }
    }

    private function create_biolink() {

        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        if(!Database::exists('project_id', 'projects', ['user_id' => $this->user->user_id, 'project_id' => $_POST['project_id']])) {
            die();
        }

        $affiliate_id = Database::simple_get('affiliate_id', 'users', ['user_id' => $this->user->user_id]);

        /* Make sure that the user didn't exceed the limit */
        $user_total_biolinks = Database::$database->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'biolink' AND `subtype` = 'base'")->fetch_object()->total;
        if($this->user->package_settings->biolinks_limit != -1 && $user_total_biolinks >= $this->user->package_settings->biolinks_limit) {
            Response::json($this->language->create_biolink_modal->error_message->biolinks_limit, 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(Database::exists('link_id', 'links', ['url' => $_POST['url'], 'domain_id' => $domain_id])) {
                Response::json($this->language->create_biolink_modal->error_message->url_exists, 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ? $_POST['url'] : string_generate(10);
        $type = 'biolink';
        $subtype = 'base';
        // $affiliate_url = "https://linkinbio.xyz/";

        if($affiliate_id == null) {

            $affiliate_url = "https://linkinbio.xyz";
            $affiliate_name = "https://linkinbio.xyz";
            
        } else {

            $affiliate_url = "https://linkinbio.xyz/?free=" .$affiliate_id;
            $affiliate_name = "https://linkinbio.xyz/?free=" .$affiliate_id;
        }

        // $affiliate_url = "https://linkinbio.xyz/?free=" .$affiliate_id;
        // $affiliate_name = "https://linkinbio.xyz/?free=" .$affiliate_id;
        $settings = json_encode([
            'title' => $this->language->link->biolink->title_default,
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
            'alt_socials' => [],
            'font' => null
        ]);

        /* Generate random url if not specified */
        while(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
            $url = string_generate(10);
        }

        $this->check_url($_POST['url']);

        $username = Database::simple_get('name', 'users', ['user_id' => $this->user->user_id]);
        $name = 'Get your LinkinBio';

        /* Insert to database */
        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `settings`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $_POST['project_id'], $this->user->user_id, $domain_id, $type, $subtype, $url,  $settings, \Altum\Date::$date);
        $stmt->execute();
        $link_id = $stmt->insert_id;
        $stmt->close();

        /* Insert a first biolink link */
        $url = string_generate(10);
        // $location_url = url();
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
            'animation_duration' => '2s',
            'icon' => ''
        ]);

        /* Generate random url if not specified */
        while(Database::exists('link_id', 'links', ['url' => $url])) {
            $url = string_generate(10);
        }

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssss', $_POST['project_id'], $link_id, $this->user->user_id, $type, $subtype, $url, $location_url, $settings, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $link_id)]);
    }

    private function create_biolink_link() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $this->check_location_url($_POST['location_url']);

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = string_generate(10);
        $type = 'biolink';
        $subtype = 'link';
        $settings = json_encode([
            'name' => $this->language->link->biolink->link->name_default,
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_duration' => '2s',
            'icon' => ''
        ]);

        /* Generate random url if not specified */
        while(Database::exists('link_id', 'links', ['url' => $url])) {
            $url = string_generate(10);
        }

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $_POST['location_url'], $settings, $new_order, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_link_affiliate() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $this->check_location_url($_POST['location_url']);

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $username = Database::simple_get('name', 'users', ['user_id' => $this->user->user_id]);
        $name = 'Get your LinkinBio';
        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = string_generate(10);
        $type = 'biolink';
        $subtype = 'link';
        $settings = json_encode([
            'name' => $this->language->link->biolink->link->name_default,
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

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $_POST['location_url'], $settings, $new_order, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);

    }

    private function create_biolink_other($subtype) {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $this->check_location_url($_POST['location_url']);

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = string_generate(10);
        $type = 'biolink';
        $settings = json_encode([]);

        /* Generate random url if not specified */
        while(Database::exists('link_id', 'links', ['url' => $url])) {
            $url = string_generate(10);
        }

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $_POST['location_url'], $settings, $new_order, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);

    }

    private function create_biolink_mail() {

        $_POST['link_id'] = (int) $_POST['link_id'];

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'mail';
        $settings = json_encode([
            'name' => $this->language->link->biolink->mail->name_default,
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'icon' => '',

            'email_placeholder' => $this->language->link->biolink->mail->email_placeholder_default,
            'button_text' => $this->language->link->biolink->mail->button_text_default,
            'success_text' => $this->language->link->biolink->mail->success_text_default,
            'show_agreement' => false,
            'agreement_url' => '',
            'agreement_text' => '',
            'mailchimp_api' => '',
            'mailchimp_api_list' => '',
            'webhook_url' => ''
        ]);

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $location_url, $settings, $new_order, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_text() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['title'] = trim(Database::clean_string($_POST['title']));
        $_POST['description'] = trim(Database::clean_string($_POST['description']));

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'text';
        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'title_text_color' => 'white',
            'description_text_color' => 'white',
        ]);

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $location_url, $settings, $new_order, \Altum\Date::$date);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_pdf() {
        
        $pdf_allowed_extensions = ['pdf'];
        $pdf = (bool) !empty($_FILES['pdfFile']['name']);
        $_POST['pdf_title'] = Database::clean_string($_POST['pdf_title']);

        /* Check for any errors */
        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }
        $link->settings = json_decode($link->settings);

        /* Check for any errors on the PDF file */
        if( $pdf ) {

            $pdf_file_extension = explode('.', $_FILES['pdfFile']['name']);
            $pdf_file_extension = strtolower(end($pdf_file_extension));
            $pdf_file_temp = $_FILES['pdfFile']['tmp_name'];

            if($_FILES['pdfFile']['error']) {
                Response::json($this->language->global->error_message->file_upload, 'error');
            }

            if(!in_array($pdf_file_extension, $pdf_allowed_extensions)) {
                Response::json($this->language->global->error_message->invalid_file_type, 'error');
            }

            /* Generate a new pdf name */
            $pdf_new_name = md5(time() . rand()) . '.' .$pdf_file_extension;
   
            /* Upload the original */
            move_uploaded_file($pdf_file_temp, UPLOADS_PATH. 'pdfs/' .$pdf_new_name);
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }
        $type = 'biolink';
        $subtype = 'pdf';
        $url = string_generate(10);
        $location_url = url() .'uploads/' .'pdfs/' .$pdf_new_name;
        $settings = json_encode([
            'title' => $_POST['pdf_title'],
            'pdf' => $pdf_new_name,
            'title_color' => '#000',
            'background_color' => '#fff',
            'border_radius' => 'straight'
        ]);
        $date = \Altum\Date::$date;
            
        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `date`, `order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $location_url, $settings, $date, $new_order);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }
    
    private function create_biolink_tawkchat() {

        /* Check for any errors */
        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }
        $subtype = $_POST['subtype'];
        $type = $_POST['type'];
        $url = string_generate(10);
        $settings = $_POST['widgetcode'];
        $date = \Altum\Date::$date;
        
        if(!$link_id = Database::simple_get('link_id', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => $_POST['subtype']])) {

            $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `date`, `order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $url, $settings, $date, $new_order);
            $stmt->execute();
            $stmt->close();

        } else {

            $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `user_id` = ? AND `biolink_id` = ? AND `subtype` = ?");
            $stmt->bind_param('ssss', $settings, $this->user->user_id, $_POST['link_id'], $_POST['subtype']);
            $stmt->execute();
            $stmt->close();

        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);

    }

    private function create_biolink_youtube_live() {

        $location_url = '';
        $channel_id = null;
        $video_id = null;
        $_POST['link_id'] = (int) $_POST['link_id'];
        $subtype = 'youtube_live';
        $nth_youtube = (int) $_POST['nth_youtube'];
        $channel_url = trim(Database::clean_string($_POST['location_url']));
        $res = \Altum\Youtube::get_id($channel_url, $nth_youtube);
        $formatted_res = json_decode($res);
        $video_id = $formatted_res->video_id;
        $channel_id = $formatted_res->channel_id;
        $location_url = "https://www.youtube.com/watch?v=". $video_id;
        // $location_url = $channel_url;
        
        $this->check_location_url($location_url);

        if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'base'])) {
            die();
        }

        $max_order = Database::simple_get_max_order('order', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $_POST['link_id']], 'order');
        $new_order = (int) $max_order + 1;

        $url = string_generate(10);
        $type = 'biolink';
        $settings = json_encode([
            'channel_url'   => $channel_url,
            'nth_video'     => $nth_youtube
        ]);

        /* Generate random url if not specified */
        while(Database::exists('link_id', 'links', ['url' => $url])) {
            $url = string_generate(10);
        }

        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`,`date`, `channel_id`, `video_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssssss', $project_id, $_POST['link_id'], $this->user->user_id, $type, $subtype, $url, $location_url, $settings, $new_order, \Altum\Date::$date, $channel_id, $video_id);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);

    }
    
    private function update() {

        if(!empty($_POST)) {
            $_POST['type'] = trim(Database::clean_string($_POST['type']));

            /* Check for possible errors */
            if(!in_array($_POST['type'], ['link', 'biolink'])) {
                die();
            }
            if(!Csrf::check()) {
                Response::json($this->language->global->error_message->invalid_csrf_token, 'error');
            }

            switch($_POST['type']) {
                case 'link':

                    $this->update_link();

                    break;

                case 'biolink':

                    $biolink_link_types = require APP_PATH . 'includes/biolink_link_types.php';

                    /* Check for subtype */
                    if((isset($_POST['subtype']) && in_array($_POST['subtype'], $biolink_link_types)) || (isset($_POST['subtype']) && $_POST['subtype'] == 'shopify_products') || (isset($_POST['subtype']) && $_POST['subtype'] == 'cart_products') || (isset($_POST['subtype']) && $_POST['subtype'] == 'num_cart_products')) {
                        $_POST['subtype'] = trim(Database::clean_string($_POST['subtype']));

                        if($_POST['subtype'] == 'link') {

                            $this->update_biolink_link();
                        } else if($_POST['subtype'] == 'mail') {

                            $this->update_biolink_mail();
                        } else if($_POST['subtype'] == 'text') {

                            $this->update_biolink_text();
                        } else if ($_POST['subtype'] == 'pdf') {

                            $this->update_biolink_pdf();
                        } else if($_POST['subtype'] == 'instagramfeed') {

                            $this->update_instagram_biolink();
                        } else if($_POST['subtype'] == 'shopify_products') {

                            $this->update_shopify_products();
                        } else if ($_POST['subtype'] == 'cart_products') {

                            $this->update_cart_products();
                        } else if($_POST['subtype'] == 'num_cart_products') {

                            $this->update_num_cart_products();
                        } else if($_POST['subtype'] == 'youtube_live') {

                            $this->update_youtube_live();
                        }else {

                            $this->update_biolink_other($_POST['subtype']);
                        }

                    } else {
                        /* Base biolink */
                        $this->update_biolink();
                    }

                    break;
            }

        }

        die();
    }

    private function update_biolink() {

        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);
        $image_delete = isset($_POST['image_delete']) && $_POST['image_delete'] == 'true';
        $image_delete = isset($_POST['image_delete']) && $_POST['image_delete'] == 'true';

        $_POST['title'] = Database::clean_string($_POST['title']);
        $_POST['description'] = Database::clean_string($_POST['description']);
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        /* Check for any errors */
        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        $link->settings = json_decode($link->settings);

        /* Check for any errors on the logo image */
        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));

            $image_new_name = $_SESSION["fileTmp"];

            if($_FILES['image']['error']) {
                Response::json($this->language->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json($this->language->global->error_message->invalid_file_type, 'error');
            }
        }

        if($_POST['url'] == $link->url) {
            $url = $link->url;

            if($link->domain_id != $domain_id) {
                if(Database::exists('link_id', 'links', ['url' => $_POST['url'], 'domain_id' => $domain_id])) {
                    Response::json($this->language->create_biolink_modal->error_message->url_exists, 'error');
                }
            }

        } else {

            $url = $_POST['url'] ? $_POST['url'] : string_generate(10);

            if(Database::exists('link_id', 'links', ['url' => $_POST['url'], 'domain_id' => $domain_id])) {
                Response::json($this->language->create_biolink_modal->error_message->url_exists, 'error');
            }

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
                $url = string_generate(10);
            }

            $this->check_url($_POST['url']);
        }

        /* Delete avatar */
        if($image_delete) {
  
            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'avatars/' . $link->settings->image)) {
         
                unlink(UPLOADS_PATH . 'avatars/' . $link->settings->image);
            }
        }

        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#fff' : $_POST['text_color'];
        $_POST['socials_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['socials_color']) ? '#fff' : $_POST['socials_color'];
        $biolink_backgrounds = require APP_PATH . 'includes/biolink_backgrounds.php';
        $_POST['background_type'] = array_key_exists($_POST['background_type'], $biolink_backgrounds) ? $_POST['background_type'] : 'preset';
        $background = 'one';

        switch($_POST['background_type']) {
            case 'preset':
                $background = in_array($_POST['background'], $biolink_backgrounds['preset']) ? $_POST['background'] : 'one';
                break;

            case 'color':

                $background = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background']) ? '#000' : $_POST['background'];

                break;

            case 'gradient':

                $color_one = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background'][0]) ? '#000' : $_POST['background'][0];
                $color_two = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background'][1]) ? '#000' : $_POST['background'][1];

                $background = [
                    'color_one' => $color_one,
                    'color_two' => $color_two
                ];

                break;

            case 'image':

                $background = (bool) !empty($_FILES['background']['name']);

                /* Check for any errors on the logo image */
                if($background) {
                    $background_file_extension = explode('.', $_FILES['background']['name']);
                    $background_file_extension = strtolower(end($background_file_extension));
                    $background_file_temp = $_FILES['background']['tmp_name'];

                    if($_FILES['background']['error']) {
                        Response::json($this->language->global->error_message->file_upload, 'error');
                    }

                    if(!in_array($background_file_extension, $image_allowed_extensions)) {
                        Response::json($this->language->global->error_message->invalid_file_type, 'error');
                    }

                    /* Delete current image */
                    if(!empty($link->settings->background) && file_exists(UPLOADS_PATH . 'backgrounds/' . $link->settings->background)) {
                        unlink(UPLOADS_PATH . 'backgrounds/' . $link->settings->background);
                    }

                    /* Generate new name for logo */
                    $background_new_name = md5(time() . rand()) . '.' . $background_file_extension;

                    /* Upload the original */
                    move_uploaded_file($background_file_temp, UPLOADS_PATH . 'backgrounds/' . $background_new_name);

                    $background = $background_new_name;
                }

                break;
        }

        // $_POST['display_branding'] = (bool) isset($_POST['display_branding']);
        $_POST['display_branding'] = true;
        $_POST['display_verified'] = (bool) isset($_POST['display_verified']);
        $_POST['branding_name'] = isset($_POST['branding_name']) ? Database::clean_string($_POST['branding_name']) : '';
        $_POST['branding_url'] = isset($_POST['branding_url']) ? Database::clean_string($_POST['branding_url']) : '';
        $_POST['google_analytics'] = Database::clean_string($_POST['google_analytics']);
        $_POST['facebook_pixel'] = Database::clean_string($_POST['facebook_pixel']);
        $_POST['seo_title'] = Database::clean_string(mb_substr($_POST['seo_title'], 0, 70));
        $_POST['seo_meta_description'] = Database::clean_string(mb_substr($_POST['seo_meta_description'], 0, 160));
        $_POST['utm_medium'] = Database::clean_string($_POST['utm_medium']);
        $_POST['utm_source'] = Database::clean_string($_POST['utm_source']);

        /* Make sure the socials sent are proper */
        $biolink_socials = require APP_PATH . 'includes/biolink_socials.php';

        foreach($_POST['socials'] as $key => $value) {

            if(!array_key_exists($key, $biolink_socials)) {
                unset($_POST['socials'][$key]);
            } else {
                $_POST['socials'][$key] = Database::clean_string($_POST['socials'][$key]);
            }

        }

        $biolink_alt_socials = require APP_PATH . 'includes/biolink_alt_socials.php';

        foreach($_POST['socials'] as $key => $value) {

            if(!array_key_exists($key, $biolink_alt_socials)) {
                unset($_POST['alt_socials'][$key]);
            } else {
                $_POST['alt_socials'][$key] = Database::clean_string($_POST['alt_socials'][$key]);
            }

        }

        /* Make sure the font is ok */
        $biolink_fonts = require APP_PATH . 'includes/biolink_fonts.php';
        $_POST['font'] = !array_key_exists($_POST['font'], $biolink_fonts) ? false : Database::clean_string($_POST['font']);

        if($image) {

            $image_data = $image_new_name;
        } else {
            if ($image_delete) {

                $image_data = '';
            } else {
                
                $image_data = $link->settings->image;
            }
        }
        /* Set the new settings variable */
        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'display_verified' => $_POST['display_verified'],
            'image' => $image_data,
            'background_type' => $_POST['background_type'],
            'background' => $background ? $background : $link->settings->background,
            'text_color' => $_POST['text_color'],
            'socials_color' => $_POST['socials_color'],
            'google_analytics' => $_POST['google_analytics'],
            'facebook_pixel' => $_POST['facebook_pixel'],
            'display_branding' => $_POST['display_branding'],
            'branding' => [
                'name' => $_POST['branding_name'],
                'url' => $_POST['branding_url'],
            ],
            'seo' => [
                'title' => $_POST['seo_title'],
                'meta_description' => $_POST['seo_meta_description'],
            ],
            'utm' => [
                'medium' => $_POST['utm_medium'],
                'source' => $_POST['utm_source'],
            ],
            'socials' => $_POST['socials'],
            'alt_socials' => $_POST['alt_socials'],
            'font' => $_POST['font']
        ]);

        /* Update the record */
        $stmt = Database::$database->prepare("UPDATE `links` SET `domain_id` = ?, `url` = ?, `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ssss', $domain_id, $url, $settings, $link->link_id);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');

    }

    private function update_biolink_link() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        //        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], ['false', 'bounce', 'tada', 'wobble', 'swing', 'shake', 'rubberBand', 'pulse', 'flash']) ? Database::clean_string($_POST['animation']) : false;
        switch($_POST['animation_speed']) {
            case 'faster':
                $animation_duration = '0.7s';
                break;
            case 'fast':
                $animation_duration = '1s';
                break;
            case 'normal':
                $animation_duration = '2s';
                break;
            case 'slow':
                $animation_duration = '3s';
                break;
            case 'slower':
                $animation_duration = '4s';
                break;
            default:
                break;
        }
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];
        if(isset($_POST['schedule']) && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }

        /* Check for any errors */
        $fields = ['location_url', 'name'];

        /* Check for any errors */
        foreach($_POST as $key => $value) {
            if(empty($value) && in_array($key, $fields) == true) {
                Response::json($this->language->global->error_message->empty_fields, 'error');
                break 1;
            }
        }

        //        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        /* Check for duplicate url if needed */
        //        if($_POST['url'] && $_POST['url'] != $link->url) {
        //            if(Database::exists('link_id', 'links', ['url' => $_POST['url']])) {
        //                Response::json($this->language->create_biolink_link_modal->error_message->url_exists, 'error');
        //            }
        //        } else if(!$_POST['url']) {
        //            $_POST['url'] = string_generate(10);
        //
        //            /* Generate random url if not specified */
        //            while(Database::exists('link_id', 'links', ['url' => $_POST['url']])) {
        //                $_POST['url'] = string_generate(10);
        //            }
        //
        //            $this->check_url($_POST['url']);
        //        }

        $settings = json_encode([
            'name' => $_POST['name'],
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'animation_duration' => $animation_duration,
            'icon' => $_POST['icon']
        ]);

        $stmt = Database::$database->prepare("UPDATE `links` SET `location_url` = ?, `settings` = ?, `start_date` = ?, `end_date` = ? WHERE `link_id` = ?");
        $stmt->bind_param('sssss', $_POST['location_url'], $settings, $_POST['start_date'], $_POST['end_date'], $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        $resDetails = ['link_id' => $_POST['link_id'], 'title_value' => $_POST['name']];
        Response::json($this->language->link->success_message->settings_updated, 'success', $resDetails);

    }

    private function update_biolink_other($subtype) {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $this->check_location_url($_POST['location_url']);

        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        $stmt = Database::$database->prepare("UPDATE `links` SET `location_url` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $_POST['location_url'], $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_mail() {
        
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], ['false', 'bounce', 'tada', 'wobble', 'swing', 'shake', 'rubberBand', 'pulse', 'flash']) ? Database::clean_string($_POST['animation']) : false;
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];

        $_POST['email_placeholder'] = trim(Database::clean_string($_POST['email_placeholder']));
        $_POST['button_text'] = trim(Database::clean_string($_POST['button_text']));
        $_POST['success_text'] = trim(Database::clean_string($_POST['success_text']));
        $_POST['show_agreement'] = (bool) isset($_POST['show_agreement']);
        $_POST['agreement_url'] = trim(Database::clean_string($_POST['agreement_url']));
        $_POST['agreement_text'] = trim(Database::clean_string($_POST['agreement_text']));
        $_POST['mailchimp_api'] = trim(Database::clean_string($_POST['mailchimp_api']));
        $_POST['mailchimp_api_list'] = trim(Database::clean_string($_POST['mailchimp_api_list']));
        $_POST['webhook_url'] = trim(Database::clean_string($_POST['webhook_url']));

        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        $settings = json_encode([
            'name' => $_POST['name'],
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'icon' => $_POST['icon'],

            'email_placeholder' => $_POST['email_placeholder'],
            'button_text' => $_POST['button_text'],
            'success_text' => $_POST['success_text'],
            'show_agreement' => $_POST['show_agreement'],
            'agreement_url' => $_POST['agreement_url'],
            'agreement_text' => $_POST['agreement_text'],
            'mailchimp_api' => $_POST['mailchimp_api'],
            'mailchimp_api_list' => $_POST['mailchimp_api_list'],
            'webhook_url' => $_POST['webhook_url']
        ]);

        $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_text() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['title'] = trim(Database::clean_string($_POST['title']));
        $_POST['description'] = trim(Database::clean_string($_POST['description']));
        $_POST['title_text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['title_text_color']) ? '#fff' : $_POST['title_text_color'];
        $_POST['description_text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['description_text_color']) ? '#fff' : $_POST['description_text_color'];

        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'title_text_color' => $_POST['title_text_color'],
            'description_text_color' => $_POST['description_text_color'],
        ]);

        $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_pdf() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['title'] = trim(Database::clean_string($_POST['title']));
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#fff' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }
        $link->settings = json_decode($link->settings);
        $file_name = $link->settings->pdf;

        $pdf_allowed_extensions = ['pdf'];
        $pdf = (bool) !empty($_FILES['FileReplace']['name']);

     
        /* Check for any errors on the PDF file */
        if( $pdf ) {

            $pdf_file_extension = explode('.', $_FILES['FileReplace']['name']);
            $pdf_file_extension = strtolower(end($pdf_file_extension));
            $pdf_file_temp = $_FILES['FileReplace']['tmp_name'];

            if($_FILES['FileReplace']['error']) {
                Response::json($this->language->global->error_message->file_upload, 'error');
            }

            if(!in_array($pdf_file_extension, $pdf_allowed_extensions)) {
                Response::json($this->language->global->error_message->invalid_file_type, 'error');
            }
            if( !empty($link->settings->pdf) && file_exists(UPLOADS_PATH . 'pdfs/'. $file_name) ) {
                unlink(UPLOADS_PATH . 'pdfs/'. $file_name);
            }
            /* Generate a new pdf name */
            $pdf_new_name = md5(time() . rand()) . '.' .$pdf_file_extension;
   
            /* Upload the original */
            move_uploaded_file($pdf_file_temp, UPLOADS_PATH. 'pdfs/' .$pdf_new_name);
            $new_location_url = url() .'uploads/' .'pdfs/' .$pdf_new_name;
            $stmt = Database::$database->prepare("UPDATE `links` SET `location_url` = ? WHERE `link_id` = ?");
            $stmt->bind_param('ss', $new_location_url, $_POST['link_id']);
            $stmt->execute();
            $stmt->close();
    
        }

        $settings = json_encode([
            'title' => $_POST['title'],
            'pdf' => $pdf_new_name,
            'title_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'border_radius' => $_POST['border_radius']
        ]);

        $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');

    }

    private function update_instagram_biolink() {

        if($_POST['remove_status']) {

            $tag_number = 0;
            $tagged_products = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $_POST['link_id']]);
            $tagged_products = json_decode($tagged_products);
            $new_tagged_products = array();
            foreach($tagged_products as $key => $tagged_product_item) {
                $tagged_product_obj = get_object_vars($tagged_product_item);
                $tagged_product = $tagged_product_obj[key($tagged_product_obj)];
    
                if(key($tagged_product_obj) != $_POST['post_connected_link_id']) {
    
                    $tag_number++; 
                    $updated_tagged_product = [
                        key($tagged_product_obj) => [
                            'shopify_product_id'            => $tagged_product->shopify_product_id,
                            'shopify_product_vendor'        => $tagged_product->shopify_product_vendor,
                            'shopify_product_image_url'     => $tagged_product->shopify_product_image_url,
                            'shopify_product_title'         => $tagged_product->shopify_product_title,
                            'shopify_product_price'         => $tagged_product->shopify_product_price,
                            'shopify_product_option_key'    => $tagged_product->shopify_product_option_key,
                            'shopify_product_option_value'  => $tagged_product->shopify_product_option_value,
                            'shopify_product_variant_id'    => $tagged_product->shopify_product_variant_id,
                            'tag_number'                    => $tag_number,
                            'tag_position'                  => $tagged_product->tag_position,
                            'shopify_product_url'           => $tagged_product->shopify_product_url,
                            'num_add_to_cart_products'      => $tagged_product->num_add_to_cart_products
                        ]
                    ];
    
                    array_push($new_tagged_products, $updated_tagged_product);
                }
            }
    
            $new_tagged_products = json_encode($new_tagged_products);
    
            $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `shopify_link_id` = ?");
            $stmt->bind_param('ss', $new_tagged_products, $_POST['link_id']);
            $stmt->execute();
            $stmt->close();
        }

        $_POST['link_id'] = (int) $_POST['link_id'];

        if(!$_POST['remove_status']) {
            
            $this->check_url($_POST['post_connected_link']);
            $this->check_location_url($_POST['post_connected_link']);
        }
        $settings = Database::simple_get('settings', 'links', ['link_id' => $_POST['link_id']]);
        $settings = json_decode($settings);
        $profile_name = $settings->profile_name;
        $profile_pic_url = $settings->profile_pic_url;
        $medias = $settings->medias;
        $medias = json_decode($medias);

        foreach ($medias as $key => $media) {

            if($media->id == $_POST['post_connected_link_id']) {
                if(!$_POST['remove_status']) {
                    $media->link = $_POST['post_connected_link'];
                    break;
                } else {
                    $media->link = '';
                    break;
                }
            }

        }

        $settings = json_encode([
            'profile_name' => $profile_name,
            'profile_pic_url' => $profile_pic_url,
            'medias' => json_encode($medias)
        ]);

        $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['link_id']]);

        $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = {$_POST['link_id']}");
        $stmt->bind_param('s', $settings);
        $stmt->execute();
        $stmt->close();

        $_SESSION['save_status'] = true;
        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links')]);

    }

    private function update_shopify_products() {
        
        if(isset($_POST)) {

            $tagged_product = [
                $_POST['tag_post_id'] => [
                    'shopify_product_id'            => $_POST['shopify_product_id'],
                    'shopify_product_vendor'        => $_POST['shopify_product_vendor'],
                    'shopify_product_image_url'     => $_POST['shopify_product_image_url'],
                    'shopify_product_title'         => $_POST['shopify_product_title'],
                    'shopify_product_price'         => $_POST['shopify_product_price'],
                    'shopify_product_option_key'    => $_POST['shopify_product_option_key'],
                    'shopify_product_option_value'  => $_POST['shopify_product_option_value'],
                    'shopify_product_variant_id'    => $_POST['shopify_product_variant_id'],
                    'tag_number'                    => $_POST['tag_number'],
                    'tag_position'                  => $_POST['tag_position'],
                    'shopify_product_url'           => $_POST['shopify_product_url'],
                    'num_add_to_cart_products'      => 0
                ]
            ];
        }

        $tagged_products = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $_POST['link_id'], 'shop' => $_POST['shopify_product_vendor']]);
        if (!$tagged_products) {
            $tagged_products = array();
            array_push($tagged_products, $tagged_product);
        } else {
            $tagged_products = json_decode($tagged_products);
            array_push($tagged_products, $tagged_product);
        }

        $tagged_products = json_encode($tagged_products);
        $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['link_id']]);

        $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `shop` = ? AND `shopify_link_id` = ?");
        $stmt->bind_param('sss', $tagged_products, $_POST['shopify_product_vendor'], $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        
        $_SESSION['save_status'] = true;
        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links')]);

    }

    private function update_cart_products() {

        $total_carts = 0;
        if(isset($_POST)) {
            $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['shopify_link_id']]);
            $tagged_products = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $_POST['shopify_link_id'], 'shop' => $_POST['shopify_product_vendor']]);
            $tagged_products = json_decode($tagged_products);
            $new_tagged_products = array();
            foreach($tagged_products as $key => $tagged_product_item) {
                $tagged_product_obj = get_object_vars($tagged_product_item);
                $tagged_product = $tagged_product_obj[key($tagged_product_obj)];

                if((key($tagged_product_obj) == $_POST['post_id']) && ($tagged_product->shopify_product_variant_id == $_POST['shopify_product_variant_id'])) {

                    $num_add_to_cart_products = $tagged_product->num_add_to_cart_products + 1;
                    $total_carts += intval($num_add_to_cart_products);

                    $updated_tagged_product = [
                        $_POST['post_id'] => [
                            'shopify_product_id'            => $tagged_product->shopify_product_id,
                            'shopify_product_vendor'        => $tagged_product->shopify_product_vendor,
                            'shopify_product_image_url'     => $tagged_product->shopify_product_image_url,
                            'shopify_product_title'         => $tagged_product->shopify_product_title,
                            'shopify_product_price'         => $tagged_product->shopify_product_price,
                            'shopify_product_option_key'    => $tagged_product->shopify_product_option_key,
                            'shopify_product_option_value'  => $tagged_product->shopify_product_option_value,
                            'shopify_product_variant_id'    => $tagged_product->shopify_product_variant_id,
                            'tag_number'                    => $tagged_product->tag_number,
                            'tag_position'                  => $tagged_product->tag_position,
                            'shopify_product_url'           => $tagged_product->shopify_product_url,
                            'num_add_to_cart_products'      => $num_add_to_cart_products
                        ]
                    ];

                    array_push($new_tagged_products, $updated_tagged_product);
                } else {

                    $total_carts += intval($tagged_product->num_add_to_cart_products);
                    array_push($new_tagged_products, $tagged_product_item);
                }
            }

            $new_tagged_products = json_encode($new_tagged_products);

            $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `shop` = ? AND `shopify_link_id` = ?");
            $stmt->bind_param('sss', $new_tagged_products, $_POST['shopify_product_vendor'], $_POST['shopify_link_id']);
            $stmt->execute();
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
    
            Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links'), 'total_carts' => $total_carts]);
        }

    }

    private function update_num_cart_products() {

        $total_carts = 0;
        if(isset($_POST)) {
            $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['shopify_link_id']]);
            $tagged_products = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $_POST['shopify_link_id'], 'shop' => $_POST['shopify_product_vendor']]);
            $tagged_products = json_decode($tagged_products);
            $new_tagged_products = array();
            foreach($tagged_products as $key => $tagged_product_item) {
                $tagged_product_obj = get_object_vars($tagged_product_item);
                $tagged_product = $tagged_product_obj[key($tagged_product_obj)];

                if((key($tagged_product_obj) == $_POST['post_id']) && ($tagged_product->shopify_product_variant_id == $_POST['shopify_product_variant_id'])) {

                    
                    $total_carts += intval($_POST['num_add_to_cart_products']);

                    $updated_tagged_product = [
                        $_POST['post_id'] => [
                            'shopify_product_id'            => $tagged_product->shopify_product_id,
                            'shopify_product_vendor'        => $tagged_product->shopify_product_vendor,
                            'shopify_product_image_url'     => $tagged_product->shopify_product_image_url,
                            'shopify_product_title'         => $tagged_product->shopify_product_title,
                            'shopify_product_price'         => $tagged_product->shopify_product_price,
                            'shopify_product_option_key'    => $tagged_product->shopify_product_option_key,
                            'shopify_product_option_value'  => $tagged_product->shopify_product_option_value,
                            'shopify_product_variant_id'    => $tagged_product->shopify_product_variant_id,
                            'tag_number'                    => $tagged_product->tag_number,
                            'tag_position'                  => $tagged_product->tag_position,
                            'shopify_product_url'           => $tagged_product->shopify_product_url,
                            'num_add_to_cart_products'      => $_POST['num_add_to_cart_products']
                        ]
                    ];

                    array_push($new_tagged_products, $updated_tagged_product);
                } else {
                    
                    $total_carts += intval($tagged_product->num_add_to_cart_products);
                    array_push($new_tagged_products, $tagged_product_item);
                }
            }

            $new_tagged_products = json_encode($new_tagged_products);

            $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `shop` = ? AND `shopify_link_id` = ?");
            $stmt->bind_param('sss', $new_tagged_products, $_POST['shopify_product_vendor'], $_POST['shopify_link_id']);
            $stmt->execute();
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
    
            Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links'), 'total_carts' => $total_carts, 'sub_total_carts' => $_POST['num_add_to_cart_products'], 'post_id' => $_POST['post_id'], 'variant_id' => $_POST['shopify_product_variant_id']]);
        }

    }

    private function update_youtube_live() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $subtype = 'youtube_live';
        $nth_video = (int) $_POST['nth_video'];
        $channel_url = trim(Database::clean_string($_POST['channel_url']));
        $res = \Altum\Youtube::get_id($channel_url, $nth_video);
        $formatted_res = json_decode($res);
        $video_id = $formatted_res->video_id;
        $channel_id = $formatted_res->channel_id;
        $location_url = "https://www.youtube.com/watch?v=". $video_id;
        // $location_url = $channel_url;
        
        $this->check_location_url($location_url);

        $url = string_generate(10);
        $type = 'biolink';
        $settings = json_encode([
            'channel_url'   => $channel_url,
            'nth_video'     => $nth_video
        ]);

        $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['link_id']]);


        $stmt = Database::$database->prepare("UPDATE `links` SET `location_url` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $location_url, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        $stmt = Database::$database->prepare("UPDATE `links` SET `channel_id` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $channel_id, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        $stmt = Database::$database->prepare("UPDATE `links` SET `video_id` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ss', $video_id, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();


        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links'), 'subtype' => 'youtube_live']);

    }

    private function update_link() {

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url'])) : false;
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        if(isset($_POST['schedule']) && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id']);

        /* Check for any errors */
        $fields = ['location_url'];

        /* Check for any errors */
        foreach($_POST as $key => $value) {
            if(empty($value) && in_array($key, $fields) == true) {
                Response::json($this->language->global->error_message->empty_fields, 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        if(!$link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'user_id' => $this->user->user_id])) {
            die();
        }

        if($_POST['url'] == $link->url) {
            $url = $link->url;
        } else {
            $url = $_POST['url'] ? $_POST['url'] : string_generate(10);

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url, 'domain_id' => $domain_id])) {
                $url = string_generate(10);
            }
        }

        $stmt = Database::$database->prepare("UPDATE `links` SET `domain_id` = ?, `url` = ?, `location_url` = ?, `start_date` = ?, `end_date` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ssssss', $domain_id, $url, $_POST['location_url'], $_POST['start_date'], $_POST['end_date'], $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json($this->language->link->success_message->settings_updated, 'success');
    }

    private function delete() {

        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Check for possible errors */
        if(!$link = Database::get(['project_id', 'biolink_id', 'type', 'subtype', 'settings'], 'links', ['user_id' => $this->user->user_id, 'link_id' => $_POST['link_id']])) {
            die();
        }

        if(empty($errors)) {

            if( $link->subtype === 'pdf' ) {

                $link->settings = json_decode($link->settings);
                $file_name = $link->settings->pdf;
                if( !empty($link->settings->pdf) && file_exists(UPLOADS_PATH . 'pdfs/'. $file_name) ) {
                    unlink(UPLOADS_PATH . 'pdfs/'. $file_name);
                }
            }
            /* Delete from database */
            $stmt = Database::$database->prepare("DELETE FROM `links` WHERE `link_id` = ? OR `biolink_id` = ? AND `user_id` = ?");
            $stmt->bind_param('sss', $_POST['link_id'], $_POST['link_id'], $this->user->user_id);
            $stmt->execute();
            $stmt->close();

            /* Determine where to redirect the user */
            if($link->type == 'biolink' && $link->subtype != 'base') {
                $redirect_url = url('link/' . $link->biolink_id . '?tab=links');
            } else {
                $redirect_url = url('project/' . $link->project_id);
            }

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success', ['url' => $redirect_url]);
        }
    }

    private function delete_shopify_tag() {

        if(isset($_POST)) {

            $tag_number = 0;
            $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $_POST['link_id']]);
            $tagged_products = Database::simple_get('tagged_products', 'shopify_tokens', ['shopify_link_id' => $_POST['link_id'], 'shop' => $_POST['shopify_product_vendor']]);
            $tagged_products = json_decode($tagged_products);
            $new_tagged_products = array();
            foreach($tagged_products as $key => $tagged_product_item) {
                $tagged_product_obj = get_object_vars($tagged_product_item);
                $tagged_product = $tagged_product_obj[key($tagged_product_obj)];

                if(!((key($tagged_product_obj) == $_POST['post_id']) && ($tagged_product->shopify_product_variant_id == $_POST['variant_id']))) {

                    $tag_number++; 
                    $updated_tagged_product = [
                        key($tagged_product_obj) => [
                            'shopify_product_id'            => $tagged_product->shopify_product_id,
                            'shopify_product_vendor'        => $tagged_product->shopify_product_vendor,
                            'shopify_product_image_url'     => $tagged_product->shopify_product_image_url,
                            'shopify_product_title'         => $tagged_product->shopify_product_title,
                            'shopify_product_price'         => $tagged_product->shopify_product_price,
                            'shopify_product_option_key'    => $tagged_product->shopify_product_option_key,
                            'shopify_product_option_value'  => $tagged_product->shopify_product_option_value,
                            'shopify_product_variant_id'    => $tagged_product->shopify_product_variant_id,
                            'tag_number'                    => $tag_number,
                            'tag_position'                  => $tagged_product->tag_position,
                            'shopify_product_url'           => $tagged_product->shopify_product_url,
                            'num_add_to_cart_products'      => $tagged_product->num_add_to_cart_products
                        ]
                    ];

                    array_push($new_tagged_products, $updated_tagged_product);
                }
            }

            $new_tagged_products = json_encode($new_tagged_products);

            $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `shop` = ? AND `shopify_link_id` = ?");
            $stmt->bind_param('sss', $new_tagged_products, $_POST['shopify_product_vendor'], $_POST['link_id']);
            $stmt->execute();
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
    
            Response::json('', 'success', ['url' => url('link/' . $biolink_id . '?tab=links')]);
        }

    }

    private function mail() {
        
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['email'] = mb_substr(trim(Database::clean_string($_POST['email'])), 0, 320);

        /* Get the link data */
        $link = Database::get('*', 'links', ['link_id' => $_POST['link_id'], 'type' => 'biolink', 'subtype' => 'mail']);

        if($link) {
            $link->settings = json_decode($link->settings);

            /* Send the webhook */
            if($link->settings->webhook_url) {

                $body = \Unirest\Request\Body::form(['email' => $_POST['email']]);

                $response = \Unirest\Request::post($link->settings->webhook_url, [], $body);

            }

            /* Send the email to mailchimp */
            if($link->settings->mailchimp_api && $link->settings->mailchimp_api_list) {

                /* Check the mailchimp api list and get data */
                $explode = explode('-', $link->settings->mailchimp_api);

                if(count($explode) < 2) {
                    die();
                }

                $dc = $explode[1];
                $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $link->settings->mailchimp_api_list . '/members';

                /* Try to subscribe the user to mailchimp list */
                \Unirest\Request::auth('altum', $link->settings->mailchimp_api);

                $body = \Unirest\Request\Body::json([
                    'email_address' => $_POST['email'],
                    'status' => 'subscribed',
                ]);

                \Unirest\Request::post(
                    $url,
                    [],
                    $body
                );

            }

            Response::json($link->settings->success_text, 'success');
        }
    }

    private function update_user_repeat() {

        Database::update(
            'users',
            [
                'user_repeat' => 0,
            ],
            [
                'user_id' => $this->user->user_id
            ]
        );
        Response::json($this->language->link->success_message->settings_updated, 'success');
    }
    /* Function to bundle together all the checks of a custom url */
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

    /* Function to bundle together all the checks of an url */
    private function check_location_url($url) {

        if(empty(trim($url))) {
            Response::json($this->language->global->error_message->empty_fields, 'error');
        }

        $url_details = parse_url($url);

        if(!isset($url_details['scheme'])) {
            Response::json($this->language->link->error_message->invalid_location_url, 'error');
        }

        if(!$this->user->package_settings->deep_links && !in_array($url_details['scheme'], ['http', 'https'])) {
            Response::json($this->language->link->error_message->invalid_location_url, 'error');
        }

        /* Make sure the domain is not blacklisted */
        if(in_array(get_domain($url), explode(',', $this->settings->links->blacklisted_domains))) {
            Response::json($this->language->link->error_message->blacklisted_domain, 'error');
        }

        /* Check the url with phishtank to make sure its not a phishing site */
        if($this->settings->links->phishtank_is_enabled) {
            if(phishtank_check($url, $this->settings->links->phishtank_api_key)) {
                Response::json($this->language->link->error_message->blacklisted_location_url, 'error');
            }
        }

        /* Check the url with google safe browsing to make sure it is a safe website */
        if($this->settings->links->google_safe_browsing_is_enabled) {
            if(google_safe_browsing_check($url, $this->settings->links->google_safe_browsing_api_key)) {
                Response::json($this->language->link->error_message->blacklisted_location_url, 'error');
            }
        }
    }

    private function check_youtube_location_url($url) {

        $parsed = parse_url(rtrim($url, '/'));
        if ((isset($parsed['path']) && preg_match('/^\/channel\/(([^\/])+?)$/', $parsed['path'], $matches)) || (isset($parsed['path']) && preg_match('/^\/c\/(([^\/])+?)$/', $parsed['path'], $matches))) {
            Response::json($this->language->link->error_message->invalid_youtube_location_url, 'error');
        }
    }
    /* Check if custom domain is set and return the proper value */
    private function get_domain_id($posted_domain_id) {

        $domain_id = 0;

        if(isset($posted_domain_id)) {
            $domain_id = (int) Database::clean_string($posted_domain_id);

            /* Make sure the user has access to global additional domains */
            if(!empty($this->user->package_settings->additional_global_domains) || !empty($this->user->package_settings->no_ads) ) {
                $domain_id = $this->database->query("SELECT `domain_id` FROM `domains` WHERE `domain_id` = {$domain_id} AND (`user_id` = {$this->user->user_id} OR `type` = 1) ORDER BY `order` ASC")->fetch_object()->domain_id ?? 0;
            } else {
                $domain_id = $this->database->query("SELECT `domain_id` FROM `domains` WHERE `domain_id` = {$domain_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->domain_id ?? 0;
            }

        }

        return $domain_id;
    }
}
