<?php defined('ALTUMCODE') || die() ?>

<header class="header pb-0">
    <div class="container">
        <?= $this->views['account_header'] ?>
    </div>
</header>

<?php require THEME_PATH . 'views/partials/ads_header.php' ?>

<section class="container pt-5">

    <?php display_notifications() ?>

    <?php if($data->payments_result->num_rows): ?>
        <h2 class="h3"><?= $this->language->account_payments->header ?></h2>
        <p class="text-muted"><?= $this->language->account_payments->subheader ?></p>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= $this->language->account_payments->payments->nr ?></th>
                    <th><?= $this->language->account_payments->payments->type ?></th>
                    <th></th>
                    <th><?= $this->language->account_payments->payments->package_id ?></th>
                    <th><?= $this->language->account_payments->payments->email ?></th>
                    <th><?= $this->language->account_payments->payments->name ?></th>
                    <th><?= $this->language->account_payments->payments->amount ?></th>
                    <th><?= $this->language->account_payments->payments->date ?></th>
                    <?php if($this->settings->business->invoice_is_enabled): ?>
                    <th></th>
                    <?php endif ?>
                </tr>
                </thead>
                <tbody>

                <?php $nr = 1; while($row = $data->payments_result->fetch_object()): ?>

                    <?php
                    switch($row->processor) {
                        case 'STRIPE':
                            $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->stripe .'"><i class="fab fa-fw fa-fw fa-stripe icon-stripe"></i></span>';
                            break;

                        case 'PAYPAL':
                            $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->paypal .'"><i class="fab fa-fw fa-fw fa-paypal icon-paypal"></i></span>';
                            break;
                    }
                    ?>

                    <tr>
                        <td class="text-muted"><?= $nr++ ?></td>
                        <td><?= $row->type == 'ONE-TIME' ? '<span data-toggle="tooltip" title="' . $row->type . '"><i class="fa fa-fw fa-hand-holding-usd"></i></span>' : '<span data-toggle="tooltip" title="' . $row->type . '"><i class="fa fa-fw fa-sync-alt"></i></span>' ?></td>
                        <td><?= $row->processor ?></td>
                        <td><?= $row->package_name ?></td>
                        <td><?= $row->email ?></td>
                        <td><?= $row->name ?></td>
                        <td><span class="text-success"><?= $row->amount ?></span> <?= $row->currency ?></td>
                        <td class="text-muted"><span data-toggle="tooltip" title="<?= \Altum\Date::get($row->date, 1) ?>"><?= \Altum\Date::get($row->date, 2) ?></span></td>
                        <?php if($this->settings->business->invoice_is_enabled): ?>
                        <td>
                            <a href="<?= url('invoice/' . $row->id) ?>">
                                <span data-toggle="tooltip" title="<?= $this->language->account_payments->payments->invoice ?>"><i class="fa fa-fw fa-file-invoice"></i></span>
                            </a>
                        </td>
                        <?php endif ?>
                    </tr>
                <?php endwhile ?>

                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column align-items-center justify-content-center">
            <img src="<?= SITE_URL . ASSETS_URL_PATH . 'images/no_data.svg' ?>" class="col-10 col-md-6 col-lg-4 mb-3" alt="<?= $this->language->account_payments->payments->no_data ?>" />
            <h2 class="h4 text-muted"><?= $this->language->account_payments->payments->no_data ?></h2>
        </div>
    <?php endif ?>
</section>
