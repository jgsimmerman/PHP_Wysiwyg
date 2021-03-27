<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="h3 mr-3"><i class="fa fa-fw fa-xs fa-user text-primary-900 mr-2"></i> <?= $this->language->admin_user_view->header ?></h1>

        <?= get_admin_options_button('user', $data->user->user_id) ?>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->email ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->email ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->name ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->name ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->status ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->active ? $this->language->admin_user_view->main->status_active : $this->language->admin_user_view->main->status_disabled ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->ip ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->ip ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->country ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->country ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->last_activity ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->last_activity ? $data->user->last_activity : '-' ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->last_user_agent ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->last_user_agent ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->package ?></label>
                    <div>
                        <a href="<?= url('admin/package-update/' . $data->user->package->package_id) ?>"><?= $data->user->package->name ?></a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->package_expiration_date ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->package_expiration_date ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->package_trial_done ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->package_trial_done ? $this->language->global->yes : $this->language->global->no ?>" readonly />
                </div>
            </div>


            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->total_logins ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->total_logins ?>" readonly />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold"><?= $this->language->admin_user_view->main->language ?></label>
                    <input type="text" class="form-control-plaintext" value="<?= $data->user->language ?>" readonly />
                </div>
            </div>
        </div>
    </div>
</div>


<?php if($this->settings->payment->is_enabled): ?>
    <?php if($data->user_payments_result->num_rows): ?>
        <h2 class="h4 mt-5"><?= $this->language->admin_user_view->payments->header ?></h2>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= $this->language->admin_user_view->payments->payment ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php $nr = 1; while($row = $data->user_payments_result->fetch_object()): ?>

                    <?php
                    switch($row->processor) {
                        case 'STRIPE':
                            $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->stripe .'"><i class="fab fa-fw fa-stripe icon-stripe"></i></span>';
                            break;

                        case 'PAYPAL':
                            $row->processor = '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->paypal .'"><i class="fab fa-fw fa-paypal icon-paypal"></i></span>';
                            break;
                    }
                    ?>

                    <tr>
                        <td class="text-muted"><?= $nr++ ?></td>
                        <td>
                            <div class="d-flex flex-column">
                                <?= $row->name ?>
                                <span class="text-muted"><?= $row->email ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <div><span class="text-success"><?= $row->amount ?></span> <?= $row->currency ?></div>
                                <div><small class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->date, 1) ?>"><?= \Altum\Date::get($row->date, 2) ?></small></div>
                            </div>
                        </td>
                        <td><?= $row->type == 'one-time' ? '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->one_time . '"><i class="fa fa-fw fa-hand-holding-usd"></i></span>' : '<span data-toggle="tooltip" title="' . $this->language->admin_payments->table->recurring . '"><i class="fa fa-fw fa-sync-alt"></i></span>' ?></td>
                        <td><?= $row->processor ?></td>
                    </tr>
                <?php endwhile ?>

                </tbody>
            </table>
        </div>
    <?php endif ?>
<?php endif ?>

<?php if($data->user_logs_result->num_rows): ?>
    <h2 class="h4 mt-5"><?= $this->language->admin_user_view->logs->header ?></h2>
    <p class="text-muted"><?= $this->language->admin_user_view->logs->subheader ?></p>

    <div class="table-responsive table-custom-container">
        <table class="table table-custom">
            <thead class="thead-black">
            <tr>
                <th><?= $this->language->admin_user_view->logs->type ?></th>
                <th><?= $this->language->admin_user_view->logs->ip ?></th>
                <th><?= $this->language->admin_user_view->logs->date ?></th>
            </tr>
            </thead>
            <tbody>

            <?php $nr = 1; while($row = $data->user_logs_result->fetch_object()): ?>
                <tr>
                    <td><?= $row->type ?></td>
                    <td><?= $row->ip ?></td>
                    <td class="text-muted"><?= \Altum\Date::get($row->date, 1) ?></td>
                </tr>
            <?php endwhile ?>

            </tbody>
        </table>
    </div>
<?php endif ?>
