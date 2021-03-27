<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Csrf;
use Altum\Middlewares\Authentication;
use Altum\Response;

use Altum\Date;
use Altum\Routing\Router;

class AdminDomains extends Controller {

    public function index() {

        
        Authentication::guard('admin');

        if( Csrf::check('global_token') && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {
                case 'order':
                    $this->orderUpdate();
                    break;
            }
        }
        /* Main View */
        $view = new \Altum\Views\View('admin/domains/index', (array) $this);

        $this->add_view_content('content', $view->run());

    }

    public function read() {

        Authentication::guard('admin');

        $datatable = new \Altum\DataTable();
        $datatable->set_accepted_columns(['domain_id', 'type', 'host', 'date']);
        $datatable->process($_POST);

        $result = Database::$database->query("
            SELECT
                `domains`.*,
                COUNT(`links`.`domain_id`) AS `links`,
                (SELECT COUNT(*) FROM `domains`) AS `total_before_filter`,
                (SELECT COUNT(*) FROM `domains` WHERE `domains`.`host` LIKE '%{$datatable->get_search()}%') AS `total_after_filter`
            FROM
                `domains`
            LEFT JOIN
                `links` ON `domains`.`domain_id` = `links`.`domain_id`
            WHERE 
                `domains`.`host` LIKE '%{$datatable->get_search()}%'
            GROUP BY
                `domain_id`
            ORDER BY
                `order` ASC,
                " . $datatable->get_order() . "
            LIMIT
                {$datatable->get_start()}, {$datatable->get_length()}
        ");

        $total_before_filter = 0;
        $total_after_filter = 0;

        $data = [];

        while($row = $result->fetch_object()):

            /* Type */
            $row->type =
                $row->type == 1 ?
                    '<span class="badge badge-pill badge-success"><i class="fa fa-fw fa-globe mr-1 drag"></i> ' . $this->language->admin_domains->display->type_global . '</span>' :
                    '<span class="badge badge-pill badge-secondary"><i class="fa fa-fw fa-user mr-1 drag"></i> ' . $this->language->admin_domains->display->type_user . '</span>';

            /* host */
            $host_prepend = '<img src="https://www.google.com/s2/favicons?domain=' . $row->host . '" class="img-fluid mr-1" />';
            $row->host = $host_prepend . '<span class="align-middle">' . $row->scheme . $row->host . '</span>';

            /* Links */
            $row->links = '<i class="fa fa-fw fa-link text-muted"></i> ' . nr($row->links);

            $row->date = '<span data-toggle="tooltip" title="' . \Altum\Date::get($row->date, 1) . '">' . \Altum\Date::get($row->date, 2) . '</span>';
            $row->actions = get_admin_options_button('domain', $row->domain_id);
            $row->domain_id= $row->domain_id;

            $data[] = $row;
            $total_before_filter = $row->total_before_filter;
            $total_after_filter = $row->total_after_filter;

        endwhile;

        Response::simple_json([
            'data' => $data,
            'draw' => $datatable->get_draw(),
            'recordsTotal' => $total_before_filter,
            'recordsFiltered' =>  $total_after_filter
        ]);

    }

    public function delete() {

        Authentication::guard();

        $domain_id = (isset($this->params[0])) ? (int) $this->params[0] : false;

        if(!Csrf::check()) {
            $_SESSION['error'][] = $this->language->global->error_message->invalid_csrf_token;
        }

        if(empty($_SESSION['error'])) {

            /* Delete the domain */
            $this->database->query("DELETE FROM `domains` WHERE `domain_id` = {$domain_id}");

            /* Delete all the links using that domain */
            $this->database->query("DELETE FROM `links` WHERE `domain_id` = {$domain_id}");

            redirect('admin/domains');

        }

        die();
    }

    private function orderUpdate_old() {

        if(isset($_POST['domains']) && is_array($_POST['domains'])) {

            $updatedIdArr = [];
            $updatedDomainIdArr = [];
            $originDomainArr = [];
            $originDomainIdArr = [];
            $tobeChangedDomainIDArr = [];
            $tmpDomainIdArr = [];
            $diffDomainIdArr = [];

            foreach($_POST['domains'] as $domain) {

                $domain['domain_id'] = substr($domain['domain_id'], 5);
                $domain['domain_id'] = (int) $domain['domain_id'];
                $domain['order'] = (int) $domain['order'];
                array_push($updatedIdArr, $domain['domain_id']);

            }
            $datatable = new \Altum\DataTable();
            $datatable->set_accepted_columns(['domain_id', 'user_id', 'scheme', 'type', 'host', 'date']);
            $datatable->process($_POST);
            $result = Database::$database->query("SELECT `domain_id`, `user_id` , `scheme`, `host`, `type`, `date` FROM `domains`");

            while($row = $result -> fetch_object()):
                array_push($originDomainArr, $row);
            endwhile;

            for ($index = 0 ; $index < count($updatedIdArr); $index++) {
                if( ($index + 1) !== $updatedIdArr[$index]) {

                    $originDomainId = (int) $originDomainArr[$index]->domain_id;
                    $tobeChangedDomainID = (int) $originDomainArr[$updatedIdArr[$index]-1]->domain_id;
                    $tmpDomainId = 999 + $tobeChangedDomainID;
                    
                    Database::$database->query("UPDATE `domains` SET `domain_id` = {$tmpDomainId} WHERE `domain_id` = {$tobeChangedDomainID}");

                break;

                }
            }

            for ($index = 0 ; $index < count($updatedIdArr); $index++) {
                if( ($index + 1) !== $updatedIdArr[$index]) {
                    array_push($diffDomainIdArr, $updatedIdArr[$index]);
                }
            }

            for( $reIndex = count($diffDomainIdArr) - 1 ; $reIndex > 0; $reIndex--) {
                $oldDomainId = (int) $originDomainArr[$reIndex]->domain_id;
                $newDomainId = 1 + $oldDomainId;
                Database::$database->query("UPDATE `domains` SET `domain_id` = {$newDomainId} WHERE `domain_id` = {$oldDomainId}");
            }

            Database::$database->query("UPDATE `domains` SET `domain_id` = {$originDomainId} WHERE `domain_id` = {$tmpDomainId}");

        }

        /* Clear the cache */

        Response::json('', 'success');

    }

    private function orderUpdate() {

        $updatedIdArr = [];
        $originDomainArr = [];
        $orderedDomainIdArr = [];
        $criterionArr = [];


        if(isset($_POST['domains']) && is_array($_POST['domains'])) {

            $datatable = new \Altum\DataTable();
            $datatable->set_accepted_columns(['domain_id', 'user_id', 'scheme', 'type', 'host', 'date', 'custom_index_url', 'order']);
            $datatable->process($_POST);
            $result = Database::$database->query("SELECT `domain_id`, `user_id` , `scheme`, `host`, `type`, `date`, `custom_index_url`, `order` FROM `domains` ORDER BY `order` ASC");

            while($row = $result -> fetch_object()):
                array_push($originDomainArr, $row);
            endwhile;

            foreach($_POST['domains'] as $domain) {

                $domain['domain_id'] = substr($domain['domain_id'], 5);
                $domain['domain_id'] = (int) $domain['domain_id'];
                array_push($updatedIdArr, $domain['domain_id']);
            }

            for ($num = 0; $num < count($updatedIdArr); $num++ ) {

                $domain_id = (int) $updatedIdArr[$num];
                $order = $num + 1 ;

                /* Update the domain order */
                $stmt = $this->database->prepare("UPDATE `domains` SET `order` = ? WHERE `domain_id` = ? AND `user_id` = ?");
                $stmt->bind_param('sss', $order, $domain_id, $this->user->user_id);
                $stmt->execute();
                $stmt->close();

            }

        }

       
        /* Clear the cache */

        Response::json('', 'success');

    }

    
}
