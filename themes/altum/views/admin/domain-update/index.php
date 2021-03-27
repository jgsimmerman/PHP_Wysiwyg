<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="h3 mr-3"><i class="fa fa-fw fa-xs fa-globe text-primary-900 mr-2"></i> <?= $this->language->admin_domain_update->header ?></h1>

        <?= get_admin_options_button('domain', $data->domain->user_id) ?>
    </div>
</div>
<p class="text-muted"><?= $this->language->admin_domain_update->subheader ?></p>

<?php display_notifications() ?>

<div class="card">
    <div class="card-body">

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" />

            <div class="form-group">
                <label><i class="fa fa-fw fa-network-wired fa-sm mr-1 text-primary-900 mr-2"></i> <?= $this->language->admin_domain_create->form->host ?></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <select name="scheme" class="appearance-none select-custom-altum form-control form-control-lg input-group-text">
                            <option value="https://" <?= $data->domain->scheme == 'https://' ? 'selected="selected"' : null ?>>https://</option>
                            <option value="http://" <?= $data->domain->scheme == 'http://' ? 'selected="selected"' : null ?>>http://</option>
                        </select>
                    </div>
             
                    <input type="text" class="form-control form-control-lg" name="host" placeholder="<?= $this->language->admin_domain_create->form->host_placeholder ?>" value="<?= $data->domain->host ?>" required="required" />
                </div>
                <small class="text-muted"><i class="fa fa-fw fa-info-circle"></i> <?= $this->language->admin_domain_create->form->host_help ?></small>
            </div>

            <div class="form-group">
                <label><i class="fa fa-fw fa-sitemap fa-sm mr-1"></i> <?= $this->language->admin_domain_create->form->custom_index_url ?></label>
                <input type="text" class="form-control" name="custom_index_url" value="<?= $data->domain->custom_index_url ?>" placeholder="<?= $this->language->admin_domain_create->form->custom_index_url_placeholder ?>" />
                <small class="text-muted"><?= $this->language->admin_domain_create->form->custom_index_url_help ?></small>
            </div>

            <div class="form-group">
                <label><i class="fa fa-eye fa-sm mr-1"></i> <?= $this->language->admin_domain_update->status_label ?></label>
                <div style="padding:10px;">
                    <div class="checkbox checkbox-primary">
                        <input id="free" name="free" type="checkbox" <?= $data->domain->package_free ? 'checked' : null ?> />
                        <label for="free"><?= $this->language->admin_domain_update->free ?></label>
                    </div>

                    <div class="checkbox checkbox-primary">
                        <input id="small_business" name="small_business" type="checkbox" <?= $data->domain->package_small ? 'checked' : null ?> />
                        <label for="small_business"><?= $this->language->admin_domain_update->small_business ?></label>
                    </div>

                    <div class="checkbox checkbox-primary">
                        <input id="agency" name="agency" type="checkbox" <?= $data->domain->package_agency ? 'checked' : null ?> />
                        <label for="agency"><?= $this->language->admin_domain_update->agency ?></label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->global->update ?></button>
            </div>
        </form>

    </div>
</div>
