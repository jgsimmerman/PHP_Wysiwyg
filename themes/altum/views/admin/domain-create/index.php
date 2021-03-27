<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-globe text-primary-900 mr-2"></i> <?= $this->language->admin_domain_create->header ?></h1>
</div>
<p class="text-muted"><?= $this->language->admin_domain_create->subheader ?></p>

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
                            <option value="https://">https://</option>
                            <option value="http://">http://</option>
                        </select>
                    </div>

                    <input type="text" class="form-control form-control-lg" name="host" placeholder="<?= $this->language->admin_domain_create->form->host_placeholder ?>" required="required" />
                </div>
                <small class="text-muted"><?= $this->language->admin_domain_create->form->host_help ?></small>
            </div>

            <div class="form-group">
                <label><i class="fa fa-fw fa-sitemap fa-sm mr-1"></i> <?= $this->language->admin_domain_create->form->custom_index_url ?></label>
                <input type="text" class="form-control" name="custom_index_url" placeholder="<?= $this->language->admin_domain_create->form->custom_index_url_placeholder ?>" />
                <small class="text-muted"><?= $this->language->admin_domain_create->form->custom_index_url_help ?></small>
            </div>

            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->admin_domain_create->form->create ?></button>
            </div>
        </form>

    </div>
</div>

