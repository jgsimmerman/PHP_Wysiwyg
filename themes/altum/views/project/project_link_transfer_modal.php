<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="project_link_transfer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->project_link_transfer_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form name="project_link_transfer" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="transfer" />
                    <input type="hidden" name="project_id" value="" />
                    <input type="hidden" name="project_name" value="" />
                    <input type="hidden" name="project_url" value=" /"> 
                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label><i class="fa fa-fw fa-envelope fa-sm mr-1"></i> <?= $this->language->project_link_transfer_modal->input->email ?></label>
                        <input type="email" class="form-control" name="email" required  />
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->global->submit ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    /* On modal show load new data */
    $('#project_link_transfer').on('show.bs.modal', event => {
        let project_id = $(event.relatedTarget).data('project-id');
        let project_name = $(event.relatedTarget).data('name');
        let project_url = $(event.relatedTarget).data('url');

        $(event.currentTarget).find('input[name="project_id"]').val(project_id);
        $(event.currentTarget).find('input[name="project_name"]').val(project_name);
        $(event.currentTarget).find('input[name="project_url"]').val(project_url);
    });

    $('form[name="project_link_transfer"]').on('submit', event => {

        // window.alert( JSON.stringify($(event.currentTarget).serialize()))

        $.ajax({
            type: 'POST',
            url: 'project-ajax',
            data: $(event.currentTarget).serialize(),
            success: (data) => {
                
                if (data.status == 'error') {
                    let notification_container = $(event.currentTarget).find('.notification-container');

                    notification_container.html('');

                    display_notifications(data.message, 'error', notification_container);

                    $(event.currentTarget).find('input[name="email"]').val('');
                }

                else if(data.status == 'success') {

                    console.log(data);

                    let notification_container = $(event.currentTarget).find('.notification-container');

                    notification_container.html('');

                    display_notifications(data.message, 'success', notification_container);

                    $(event.currentTarget).find('input[name="email"]').val('');

                    setTimeout(() => {
                        
                        /* Hide modal */
                        $('#project_link_transfer').modal('hide');

                        /* Clear input values */
                        $('form[name="project_link_transfer"] input').val('');

                    }, 2000);

                    // /* Fade out refresh */
                    // redirect(`dashboard`);

                }
            },
            dataType: 'json'
        });

        event.preventDefault();
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

