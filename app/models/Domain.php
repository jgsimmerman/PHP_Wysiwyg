<?php

namespace Altum\Models;

use Altum\Database\Database;

class Domain extends Model {

    public function get_domains($user = false) {

        // if($user->package_settings->additional_global_domains) {
        //     $where = "user_id = {$user->user_id} OR `type` = 1";
        // } else {
        //     $where = "user_id = {$user->user_id}";
        // }

        $package_id = Database::simple_get('package_id', 'users', ['user_id' => $user->user_id]);
        
        if($user->user_id == 1) {

            $where = "user_id = {$user->user_id}";
        } else {

            switch($package_id) {
    
                case 'free':
                case '1':
                    $where = "user_id = {$user->user_id} OR `package_free` = 1";
                    break;
                case '2':
                    $where = "user_id = {$user->user_id} OR `package_small` = 1";
                    break;
                case '3':
                    $where = "user_id = {$user->user_id} OR `package_agency` = 1";
                    break;
                default:
                    break;
            }

        }
        // $domains_result = Database::$database->query("SELECT * FROM `domains` WHERE `user_id` = {$this->user->user_id} AND `type` = 0 ORDER BY `order` ASC");

        $result = Database::$database->query("SELECT * FROM `domains` WHERE {$where} ORDER BY `order` ASC");
        $data = [];

        while($row = $result->fetch_object()) {

            /* Build the url */
            $row->url = $row->scheme . $row->host . '/';

            $data[] = $row;
        }

        return $data;
    }

    public function get_domain($domain_id) {

        $domain_id = (int) Database::clean_string($domain_id);

        $result = Database::$database->query("SELECT * FROM `domains` WHERE `domain_id` = {$domain_id}");

        if(!$result->num_rows) return false;

        $row = $result->fetch_object();

        /* Build the url */
        $row->url = $row->scheme . $row->host . '/';

        return $row;
    }

}
