<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="project_link_clone" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->project_link_clone_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
            
                <form name="project_link_clone" method="post" role="form">
                    
                    <div class="form-group">
                        <label><i class="fa fa-fw fa-link fa-sm mr-1"></i> <?= $this->language->project_link_clone_modal->input->url ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                
                                <?php if(!empty($data->domains)): ?>
                                    <select name="domain_id" class="appearance-none select-custom-altum form-control input-group-text" id="domain_id" onchange="onChangeHandler(this)">
                                        <option value="0"><?= url() ?></option>
                                        <?php foreach($data->domains as $row): ?>
                                            <option value="<?= $row->domain_id ?>"><?= $row->url ?></option>
                                        <?php endforeach ?>
                                    </select>
                                <?php else: ?>
                                    <span class="input-group-text"><?= url() ?></span>
                                <?php endif ?>
                            </div>
                            <input type="text" class="form-control" name="url" />
                        </div>
                    </div>

                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="clone" />
                    <input type="hidden" name="project_id" value="" />
                    <input type="hidden" name="project_name" value="" />
                    <input type="hidden" name="project_url" value="" />
                    <input type="hidden" name="domainid" value="" />
                    <div class="notification-container"></div>

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
    let project_id;
    let domain_id;
    $('#project_link_clone').on('show.bs.modal', event => {

        project_id = $(event.relatedTarget).data('project-id');
        domain_id = $(event.currentTarget).find('select[name="domain_id"]').val();
        let project_name = $(event.relatedTarget).data('name');
        let project_url = $(event.relatedTarget).data('url');
        let url = project_url + '-copy';

        $(event.currentTarget).find('input[name="project_id"]').val(project_id);
        $(event.currentTarget).find('input[name="project_name"]').val(project_name);
        $(event.currentTarget).find('input[name="project_url"]').val(project_url);
        $(event.currentTarget).find('input[name="url"]').val(url);
        $(event.currentTarget).find('input[name="domainid"]').val(domain_id);
        
    });

    function onChangeHandler(e) {

        domain_id = $( "#domain_id option:selected" ).val();
        $("input[name='domainid']").val(domain_id);
    }
    $('form[name="project_link_clone"]').on('submit', event => {

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
                    // $(event.currentTarget).find('input[name="url"]').val('');
                }

                else if(data.status == 'success') {

                    let notification_container = $(event.currentTarget).find('.notification-container');
                    notification_container.html('');
                    display_notifications(data.message, 'success', notification_container);
                    // $(event.currentTarget).find('input[name="url"]').val('');

                    setTimeout(() => {
                        
                        /* Hide modal */
                        $('#project_link_clone').modal('hide');
                        /* Clear input values */
                        $('form[name="project_link_clone"] input').val('');
                        // /* Fade out refresh */
                        redirect(`project/${project_id}`);

                    }, 2000);

                }
            },
            dataType: 'json'
        });

        event.preventDefault();
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

