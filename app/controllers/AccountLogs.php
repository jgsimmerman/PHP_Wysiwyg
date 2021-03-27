<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Models\Package;
use Altum\Models\User;
use Altum\Routing\Router;

class AccountLogs extends Controller {

    public function index() {

        Authentication::guard();

        /* Get last X logs */
        $logs_result = Database::$database->query("SELECT * FROM `users_logs` WHERE `user_id` = {$this->user->user_id} ORDER BY `id` DESC LIMIT 15");

        /* Establish the account header view */
        $menu = new \Altum\Views\View('partials/account_header', (array) $this);
        $this->add_view_content('account_header', $menu->run());

        /* Prepare the View */
        $data = ['logs_result' => $logs_result];

        $view = new \Altum\Views\View('account-logs/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


}
