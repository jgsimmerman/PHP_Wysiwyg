<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_biolink_youtube_live" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->create_biolink_youtube_live_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <p class="text-muted modal-subheader"><?= $this->language->create_biolink_youtube_live_modal->subheader ?></p>

            <div class="modal-body">
                <form name="create_biolink_youtube_live" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />
                    <input type="hidden" name="type" value="biolink" />
                    <input type="hidden" name="subtype" value="youtube_live" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label><i class="fa fa-fw fa-signature fa-sm mr-1"></i> <?= $this->language->create_biolink_youtube_live_modal->input->location_url ?></label>
                        <input type="text" class="form-control" name="location_url" required="required" placeholder="<?= $this->language->create_biolink_youtube_live_modal->input->location_url_placeholder ?>" />
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-fw fa-video fa-sm mr-1"></i> <?= $this->language->create_biolink_youtube_live_modal->input->selection_video ?></label>
                        <select name="nth_youtube" class="form-control" id="nth_youtube" required>
                            <option value="0"><?= $this->language->create_biolink_youtube_live_modal->input->first_video ?></option>
                            <option value="1"><?= $this->language->create_biolink_youtube_live_modal->input->second_video ?></option>
                            <option value="2"><?= $this->language->create_biolink_youtube_live_modal->input->third_video ?></option>
                            <option value="3"><?= $this->language->create_biolink_youtube_live_modal->input->fourth_video ?></option>
                            <option value="4"><?= $this->language->create_biolink_youtube_live_modal->input->fifth_video ?></option>
                        </select>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->create_biolink_youtube_live_modal->input->submit ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    $('form[name="create_biolink_youtube_live"]').on('submit', event => {

        $.ajax({
            type: 'POST',
            url: 'link-ajax',
            data: $(event.currentTarget).serialize(),
            success: (data) => {
                if(data.status == 'error') {

                    let notification_container = $(event.currentTarget).find('.notification-container');

                    notification_container.html('');

                    display_notifications(data.message, 'error', notification_container);

                }

                else if(data.status == 'success') {

                    /* Fade out refresh */
                    fade_out_redirect({ url: data.details.url, full: true });

                }
            },
            dataType: 'json'
        });

        event.preventDefault();
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
