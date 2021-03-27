<?php defined('ALTUMCODE') || die() ?>


<div id="uploadimageModal" class="modal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
      		<div class="modal-header">
                <h5 class="modal-title"><?= $this->language->create_imageHandle_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
      		</div>

            <p class="text-muted modal-subheader"><?= $this->language->create_imageHandle_modal->subheader ?></p>

      		<div class="modal-body">
        		<div class="row">
                    <div class="col-md-8 text-center">
                        <div id="image_demo" style="width:350px; margin-top:30px"></div>
                    </div>

				</div>
      		</div>
            <div class="modal-footer">

                <div class="text-center mt-4">
                    <button class="btn btn-primary crop_image"><?= $this->language->create_imageHandle_modal->input->submit ?></button>
                </div>

            </div>
    	</div>
    </div>
</div>