<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_biolink_pdf" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $this->language->create_biolink_pdf_modal->header ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <p class="text-muted modal-subheader"><?= $this->language->create_biolink_pdf_modal->subheader ?></p>

            <div class="modal-body">
                <form name="create_biolink_pdf" method="post" role="form" enctype="multipart/form-data">

                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />
                    <input type="hidden" name="type" value="biolink" />
                    <input type="hidden" name="subtype" value="pdf" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label><i class="fa fas fa-file-pdf fa-sm mr-1"></i> <?= $this->language->create_biolink_pdf_modal->input->title ?></label>
                        <input type="text" class="form-control" name="pdf_title" required="required" placeholder="<?= $this->language->create_biolink_pdf_modal->input->title_placeholder ?>" />
                    </div>
                    

                    <label class="filelabel">
                        <i class="fa fa-paperclip"></i>
                        <span class="title"><?= $this->language->create_biolink_pdf_modal->input->upload ?></span>
                        <input class="FileUpload1" id="FileInput" name="pdfFile" type="file" />
                    </label>
                    <br />
                    <div class="form-group" id="thumbnail_pdf" style="margin-left:3px;">
                        <canvas id="upload_pdf" style="width:100px;"></canvas>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary"><?= $this->language->create_biolink_pdf_modal->input->submit ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<?php ob_start() ?>
<script>

    let sel_file;
    $("#FileInput").on('change',function (e) {

        console.log("cliecked dsfsdafsdafsdaf")
        
        let labelVal = $(".title").text();
        let oldfileName = $(this).val();
            fileName = e.target.value.split( '\\' ).pop();

            if (oldfileName == fileName) {return false;}
            let extension = fileName.split('.').pop();

        if ($.inArray(extension,['jpg','jpeg','png']) >= 0) {
            $(".filelabel i").removeClass().addClass('fa fa-file-image-o');
            $(".filelabel i, .filelabel .title").css({'color':'#208440'});
            $(".filelabel").css({'border':' 2px solid #208440'});
        }
        else if(extension == 'pdf'){
            $(".filelabel i").removeClass().addClass('fa fa-file-pdf-o');
            $(".filelabel i, .filelabel .title").css({'color':'red'});
            $(".filelabel").css({'border':' 2px solid red'});

        }
        else if(extension == 'doc' || extension == 'docx'){
            $(".filelabel i").removeClass().addClass('fa fa-file-word-o');
            $(".filelabel i, .filelabel .title").css({'color':'#2388df'});
            $(".filelabel").css({'border':' 2px solid #2388df'});
        }
        else{
            $(".filelabel i").removeClass().addClass('fa fa-file-o');
            $(".filelabel i, .filelabel .title").css({'color':'black'});
            $(".filelabel").css({'border':' 2px solid black'});
        }

        if(fileName) {
            if (fileName.length > 10){
                $(".filelabel .title").text(fileName.slice(0,4)+'...'+extension);
            } else{
                $(".filelabel .title").text(fileName);
            }
        } else {
            $(".filelabel .title").text(labelVal);
        }

        //Read File
        let selectedFile = document.getElementById("FileInput").files;
        sel_file = selectedFile;

        //Check File is not Empty
        if (selectedFile.length > 0) {

            // Select the very first file from list
            let fileToLoad = selectedFile[0];
            // FileReader function for read the file.
            let fileReader = new FileReader();
            // Onload of file read the file content
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
                __CANVAS = $('#upload_pdf').get(0),
                __CANVAS_CTX = __CANVAS.getContext('2d');


                PDFJS.getDocument({ url: url }).then(function (pdf_doc) {

                    __PDF_DOC = pdf_doc;
                    __TOTAL_PAGES = __PDF_DOC.numPages;
                    $("#thumbnail_pdf").show();

                    // Show the first page
                    __PAGE_RENDERING_IN_PROGRESS = 1;
                    __CURRENT_PAGE = 1;

                    // Fetch the page
                    __PDF_DOC.getPage(1).then(function (page) {
                        // As the canvas is of a fixed width we need to set the scale of the viewport accordingly
                        let scale_required = __CANVAS.width / page.getViewport(1).width;

                        // Get viewport of the page at required scale
                        let viewport = page.getViewport(scale_required);

                        // Set canvas height

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

                        // Render the page contents in the canvas
                        page.render(renderContext).then(function () {
                            __PAGE_RENDERING_IN_PROGRESS = 0;

                        });
                    });

                }).catch(function (error) {
                    alert(error.message);
                });

            };
            // Convert data to base64
            fileReader.readAsDataURL(fileToLoad);
        }

    });

    $('form[name="create_biolink_pdf"]').on('submit', event => {

        let form = $(event.currentTarget)[0];
        let data = new FormData(form);
        let notification_container = $(event.currentTarget).find('.notification-container');

        if(sel_file == undefined) {

            swal("You have to upload PDF file!", "", "error", {
                timer: 2000
            });
            return false;
        }
        $.ajax({

            type: 'POST',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            url: 'link-ajax',
            data: data,

            // success: (data) => {

            //     display_notifications(data.message, data.status, notification_container);
            //     notification_container[0].scrollIntoView();
            //     update_main_url();
            // },

            success: (data) => {

                if(data.status == 'error') {

                    let notification_container = $(event.currentTarget).find('.notification-container');
                    notification_container.html('');
                    display_notifications(data.message, 'error', notification_container);
                    
                } else if(data.status == 'success') {

                    /* Fade out refresh */
                    fade_out_redirect({ url: data.details.url, full: true });
                }
            },

            dataType: 'json'
        });

        event.preventDefault();

    });

</script>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
