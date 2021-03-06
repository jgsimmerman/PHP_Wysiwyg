<?php defined('ALTUMCODE') || die() ?>

<div class="card border-0">
    <div class="card-body">
        <h3 class="h5"><?= $this->language->link->statistics->browser_language ?></h3>
        <p class="text-muted mb-3"><?= $this->language->link->statistics->browser_language_help ?></p>

        <?php foreach($data->rows as $row): ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-truncate">
                        <?php if(!$row->browser_language): ?>
                            <span><?= $this->language->link->statistics->browser_language_unknown ?></span>
                        <?php else: ?>
                            <span><?= $row->browser_language ?></span>
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
