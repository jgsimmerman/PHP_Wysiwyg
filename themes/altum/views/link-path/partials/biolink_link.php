<?php defined('ALTUMCODE') || die() ?>

<div class="my-3">
    <a href="<?= $data->link->location_url . $data->link->utm_query ?>" data-location-url="<?= $data->link->url ?>" class="btn btn-block btn-primary link-btn <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>">

        <?php if($data->link->settings->icon): ?>
            <i class="<?= $data->link->settings->icon ?> mr-1"></i>
        <?php endif ?>

        <?= $data->link->settings->name ?>
    </a>
</div>


