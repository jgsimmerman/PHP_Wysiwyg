<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Models\Plan;
use Altum\Middlewares\Authentication;
use Altum\Response;

class AdminPayments extends Controller {

    public function index() {

        Authentication::guard('admin');

        /* Main View */
        $view = new \Altum\Views\View('admin/payments/index', (array) $this);

        $this->add_view_content('content', $view->run());

    }


    public function read() {

        Authentication::guard('admin');

        $datatable = new \Altum\DataTable();
        $datatable->set_accepted_columns(['type', 'processor', 'email', 'name', 'amount', 'date', 'user_email']);
        $datatable->process($_POST);

        $result = Database::$database->query("
            SELECT 
                `payments` . *, `users` . `user_id`, `users` . `type` AS `user_type`, `users` . `email` AS `user_email`,
                (SELECT COUNT(*) FROM `payments`) AS `total_before_filter`,
                (SELECT COUNT(*) FROM `payments` LEFT JOIN `users` ON `payments` . `user_id` = `users` . `user_id` WHERE `users` . `email` LIKE '%{$datatable->get_search()}%' OR `users` . `name` LIKE '%{$datatable->get_search()}%' OR `payments` . `name` LIKE '%{$datatable->get_search()}%' OR `payments` . `email` LIKE '%{$datatable->get_search()}%') AS `total_after_filter`
            FROM 
                `payments`
            LEFT JOIN
                `users` ON `payments` . `user_id` = `users` . `user_id`
            WHERE 
                `users` . `email` LIKE '%{$datatable->get_search()}%' 
                OR `users` . `name` LIKE '%{$datatable->get_search()}%'
                OR `payments` . `name` LIKE '%{$datatable->get_search()}%'
                OR `payments` . `email` LIKE '%{$datatable->get_search()}%'
            ORDER BY 
                " . $datatable->get_order() . "
            LIMIT
                {$datatable->get_start()}, {$datatable->get_length()}	
        ");

        $total_before_filter = 0;
        $total_after_filter = 0;

        $data = [];

        while($row = $result->fetch_object()):

            $user_email_extra = $row->user_type > 0 ? ' <span class="badge badge-pill badge-primary">' . $this->language->admin_users->display->admin . '</span> ' : null;
            $row->user_email = $user_email_extra . '<a href="' . url('admin/user-update/' . $row->user_id) . '"> ' . $row->user_email . '</a>';

            $row->type = $row->type == 'one-time' ? '<span data-toggle="tooltip" title="' . $row->type . '"><i class="fa fa-fw fa-hand-holding-usd"></i></span>' : '<span data-toggle="tooltip" title="' . $row->type . '"><i class="fa fa-fw fa-sync-alt"></i></span>';

            switch($row->processor) {
                case 'STRIPE':
                    $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->stripe .'"><i class="fab fa-stripe icon-stripe"></i></span>';
                    break;

                case 'PAYPAL':
                    $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->paypal .'"><i class="fab fa-paypal icon-paypal"></i></span>';
                    break;
            }


            $row->date = '<span data-toggle="tooltip" title="' . \Altum\Date::get($row->date, 1) . '">' . \Altum\Date::get($row->date, 2) . '</span>';
            $row->amount = '<span class="text-success">' .  $row->amount . '</span> ' . $row->currency;

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

}
