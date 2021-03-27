<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Date;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Response;

class ProjectAjax extends Controller {

    public function index() {

        Authentication::guard();

        if(!empty($_POST) && (Csrf::check('token') || Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Create */
                case 'create': $this->create(); break;

                /* Update */
                case 'update': $this->update(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

                /* Transfer */
                case 'transfer': $this->transfer(); break;

                /* Clone */
                case 'clone': $this->clone(); break;

            }

        }

        die();
    }

    private function create() {

        $_POST['name'] = trim(Database::clean_string($_POST['name']));

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            $errors[] = $this->language->global->error_message->empty_fields;
        }

        /* Make sure that the user didn't exceed the limit */
        $user_total_projects = Database::$database->query("SELECT COUNT(*) AS `total` FROM `projects` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->package_settings->projects_limit != -1 && $user_total_projects >= $this->user->package_settings->projects_limit) {
            Response::json($this->language->project_create_modal->error_message->projects_limit, 'error');
        }


        if(empty($errors)) {

            /* Insert to database */
            $stmt = Database::$database->prepare("INSERT INTO `projects` (`user_id`, `name`, `date`) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $this->user->user_id, $_POST['name'], Date::$date);
            $stmt->execute();
            $project_id = $stmt->insert_id;
            $stmt->close();

            Response::json($this->language->project_create_modal->success_message->created, 'success', ['url' => url('project/' . $project_id)]);

        }
    }

    private function update() {
        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            $errors[] = $this->language->global->error_message->empty_fields;
        }

        if(empty($errors)) {

            /* Insert to database */
            $stmt = Database::$database->prepare("UPDATE `projects` SET `name` = ? WHERE `project_id` = ? AND `user_id` = ?");
            $stmt->bind_param('sss', $_POST['name'], $_POST['project_id'], $this->user->user_id);
            $stmt->execute();
            $stmt->close();

            Response::json($this->language->project_update_modal->success_message->updated, 'success');

        }
    }

    private function delete() {
        $_POST['project_id'] = (int) $_POST['project_id'];

        /* Check for possible errors */
        if(!Database::exists('project_id', 'projects', ['project_id' => $_POST['project_id']])) {
            $errors[] = true;
        }

        if(empty($errors)) {

            /* Delete from database */
            $stmt = Database::$database->prepare("DELETE FROM `projects` WHERE `project_id` = ? AND `user_id` = ?");
            $stmt->bind_param('ss', $_POST['project_id'], $this->user->user_id);
            $stmt->execute();
            $stmt->close();

            Response::json($this->language->project_delete_modal->success_message, 'success');

        }
    }

