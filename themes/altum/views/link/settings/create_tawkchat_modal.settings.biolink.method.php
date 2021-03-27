<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_biolink_tawkchat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->create_biolink_tawkchat_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <p class="text-muted modal-subheader"><?= $this->language->create_biolink_tawkchat_modal->subheader ?></p>

            <div class="modal-body">
                <form name="create_biolink_tawkchat" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />
                    <input type="hidden" name="type" value="biolink" />
                    <input type="hidden" name="subtype" value="tawkchat" />

                    <div class="col-sm-12 mt-1">
                        <div class="form-group">
                            <i class="fas fa-comments"></i>&nbsp;&nbsp;<label for="tawkchatFormControlTextarea"><?= $this->language->create_biolink_tawkchat_modal->label ?></label>
                            <textarea class="form-control" id="widgetcode" name="widgetcode" rows="3" required></textarea>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->create_biolink_tawkchat_modal->submit ?></button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    $('form[name="create_biolink_tawkchat"]').on('submit', event => {

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

                    // swal('Add widget code successfully', '', 'success', {
                    //     timer: 4000
                    // });

                }
            },
            dataType: 'json'
        });

        event.preventDefault();
    })
</script>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>