<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-tags text-primary-900 mr-2"></i> <?= $this->language->admin_codes->header ?></h1>

    <div class="col-auto p-0">
        <a href="<?= url('admin/code-create') ?>" class="btn btn-outline-primary"><i class="fa fa-fw fa-plus-circle"></i> <?= $this->language->admin_codes->create ?></a>
    </div>
</div>

<?php display_notifications() ?>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead>
        <tr>
            <th><?= $this->language->admin_codes->table->code ?></th>
            <th><?= $this->language->admin_codes->table->type ?></th>
            <th><?= $this->language->admin_codes->table->package_id ?></th>
            <th><?= $this->language->admin_codes->table->discount ?></th>
            <th><?= $this->language->admin_codes->table->quantity ?></th>
            <th><?= $this->language->admin_codes->table->redeemed_codes ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
            <?php while($row = $data->codes_result->fetch_object()): ?>

            <tr data-code-id="<?= $row->code_id ?>">
                <td><?= $row->code ?></td>
                <td><?= $row->type == 'discount' ? '<span class="badge badge-pill badge-success">' . $row->type . '</span>' : '<span class="badge badge-pill badge-primary">' . $row->type . '</span>' ?></td>
                <td>
                    <span class="badge badge-pill badge-light">
                        <?= $row->package_name ?: $this->language->admin_codes->table->package_id_null ?>
                    </span>
                </td>
                <td><?= $row->discount . '%' ?></td>
                <td><?= $row->quantity ?></td>
                <td><i class="fa fa-fw fa-users text-muted"></i> <?= $row->redeemed ?></td>
                <td><?= get_admin_options_button('code', $row->code_id) ?></td>
            </tr>

            <?php endwhile ?>
        </tbody>
    </table>
</div>
