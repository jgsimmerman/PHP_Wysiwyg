<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Middlewares\Authentication;
use Altum\Models\Package;
use Altum\Routing\Router;

class Dashboard extends Controller {

    public function index() {

        Authentication::guard();
        Authentication::guard('temp');

        /* Create Modal */
        $view = new \Altum\Views\View('project/project_create_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Update Modal */
        $view = new \Altum\Views\View('project/project_update_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Delete Modal */
        $view = new \Altum\Views\View('project/project_delete_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Project link transfer modal*/
        $view = new \Altum\Views\View('project/project_link_transfer_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Project link clone modal*/
        $view = new \Altum\Views\View('project/project_link_clone_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Get the campaigns list for the user */
        $projects_result = Database::$database->query("SELECT * FROM `projects` WHERE `user_id` = {$this->user->user_id} ORDER BY `name`");
        $count = $projects_result->num_rows;

        /* Some statistics for the widgets */
        $links_total = Database::$database->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total;

        /* Get statistics based on the total clicks */
        $links_clicks_total = Database::$database->query("SELECT SUM(`clicks`) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total;

        /* Prepare the View */
        $data = [
            'projects_result'       => $projects_result,
            'links_total'           => $links_total,
            'links_clicks_total'    => $links_clicks_total,
            'count'                 => $count
        ];

        $view = new \Altum\Views\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
