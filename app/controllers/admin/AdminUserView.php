<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Models\Package;
use Altum\Middlewares\Authentication;

class AdminUserView extends Controller {

    public function index() {

        Authentication::guard('admin');

        $user_id = (isset($this->params[0])) ? $this->params[0] : false;

        /* Check if user exists */
        if(!$user = Database::get('*', 'users', ['user_id' => $user_id])) {
            $_SESSION['error'][] = $this->language->admin_user_update->error_message->invalid_account;
            redirect('admin/users');
        }

        /* Get the payments made from this account */
        $user_payments_result = $this->settings->payment->is_enabled ? Database::$database->query("SELECT * FROM `payments` WHERE `user_id` = {$user_id} ORDER BY `id` DESC") : null;

        /* Get last X logs */
        $user_logs_result = Database::$database->query("SELECT * FROM `users_logs` WHERE `user_id` = {$user_id} ORDER BY `id` DESC LIMIT 15");

        /* Get the current package details */
        $user->package = (new Package(['settings' => $this->settings]))->get_package_by_id($user->package_id);

        /* Check if its a custom package */
        if($user->package_id == 'custom') {
            $user->package->settings = $user->package_settings;
        }

        /* Login Modal */
        $view = new \Altum\Views\View('admin/users/user_login_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Main View */
        $data = [
            'user'                  => $user,
            'user_payments_result'  => $user_payments_result,
            'user_logs_result'      => $user_logs_result
        ];

        $view = new \Altum\Views\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
