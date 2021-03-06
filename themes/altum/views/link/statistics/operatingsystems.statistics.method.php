<?php defined('ALTUMCODE') || die() ?>

<div class="card border-0">
    <div class="card-body">
        <h3 class="h5"><?= $this->language->link->statistics->os_name ?></h3>
        <p class="text-muted mb-3"><?= $this->language->link->statistics->os_name_help ?></p>

        <?php foreach($data->rows as $row): ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-truncate">
                        <?php if(!$row->os_name): ?>
                            <span><?= $this->language->link->statistics->os_name_unknown ?></span>
                        <?php else: ?>
                            <span><?= $row->os_name ?></span>
                        <?php endif ?>
                    </div>

                    <div>
                        <span class="badge badge-pill badge-primary"><?= nr($row->total) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