    private function transfer() {
        
        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['project_name'] = trim(Database::clean_string($_POST['project_name']));
        $_POST['project_url'] = trim(Database::clean_string($_POST['project_url']));
        $_POST['eamil'] = $_POST['email'];
        $project_exist_flag = false;

        if($_POST['eamil']) {

            if(!Database::exists('user_id', 'users', ['email' => $_POST['eamil']])) {
                Response::json($this->language->project_link_transfer_modal->error_message->user_not_found, 'error');
            } else {

                $target_user_id = Database::simple_get('user_id', 'users', ['email' => $_POST['email']]);
                
                if($target_user_id === $this->user->user_id) {
                    Response::json($this->language->project_link_transfer_modal->error_message->same_user, 'error');
                } else {

                    if(!Database::exists('project_id', 'projects', ['user_id' => $target_user_id, 'name' => $_POST['project_name']])) {

                        $project_exist_flag = false;
                        $stmt = Database::$database->prepare("INSERT INTO `projects` (`user_id`, `name`, `date`) VALUES (?, ?, ?)");
                        $stmt->bind_param('sss', $target_user_id, $_POST['project_name'], Date::$date);
                        $stmt->execute();
                        $project_new_id = $stmt->insert_id;
                        $stmt->close();
    
                    } else {
    
                        $project_exist_flag = true;
    
                    }
                    $url = $_POST['project_url'].'-transfer'.$target_user_id;
                    if(Database::exists('link_id', 'links', ['user_id' => $target_user_id, 'url' => $url])) {
    
                        Response::json($this->language->project_link_transfer_modal->error_message->project_link_exist, 'error');
                    } else {
    
                        /* Get the link data */
                        $biolink = Database::get('*', 'links', ['user_id' => $this->user->user_id, 'type' => 'biolink', 'subtype' => 'base', 'url' => $_POST['project_url']]);
                        $link = Database::get('*', 'links', ['user_id' => $this->user->user_id, 'type' => 'link', 'url' => $_POST['project_url']]);
                        
                        if($project_exist_flag) {
    
                            $project_id = Database::simple_get('project_id', 'projects', ['user_id' => $target_user_id, 'name' => $_POST['project_name']]);
                            $project_id = (int) $project_id;
    
       
                        } else {
                            $project_id = $project_new_id;
                        }
                        if($biolink) {
                            
                            $biolink->settings = json_decode($biolink->settings);
                            $type = 'biolink';
                            $subtype = 'base';
                            $url = $_POST['project_url'].'-transfer'.$target_user_id;
                            $link_id = $biolink->link_id;
    
                            $settings = json_encode([
                                'title' => $biolink->settings->title,
                                'description' => $biolink->settings->description,
                                'display_verified' => $biolink->settings->display_verified,
                                'image' => $biolink->settings->image,
                                'background_type' => $biolink->settings->background_type,
                                'background' => $biolink->settings->background,
                                'text_color' => $biolink->settings->text_color,
                                'socials_color' => $biolink->settings->socials_color,
                                'google_analytics' => $biolink->settings->google_analytics,
                                'facebook_pixel' => $biolink->settings->facebook_pixel,
                                'display_branding' => $biolink->settings->display_branding,
                                'branding' => ['name' => $biolink->settings->branding->name, 'url' => $biolink->settings->branding->url],
                                'seo' => ['title' => $biolink->settings->seo->title, 'meta_description' => $biolink->settings->seo->meta_description],
                                'utm' => ['medium' => $biolink->settings->utm->medium, 'source' => $biolink->settings->utm->source],
                                'socials' => [
                                    'email' => $biolink->settings->socials->email, 
                                    'tel' => $biolink->settings->socials->tel, 
                                    'whatsapp' => $biolink->settings->socials->whatsapp,
                                    'facebook' => $biolink->settings->socials->facebook,
                                    'facebook-messenger' => $biolink->settings->socials->facebook_messenger,
                                    'instagram' => $biolink->settings->socials->instagram,
                                    'twitter' => $biolink->settings->socials->twitter,
                                    'tiktok' => $biolink->settings->socials->tiktok,
                                    'youtube' => $biolink->settings->socials->youtube,
                                    'soundcloud' => $biolink->settings->socials->soundcloud,
                                    'linkedin' => $biolink->settings->socials->linkedin,
                                    'spotify' => $biolink->settings->socials->spotify,
                                    'pinterest' => $biolink->settings->socials->pinterest
                                ],
                            ]);
                            
                            $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('ssssssssss', $project_id, $target_user_id, $biolink->domain_id, $type, $subtype, $url, $settings,$biolink->start_date, $biolink->end_date, \Altum\Date::$date);
                            $stmt->execute();
                            $link_new_id = $stmt->insert_id;
                            $stmt->close();
                
    
                            $resultArr = [];
                            $datatable = new \Altum\DataTable();
                            $datatable->set_accepted_columns(['biolink_id', 'domain_id', 'subtype', 'url', 'location_url', 'settings', 'start_date', 'end_date']);
                            $datatable->process($_POST);
                            $result = Database::$database->query("SELECT `biolink_id`, `domain_id`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date` FROM `links` WHERE `biolink_id` = {$link_id}");
    
                            while($row = $result -> fetch_object()):
    
                                $row->settings = json_decode($row->settings);
                                $url = string_generate(10);
                                $sub_settings = json_encode([
                                    'name' => $row->settings->name,
                                    'text_color' => $row->settings->text_color,
                                    'background_color' => $row->settings->background_color,
                                    'outline' => $row->settings->outline,
                                    'border_radius' => $row->settings->border_radius,
                                    'animation' => $row->settings->animation,
                                    'icon' => $row->settings->icon
                                ]);
    
                                $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `biolink_id`, `domain_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->bind_param('ssssssssssss', $project_id, $target_user_id, $link_new_id, $row->domain_id, $type, $row->subtype, $url, $row->location_url, $sub_settings, $row->start_date, $row->end_date, \Altum\Date::$date);
                                $stmt->execute();
                                $stmt->close();
                            endwhile;
    
    
                            // Response::json('', 'success', ['url' => url('link/' . $biolink->biolink_id . '?tab=links')]);
                            Response::json($this->language->project_link_transfer_modal->success_message->project_transfered, 'success');
                
                        }
    
                        if($link) {
                            
                            $link->settings = json_decode($link->settings);
                            $type = 'link';
                            $subtype = '';
                            $url = $_POST['project_url'].'-transfer'.$target_user_id;
    
                            $settings = json_encode([
                                'title' => $link->settings->title,
                                'description' => $link->settings->description,
                                'display_verified' => $link->settings->display_verified,
                                'image' => $link->settings->image,
                                'background_type' => $link->settings->background_type,
                                'background' => $link->settings->background,
                                'text_color' => $link->settings->text_color,
                                'socials_color' => $link->settings->socials_color,
                                'google_analytics' => $link->settings->google_analytics,
                                'facebook_pixel' => $link->settings->facebook_pixel,
                                'display_branding' => $link->settings->display_branding,
                                'branding' => ['name' => $link->settings->branding->name, 'url' => $link->settings->branding->url],
                                'seo' => ['title' => $link->settings->seo->title, 'meta_description' => $link->settings->seo->meta_description],
                                'utm' => ['medium' => $link->settings->utm->medium, 'source' => $link->settings->utm->source],
                                'socials' => [
                                    'email' => $link->settings->socials->email, 
                                    'tel' => $link->settings->socials->tel, 
                                    'whatsapp' => $link->settings->socials->whatsapp,
                                    'facebook' => $link->settings->socials->facebook,
                                    'facebook-messenger' => $link->settings->socials->facebook_messenger,
                                    'instagram' => $link->settings->socials->instagram,
                                    'twitter' => $link->settings->socials->twitter,
                                    'tiktok' => $link->settings->socials->tiktok,
                                    'youtube' => $link->settings->socials->youtube,
                                    'soundcloud' => $link->settings->socials->soundcloud,
                                    'linkedin' => $link->settings->socials->linkedin,
                                    'spotify' => $link->settings->socials->spotify,
                                    'pinterest' => $link->settings->socials->pinterest
                                ],
                            ]);
                
                            $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('sssssssssss', $project_id, $target_user_id, $link->domain_id, $type, $subtype, $url, $link->location_url, $settings, $link->start_date, $link->end_date, \Altum\Date::$date);
                            $stmt->execute();
                            $stmt->close();
                
                            /* Clear the cache */
                            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
                
                            // Response::json('', 'success', ['url' => url('link/' . $biolink->biolink_id . '?tab=links')]);
                            Response::json($this->language->project_link_transfer_modal->success_message->project_transfered, 'success');
                
                        }
    
                    }

                }


            }
        } else {

            Response::json($this->language->project_link_transfer_modal->error_message->email_required, 'error');

        }

    }

    private function clone() {

        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['domainid'] = (int) $_POST['domainid'];
        $_POST['project_name'] = trim(Database::clean_string($_POST['project_name']));
        $_POST['project_url'] = trim(Database::clean_string($_POST['project_url']));
        $_POST['url'] = trim(Database::clean_string($_POST['url']));

        if($_POST['url']) {


            if(Database::exists('link_id', 'links', ['user_id' => $this->user->user_id, 'project_id' => $_POST['project_id'], 'url' => $_POST['url']])) {

                Response::json($this->language->project_link_clone_modal->error_message->url_exist, 'error');

            } else {

                /* Get the link data */
                $biolink = Database::get('*', 'links', ['user_id' => $this->user->user_id, 'project_id' => $_POST['project_id'], 'type' => 'biolink', 'subtype' => 'base', 'url' => $_POST['project_url']]);
                $link = Database::get('*', 'links', ['user_id' => $this->user->user_id, 'project_id' => $_POST['project_id'], 'type' => 'link', 'url' => $_POST['project_url']]);
                
                if($biolink) {
                    
                    $biolink->settings = json_decode($biolink->settings);
                    $type = 'biolink';
                    $subtype = 'base';
                    $url = $_POST['url'];
                    $link_id = $biolink->link_id;

                    $settings = json_encode([
                        'title' => $biolink->settings->title,
                        'description' => $biolink->settings->description,
                        'display_verified' => $biolink->settings->display_verified,
                        'image' => $biolink->settings->image,
                        'background_type' => $biolink->settings->background_type,
                        'background' => $biolink->settings->background,
                        'text_color' => $biolink->settings->text_color,
                        'socials_color' => $biolink->settings->socials_color,
                        'google_analytics' => $biolink->settings->google_analytics,
                        'facebook_pixel' => $biolink->settings->facebook_pixel,
                        'display_branding' => $biolink->settings->display_branding,
                        'branding' => ['name' => $biolink->settings->branding->name, 'url' => $biolink->settings->branding->url],
                        'seo' => ['title' => $biolink->settings->seo->title, 'meta_description' => $biolink->settings->seo->meta_description],
                        'utm' => ['medium' => $biolink->settings->utm->medium, 'source' => $biolink->settings->utm->source],
                        'socials' => [
                            'email' => $biolink->settings->socials->email, 
                            'tel' => $biolink->settings->socials->tel, 
                            'whatsapp' => $biolink->settings->socials->whatsapp,
                            'facebook' => $biolink->settings->socials->facebook,
                            'facebook-messenger' => $biolink->settings->socials->facebook_messenger,
                            'instagram' => $biolink->settings->socials->instagram,
                            'twitter' => $biolink->settings->socials->twitter,
                            'tiktok' => $biolink->settings->socials->tiktok,
                            'youtube' => $biolink->settings->socials->youtube,
                            'soundcloud' => $biolink->settings->socials->soundcloud,
                            'linkedin' => $biolink->settings->socials->linkedin,
                            'spotify' => $biolink->settings->socials->spotify,
                            'pinterest' => $biolink->settings->socials->pinterest
                        ],
                    ]);
                    
                    $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssssssssss', $_POST['project_id'], $this->user->user_id, $_POST['domainid'], $type, $subtype, $url, $settings,$biolink->start_date, $biolink->end_date, \Altum\Date::$date);
                    $stmt->execute();
                    $link_new_id = $stmt->insert_id;
                    $stmt->close();
        

                    $resultArr = [];
                    $datatable = new \Altum\DataTable();
                    $datatable->set_accepted_columns(['biolink_id', 'domain_id', 'subtype', 'url', 'location_url', 'settings', 'start_date', 'end_date']);
                    $datatable->process($_POST);
                    $result = Database::$database->query("SELECT `biolink_id`, `domain_id`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date` FROM `links` WHERE `biolink_id` = {$link_id}");

                    while($row = $result -> fetch_object()):

                        $row->settings = json_decode($row->settings);
                        $url = string_generate(10);
                        $sub_settings = json_encode([
                            'name' => $row->settings->name,
                            'text_color' => $row->settings->text_color,
                            'background_color' => $row->settings->background_color,
                            'outline' => $row->settings->outline,
                            'border_radius' => $row->settings->border_radius,
                            'animation' => $row->settings->animation,
                            'icon' => $row->settings->icon
                        ]);

                        $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `biolink_id`, `domain_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('ssssssssssss', $_POST['project_id'], $this->user->user_id, $link_new_id, $_POST['domainid'], $type, $row->subtype, $url, $row->location_url, $sub_settings, $row->start_date, $row->end_date, \Altum\Date::$date);
                        $stmt->execute();
                        $stmt->close();
                    endwhile;


                    // Response::json('', 'success', ['url' => url('link/' . $biolink->biolink_id . '?tab=links')]);
                    Response::json($this->language->project_link_clone_modal->success_message->project_cloned, 'success');
        
                }

                if($link) {
                    
                    $link->settings = json_decode($link->settings);
                    $type = 'link';
                    $subtype = '';
                    $url = $_POST['url'];

                    $settings = json_encode([
                        'title' => $link->settings->title,
                        'description' => $link->settings->description,
                        'display_verified' => $link->settings->display_verified,
                        'image' => $link->settings->image,
                        'background_type' => $link->settings->background_type,
                        'background' => $link->settings->background,
                        'text_color' => $link->settings->text_color,
                        'socials_color' => $link->settings->socials_color,
                        'google_analytics' => $link->settings->google_analytics,
                        'facebook_pixel' => $link->settings->facebook_pixel,
                        'display_branding' => $link->settings->display_branding,
                        'branding' => ['name' => $link->settings->branding->name, 'url' => $link->settings->branding->url],
                        'seo' => ['title' => $link->settings->seo->title, 'meta_description' => $link->settings->seo->meta_description],
                        'utm' => ['medium' => $link->settings->utm->medium, 'source' => $link->settings->utm->source],
                        'socials' => [
                            'email' => $link->settings->socials->email, 
                            'tel' => $link->settings->socials->tel, 
                            'whatsapp' => $link->settings->socials->whatsapp,
                            'facebook' => $link->settings->socials->facebook,
                            'facebook-messenger' => $link->settings->socials->facebook_messenger,
                            'instagram' => $link->settings->socials->instagram,
                            'twitter' => $link->settings->socials->twitter,
                            'tiktok' => $link->settings->socials->tiktok,
                            'youtube' => $link->settings->socials->youtube,
                            'soundcloud' => $link->settings->socials->soundcloud,
                            'linkedin' => $link->settings->socials->linkedin,
                            'spotify' => $link->settings->socials->spotify,
                            'pinterest' => $link->settings->socials->pinterest
                        ],
                    ]);
        
                    $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `user_id`, `domain_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssssssssss', $_POST['project_id'], $this->user->user_id, $_POST['domainid'], $type, $subtype, $url, $link->location_url, $settings, $link->start_date, $link->end_date, \Altum\Date::$date);
                    $stmt->execute();
                    $stmt->close();
        
                    /* Clear the cache */
                    \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        
                    // Response::json('', 'success', ['url' => url('link/' . $biolink->biolink_id . '?tab=links')]);
                    Response::json($this->language->project_link_clone_modal->success_message->project_cloned, 'success');
        
                }

            }

        } else {

            Response::json($this->language->project_link_clone_modal->error_message->url_required, 'error');

        }

    }
}
