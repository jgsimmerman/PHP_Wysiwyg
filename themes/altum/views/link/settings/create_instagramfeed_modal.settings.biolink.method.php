<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_biolink_instagramfeed" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->create_biolink_instagramfeed_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form name="create_biolink_youtube" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />
                    <input type="hidden" name="type" value="biolink" />
                    <input type="hidden" name="subtype" value="instagramfeed" />

                    <div class="col-sm-12 mt-1">
                        <?php
                            \Altum\Middlewares\Csrf::set_id($data->link->link_id);
                        ?>
                        <a href="<?= $data->instagram_redirecturi ?>" class="btn btn-info btn-block" ><?= sprintf($this->language->create_biolink_instagramfeed_modal->connect_button->connect, "<i class=\"fab fa-fw fa-instagram\"></i>") ?></a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
