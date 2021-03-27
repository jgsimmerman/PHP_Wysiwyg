<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-book text-primary-900 mr-2"></i> <?= $this->language->admin_pages_categories->header ?></h1>

    <div class="col-auto">
        <a href="<?= url('admin/pages-category-create') ?>" class="btn btn-outline-primary"><i class="fa fa-fw fa-plus-circle"></i> <?= $this->language->admin_pages_categories->create ?></a>
    </div>
</div>

<?php display_notifications() ?>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead class="thead-black">
        <tr>
            <th><?= $this->language->admin_pages_categories->pages_categories->pages_category ?></th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $data->pages_categories_result->fetch_object()): ?>

            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <?php if(!empty($row->icon)): ?>
                        <span class="round-circle-md bg-primary-100 text-primary p-3 mr-3"><i class="<?= $row->icon ?> fa-fw"></i></span>
                        <?php endif ?>

                        <div class="d-flex flex-column">
                            <span><?= $row->title ?></span>
                            <span><a href="<?= SITE_URL . 'pages/' . $row->url ?>" class="text-muted"><?= $row->url ?></a></span>
                        </div>
                    </div>
                </td>
                <td class="text-muted">
                    <i class="fa fa-fw fa-sm fa-file-alt"></i> <?= sprintf($this->language->admin_pages_categories->pages_categories->total_pages, $row->total_pages) ?>
                </td>
                <td><?= get_admin_options_button('pages_category', $row->pages_category_id) ?></td>
            </tr>

        <?php endwhile ?>
        </tbody>
    </table>
</div>


<div class="d-flex justify-content-between mt-5 mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-file-alt text-primary-900 mr-2"></i> <?= $this->language->admin_pages->header ?></h1>

    <div class="col-auto">
        <a href="<?= url('admin/page-create') ?>" class="btn btn-outline-primary"><i class="fa fa-fw fa-plus-circle"></i> <?= $this->language->admin_pages->create ?></a>
    </div>
</div>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead class="thead-black">
        <tr>
            <th><?= $this->language->admin_pages->pages->page ?></th>
            <th><?= $this->language->admin_pages->pages->position ?></th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $data->pages_result->fetch_object()): ?>

            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <?php if(!empty($row->pages_category_icon)): ?>
                            <span class="round-circle-md bg-primary-100 text-primary p-3 mr-3" data-toggle="tooltip" title="<?= $row->pages_category_title ?>"><i class="<?= $row->pages_category_icon ?> fa-fw"></i></span>
                        <?php endif ?>

                        <div class="d-flex flex-column">
                            <span><?= $row->title ?></span>
                            <span><a href="<?= $row->type == 'internal' ? url('page/' . $row->url) : $row->url ?>" class="text-muted"><?= $row->url ?></a></span>
                        </div>
                    </div>
                </td>
                <td class="d-flex flex-column">
                    <?= $this->language->admin_pages->pages->{'position_' . $row->position} ?>
                    <small class="text-muted"><?= $this->language->admin_pages->input->{'type_' . strtolower($row->type)} ?></small>
                </td>
                <td class="text-muted"><?= sprintf($this->language->admin_pages->pages->total_views, nr($row->total_views)) ?></td>
                <td><?= get_admin_options_button('page', $row->page_id) ?></td>
            </tr>

        <?php endwhile ?>
        </tbody>
    </table>
</div>

