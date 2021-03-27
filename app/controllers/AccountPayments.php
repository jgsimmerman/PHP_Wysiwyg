<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Models\Package;
use Altum\Models\User;
use Altum\Routing\Router;

class AccountPayments extends Controller {

    public function index() {

        Authentication::guard();

        /* Get the transactions if any  */
        $payments_result = Database::$database->query("SELECT `payments`.*, `packages`.`name` AS `package_name` FROM `payments` LEFT JOIN `packages` ON `payments`.`package_id` = `packages`.`package_id` WHERE `user_id` = {$this->user->user_id} ORDER BY `id` DESC");

        /* Establish the account header view */
        $menu = new \Altum\Views\View('partials/account_header', (array) $this);
        $this->add_view_content('account_header', $menu->run());

        /* Prepare the View */
        $data = ['payments_result' => $payments_result];

        $view = new \Altum\Views\View('account-payments/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


}
