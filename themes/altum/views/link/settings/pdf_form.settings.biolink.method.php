<?php defined('ALTUMCODE') || die() ?>

<form name="update_biolink_" id="<?= 'updateform' .$row->link_id ?>" method="post" role="form">

    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
    <input type="hidden" name="request_type" value="update" />
    <input type="hidden" name="type" value="biolink" />
    <input type="hidden" name="subtype" value="pdf" />
    <input type="hidden" name="link_id" value="<?= $row->link_id ?>" />

    <div class="notification-container"></div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-heading fa-sm mr-1"></i> <?= $this->language->create_biolink_pdf_modal->input->title ?></label>
        <input type="text" class="form-control" name="title" value="<?= $row->settings->title ?>" placeholder="<?= $this->language->create_biolink_pdf_modal->input->title_placeholder ?>" required="required" />
    </div>

    <div style="display: flex; justify-content: space-evenly;">
        <div class="form-group">
            <label class="<?= 'replacelabel'?>">
                <i class="fa fa-paperclip"></i>
                <span class="title"><?= $this->language->create_biolink_pdf_modal->input->replace ?></span>
                <input class="FileUpload2" id="<?= 'FileReplace' .$row->link_id?>" name="FileReplace" type="file" />
            </label>
        </div>
    
        <div class="form-group" id="<?= 'container'. $row->link_id ?>">
            <canvas id="<?= 'current_thumb_pdf'. $row->link_id ?>" style="width:80px;"></canvas>
        </div>
        <div class="form-group" id="<?= 'replace_thumbnail_pdf' .$row->link_id ?>">
            <canvas id="<?= 'replace_pdf' .$row->link_id ?>" style="width:100px;"></canvas>
        </div>
    </div>

    <div class="<?= !$this->user->package_settings->custom_colored_links ? 'container-disabled': null ?>">
    
        <div class="form-group">
            <label><i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= $this->language->create_biolink_pdf_modal->input->title_color ?></label>
            <input type="hidden" name="text_color" class="form-control" value="<?= $row->settings->title_color ?>" required="required" />
            <div class="text_color_pickr"></div>
        </div>

        <div class="form-group">
            <label><i class="fa fa-fw fa-fill fa-sm mr-1"></i> <?= $this->language->create_biolink_pdf_modal->input->background_color ?></label>
            <input type="hidden" name="background_color" class="form-control" value="<?= $row->settings->background_color ?>" required="required" />
            <div class="background_color_pickr"></div>
        </div>

        <div class="form-group">
            <label><?= $this->language->create_biolink_pdf_modal->input->border_radius ?></label>
            <select name="border_radius" class="form-control">
                <option value="straight" <?= $row->settings->border_radius == 'straight' ? 'selected="true"' : null ?>><?= $this->language->create_biolink_pdf_modal->input->border_radius_straight ?></option>
                <option value="round" <?= $row->settings->border_radius == 'round' ? 'selected="true"' : null ?>><?= $this->language->create_biolink_pdf_modal->input->border_radius_round ?></option>
                <option value="rounded" <?= $row->settings->border_radius == 'rounded' ? 'selected="true"' : null ?>><?= $this->language->create_biolink_pdf_modal->input->border_radius_rounded ?></option>
            </select>
        </div>

    </div>

</form>

<script type="text/javascript" src="<?= SITE_URL . ASSETS_URL_PATH . 'js/pdf.js' ?>"></script>
<script type="text/javascript" src="<?= SITE_URL . ASSETS_URL_PATH . 'js/pdf.worker.js' ?>"></script>

<script>

    // Current PDF file thumbnail

    (async () => {

        const loadingTask = PDFJS.getDocument("<?= $row->location_url ?>");
        const pdf = await loadingTask.promise;
        const page = await pdf.getPage(1);
        const scale = 1;
        const viewport = page.getViewport(scale);
        const canvas = document.getElementById("current_thumb_pdf" + "<?= $row->link_id ?>");
        const context = canvas.getContext("2d");
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };
        await page.render(renderContext);

    })();

    $("#<?= 'FileReplace' .$row->link_id ?>").on('change',function (e) {

        let labelVal = $(".title").text();
        let oldfileName = $(this).val();
        fileName = e.target.value.split( '\\' ).pop();

        if (oldfileName == fileName) {return false;}
        let extension = fileName.split('.').pop();

        if(extension !== 'pdf') {

            swal("Select PDF file!", "", "error", {
                timer: 2000
            });
            return false;
        }

        $("#<?= 'container'. $row->link_id ?>").hide();
        let selectedFile = document.getElementById("<?= 'FileReplace' .$row->link_id ?>").files;

        if (selectedFile.length > 0) {

            let fileToLoad = selectedFile[0];
            let fileReader = new FileReader();
            fileReader.onload = function(fileLoadedEvent) {
                
                base64 = fileLoadedEvent.target.result;
                base64 = base64.replace('data:application/pdf;base64,','');
                let binary = atob(base64);
                let len = binary.length;
                let buffer = new ArrayBuffer(len);
                let view = new Uint8Array(buffer);
                
                for (let bini = 0; bini < len; bini++) {
                    view[bini] = binary.charCodeAt(bini);
                }

                let blob = new Blob([view], { type: "application/pdf" });
                let url = URL.createObjectURL(blob);

                let __PDF_DOC,
                __CANVAS = $("#<?= 'replace_pdf' .$row->link_id ?>").get(0),
                __CANVAS_CTX = __CANVAS.getContext('2d');


                PDFJS.getDocument({ url: url }).then(function (pdf_doc) {

                    __PDF_DOC = pdf_doc;
                    __TOTAL_PAGES = __PDF_DOC.numPages;
                    $("#<?= 'replace_thumbnail_pdf' .$row->link_id ?>").show();
                    __PAGE_RENDERING_IN_PROGRESS = 1;
                    __CURRENT_PAGE = 1;

                    __PDF_DOC.getPage(1).then(function (page) {

                        let scale_required = __CANVAS.width / page.getViewport(1).width;
                        let viewport = page.getViewport(scale_required);
                        let thumbHeight = viewport.height;
                        if (thumbHeight < 100) {
                            __CANVAS.height = 100
                        } else {
                            __CANVAS.height = thumbHeight;
                        }
                        let renderContext = {
                            canvasContext: __CANVAS_CTX,
                            viewport: viewport
                        };

                        page.render(renderContext).then(function () {
                            __PAGE_RENDERING_IN_PROGRESS = 0;

                        });
                    });

                }).catch(function (error) {
                    alert(error.message);
                });

            };
            fileReader.readAsDataURL(fileToLoad);
        }

        $("#<?= 'updateform' .$row->link_id ?>").on('change', event => {

            let form = $(event.currentTarget)[0];
            let data = new FormData(form);
            let notification_container = $(event.currentTarget).find('.notification-container');

            $.ajax({

                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,

                success: (data) => {

                    if(data.status == 'error') {

                        let notification_container = $(event.currentTarget).find('.notification-container');
                        notification_container.html('');
                        display_notifications(data.message, 'error', notification_container);
                        
                    } else if(data.status == 'success') {

                    }
                },

                dataType: 'json'
            });

            event.preventDefault();

        });

    });
</script>