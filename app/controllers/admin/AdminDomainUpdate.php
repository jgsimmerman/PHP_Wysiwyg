<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Csrf;
use Altum\Models\Package;
use Altum\Middlewares\Authentication;

class AdminDomainUpdate extends Controller {

    public function index() {

        Authentication::guard('admin');

        $domain_id = (isset($this->params[0])) ? $this->params[0] : false;

        /* Check if user exists */
        if(!$domain = Database::get('*', 'domains', ['domain_id' => $domain_id])) {
            redirect('admin/domains');
        }

        if(!empty($_POST)) {

            /* Clean some posted variables */
            $_POST['scheme'] = isset($_POST['scheme']) && in_array($_POST['scheme'], ['http://', 'https://']) ? Database::clean_string($_POST['scheme']) : 'https://';
            $_POST['host'] = trim(Database::clean_string($_POST['host']));
            $_POST['custom_index_url'] = trim(Database::clean_string($_POST['custom_index_url']));
            $free_status = $_POST['free'] ? 1 : 0;
            $small_business_status = $_POST['small_business'] ? 1 : 0;
            $agency_status = $_POST['agency'] ? 1 : 0;

            /* Must have fields */
            $fields = ['scheme', 'host'];

            /* Check for any errors */
            foreach($_POST as $key=>$value) {
                if(empty($value) && in_array($key, $fields) == true) {
                    $_SESSION['error'][] = $this->language->global->error_message->empty_fields;
                    break 1;
                }
            }

            if(!Csrf::check()) {
                $_SESSION['error'][] = $this->language->global->error_message->invalid_csrf_token;
            }

            if(empty($_SESSION['error'])) {

                /* Update the row of the database */
                $stmt = Database::$database->prepare("UPDATE `domains` SET `scheme` = ?, `host` = ?, `custom_index_url` = ?, `package_free` = ?, `package_small` = ?, `package_agency` = ? WHERE `domain_id` = ?");
                $stmt->bind_param('sssssss', $_POST['scheme'], $_POST['host'], $_POST['custom_index_url'] , $free_status, $small_business_status, $agency_status, $domain->domain_id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['success'][] = $this->language->global->success_message->basic;

                redirect('admin/domain-update/' . $domain->domain_id);
            }

        }

        /* Main View */
        $data = ['domain' => $domain];

        $view = new \Altum\Views\View('admin/domain-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
