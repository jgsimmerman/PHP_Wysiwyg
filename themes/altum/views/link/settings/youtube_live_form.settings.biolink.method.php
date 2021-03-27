<?php defined('ALTUMCODE') || die() ?>

<form name="update_biolink_" method="post" role="form">
    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
    <input type="hidden" name="request_type" value="update" />
    <input type="hidden" name="type" value="biolink" />
    <input type="hidden" name="subtype" value="youtube_live" />
    <input type="hidden" name="link_id" value="<?= $row->link_id ?>" />

    <div class="notification-container"></div>
    
    <div class="form-group">
        <label><i class="fa fa-fw fa-signature fa-sm mr-1"></i> <?= $this->language->create_biolink_youtube_live_modal->input->location_url ?></label>
        <input type="text" class="form-control" name="channel_url" value="<?= $row->settings->channel_url ?>" placeholder="<?= $this->language->create_biolink_youtube_modal->input->location_url_placeholder ?>" required="required" />
    </div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-video fa-sm mr-1"></i> <?= $this->language->create_biolink_youtube_live_modal->input->selection_video ?></label>
        <select name="nth_video" class="form-control" required>
            <option value="0" <?= $row->settings->nth_video == 0 ? 'selected="true"' : null ?>><?= $this->language->create_biolink_youtube_live_modal->input->first_video ?></option>
            <option value="1" <?= $row->settings->nth_video == 1 ? 'selected="true"' : null ?>><?= $this->language->create_biolink_youtube_live_modal->input->second_video ?></option>
            <option value="2" <?= $row->settings->nth_video == 2 ? 'selected="true"' : null ?>><?= $this->language->create_biolink_youtube_live_modal->input->third_video ?></option>
            <option value="3" <?= $row->settings->nth_video == 3 ? 'selected="true"' : null ?>><?= $this->language->create_biolink_youtube_live_modal->input->fourth_video ?></option>
            <option value="4" <?= $row->settings->nth_video == 4 ? 'selected="true"' : null ?>><?= $this->language->create_biolink_youtube_live_modal->input->fifth_video ?></option>
        </select>
    </div>
</form>
