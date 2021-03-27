<?php defined('ALTUMCODE') || die() ?>

<body>
    <form name="edit_instagram_profile" id="edit_instagram_profile" method="post" role="form">
        <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
        <input type="hidden" name="request_type" value="update" />
        <input type="hidden" name="type" value="biolink" />
        <input type="hidden" name="subtype" value="instagramfeed" />
        <input type="hidden" name="link_id" value="<?= $row->link_id ?>" />
        <input type="hidden" name="remove_status" value="">

        <?php
        
            $tagged_product_key_arr = array();
            $products = json_decode($data->shopify_products_result);

            if($products) {
                $products = json_decode($products)->products;
                $tagged_products_result = json_decode($data->tagged_products_result);

                if($tagged_products_result) {
                    $new_tagged_num = count($tagged_products_result) + 1;
                    foreach($tagged_products_result as $key => $tagged_product) {
                        array_push($tagged_product_key_arr, key($tagged_product));  
                    }
                }
            }
        
        ?>
        <div class="instagram-container">

            <div class="instagram-preview-container">
                <div class="instagram-preview">
                    <div class="edit-header">
                        <h6><?= $this->language->create_biolink_instagramfeed_modal->thumbnail->header ?></h6>
                        <p><?= $this->language->create_biolink_instagramfeed_modal->thumbnail->date_prefix ?>
                        <?php
                            $mediaArr = json_decode($row->settings->medias) ;
                            $media = $mediaArr[0];
                            $date = $media->timestamp; 
                            $timeformat = strtotime($date);
                            $postdate = date("F j, Y", $timeformat); 
                            echo $postdate;    
                        ?>
                        </p>
                    </div>
                    <input type="hidden" name="post_connected_link_id" value="<?= $media->id?>">

                    <div class="container image-edit-layout <?= $media->link || in_array($media->id, $tagged_product_key_arr) ? '' : 'image-edit-link-blank' ?> ">
                        <img src="<?= $media->media_url?>" width="200px" />
                        <?php if($media->link) : ?>
                            <div class="image-caption"><b><?= $this->language->create_biolink_instagramfeed_modal->thumbnail->linked_badge ?></b></div>
                        <?php endif?>
                    </div>
                    <div class="form-group post-connect-link">
                        <label><?= $this->language->create_biolink_instagramfeed_modal->input->label ?></label>
                        <?php if(in_array($media->id, $tagged_product_key_arr)): ?>
                            <input type="text" name="post_connected_link" value="Linked to shopify product" class="form-control" required="required" disabled/>
                        <?php else: ?>
                            <input type="text" name="post_connected_link" value="<?= $media->link ?>" class="form-control" required="required" />
                        <?php endif; ?>
                    </div>
                    <div class="button-container">
                        <button name="remove" class="btn btn-danger" id="remove" ><?= $this->language->create_biolink_instagramfeed_modal->input->remove ?></button>
                        <button name="submit" class="btn btn-primary" id="save" ><?= $this->language->create_biolink_instagramfeed_modal->input->save ?></button>
                    </div>

                    <div class="more-link">
                        <a href="#" data-toggle="modal" data-target="#more_link_modal"><strong>More Link</strong></a>
                    </div>
                </div>
            </div>

            <div class="instagram-phone">
                <div class="phone-border">
                    <div class="instagram-header">
                        <div class="instagram-profile-info">
                            <img src="<?= $row->settings->profile_pic_url ? $row->settings->profile_pic_url: 'https://later-frontend-assets.later.com/assets/images/img--userAvatar--placeholder-2a51ace086420f9112cf8510a7f67422.svg' ?>" class="instagram-avatar" /><span class="instagram-profile-name"><?= $row->settings->profile_name ?></span>
                        </div>
                    </div>

                    <div class="instagram-body">
                        <div class="container">
                            <?php
                                $medias = json_decode($row->settings->medias);
                                $rowcnt = 0;
                                while ($rowcnt < 4) {
                            ?>
                            <div class="row row-container">
                                <?php $colcnt = 0;  while ($colcnt < 3) { ?>
                                    <div class="col-sm-4 col-xs-4">
                                        <div style="border: 2px solid transparent;">

                                            <?php if(!empty($medias[3 * $rowcnt + $colcnt])): ?>

                                                <div class="<?= $medias[3 * $rowcnt + $colcnt]->link ||  in_array($medias[3 * $rowcnt + $colcnt]->id, $tagged_product_key_arr) ? '' : 'image-edit-link-blank' ?> <?= $medias[3 * $rowcnt + $colcnt]->media_url ? '' : 'blankimagecontainer'?>" >
                                                    <img src="<?= $medias[3 * $rowcnt + $colcnt]->media_url ? $medias[3 * $rowcnt + $colcnt]->media_url: SITE_URL.ASSETS_URL_PATH .'images/blankimage.jpg' ?>" class="<?= $medias[3 * $rowcnt + $colcnt]->media_url ? '' : 'blankimagecontent' ?>" id="<?= $medias[3 * $rowcnt + $colcnt]->id ?>" width="88" style="max-height:88px;" />
                                                </div>
                                            <?php else: ?>

                                                <div class="image-edit-link-blank blankimagecontainer">
                                                    <img src="<?= SITE_URL.ASSETS_URL_PATH .'images/blankimage.jpg' ?>" class="blankimagecontent" width="88" style="max-height:88px;" />
                                                </div>

                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php $colcnt++; } ?>
                            </div>
                            <?php $rowcnt++; } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="notification-container"></div>

    </form>

    <div class="shopify-link-container">
        <?php 
            \Altum\Middlewares\Csrf::set_shopify_link_id($row->link_id); 
            \Altum\Middlewares\Csrf::set_user_id($this->user->user_id);
        ?>
        <div class="dropdown">
            <button type="button" data-toggle="dropdown" class="btn btn-primary rounded-pill dropdown-toggle dropdown-toggle-simple" style="background-color:darkblue;"><i class="fa fa-fw fa-plus-circle"></i>Connect your Marketplace</button>

            <div class="dropdown-menu dropdown-menu-right">
                <?php $marketplace_types = require APP_PATH . 'includes/marketplace_types.php'; ?>

                <?php foreach($marketplace_types as $key): ?>
                
                    <a href="#" class="dropdown-item" data-toggle="modal" data-marketplace="<?=$key?>" data-target="#create_<?= $key ?>_connection_modal">
                        <i class="<?= $this->language->create_biolink_instagramfeed_modal->input->{$key}->icon ?>"></i>

                        <?= $this->language->create_biolink_instagramfeed_modal->input->{$key}->name ?>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    </div>



    <div class="modal fade" id="create_shopify_connection_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><?= $this->language->create_biolink_instagramfeed_modal->input->shopify->header ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="container">
                        <div class="row">

                            <div class="col-sm-6 col-md-6 col-lg-6 mt-1">

                                <img src="<?= SITE_URL. ASSETS_URL_PATH .'images/shopify_connect.png'?>" width="150">
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-6 mt-1">

                                    <div class="shopify_inputbox">
                                        <label><?= $this->language->create_biolink_instagramfeed_modal->input->shopify->store_name_label ?></label>
                                        <input type="text" name="shopify_store_name" value="" class="form-control" placeholder="<?= $this->language->create_biolink_instagramfeed_modal->input->shopify->store_name_placeholder?>" required="required" />
                                    </div>
                                    <button name="shopify_connect" class="btn btn-primary shopify_connect" id="shopify_connect" disabled><i class="fab fa-shopify fa-sm mr-1"></i><?= $this->language->create_biolink_instagramfeed_modal->input->shopify->name ?></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="create_etsy_connection_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><?= $this->language->create_biolink_instagramfeed_modal->input->etsy->header ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="container">
                        <div class="row">

                            <div class="col-sm-6 col-md-6 col-lg-6 mt-1">

                                <img src="<?= SITE_URL. ASSETS_URL_PATH .'images/shopify_connect.png'?>" width="150">
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-6 mt-1">
                                <label><?= $this->language->create_biolink_instagramfeed_modal->input->etsy->label ?></label>
                                <br />
                                <button name="etsy_connect" class="btn btn-primary etsy_connect" id="etsy_connect"><i class="fab fa-etsy fa-sm mr-1"></i><?= $this->language->create_biolink_instagramfeed_modal->input->etsy->name ?></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="more_link_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered more-link-modal" role="document">
            <div class="modal-content">

                <div class="container">
                    <div class="row">
                        <div class="col-sm-8 col-md-8 col-lg-8 mt-1" id="product_full_image_container">
                            <img src="<?= $media->media_url?>" width="400" id="product_full_image">
                        </div>
                        <div class="col-sm-4 col-md-4 col-lg-4 mt-1 dynamic-content">

                            <?php if(in_array($media->id, $tagged_product_key_arr)):?>
                                <div>
                                    <div class="row">
                                        <h6>Shopify Products</h6>
                                    </div>

                                    <?php 
                                    foreach($tagged_products_result as $key => $tagged_product_item): 
                                        $tagged_product_obj = get_object_vars($tagged_product_item);
                                        $tagged_product = $tagged_product_obj[key($tagged_product_obj)];
                                        if(key($tagged_product_obj) == $media->id):
                                        ?>
                                        <div class="row tagged-product-details-list">
                                            <div class="col-sm-2 col-md-2 col-lg-2 mt-1 tag-number-list">
                                                <div class="cTG-tag">
                                                    <span><?= $tagged_product->tag_number ?></span>
                                                </div>
                                                <form name="delete_product_form" id="delete_product_form" method="post" role="form">
                                                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                                                    <input type="hidden" name="request_type" value="delete_shopify_tag" />
                                                    <input type="hidden" name="link_id" value="<?= $row->link_id?>">
                                                    <input type="hidden" name="post_id" value="<?= $media->id?>" />
                                                    <input type="hidden" name="shopify_product_vendor" value="<?= $tagged_product->shopify_product_vendor?>" />
                                                    <input type="hidden" name="variant_id" value="<?= $tagged_product->shopify_product_variant_id?>" />
                                                    
                                                    <div class="trash-cTG-tag">
                                                        <button type="submit"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-sm-2 col-md-2 col-lg-2 mt-1">
                           
                                                <img src="<?= $tagged_product->shopify_product_image_url?>" width="50" />
                                            </div>
                                            <div class="col-sm-8 col-md-8 col-lg-8 mt-1 tag-product-detail-list">
                                                <p><?= $tagged_product->shopify_product_title?></p>
                                                <p>$<?= $tagged_product->shopify_product_price?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <div class="tag-another-product" data-toggle="modal" data-target="#shopify_products_lists" id="tag_another_product">
                                        <p>+ Tag Another Product</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="link-description">
                                    <div class="row">
                                        <h6>Link Options</h6>
                                        <small>Choose to either link your image to external websites or link to Marketplace products</small>
                                    </div>
        
                                    <div class="row">
                                        <div class="col-sm-1 col-md-1 col-lg-1 mt-1">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div class="col-sm-10 col-md-10 col-lg-10 mt-1">
                                            <p>Link to External Websites</p>
                                            <small>Link your image to external websites</small>
                                        </div>
        
                                    </div>
        
                                    <div class="row" data-toggle="modal" data-target="#shopify_products_lists" id="shopify_products_details">
                                        <div class="col-sm-1 col-md-1 col-lg-1 mt-1">
                                            <i class="fa fa-shopping-cart"></i>
                                        </div>
                                        <div class="col-sm-10 col-md-10 col-lg-10 mt-1">
                                            <p>Link to Marketplace Products</p>
                                            <small>Tag Marketplace products in your image</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shopify_products_lists" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered shopify-products-lists" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="">
                        <h6>Products</h6>
                        <small>Select a product from your Marketplace store.</small>
                    </div>
                    <button type="button" class="close" id="close_shopify_products_lists" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <?php if($products): 
                        foreach($products as $key => $product): ?>
                        <div class="product-list">
                            <div class="row">

                                <div class="col-sm-3 col-md-3 col-lg-3 mt-1 radio-select-container">
                                    <input type="radio" id="product_<?= $product->id?>" name="product" value="product_<?= $product->id?>">
                                    <img src="<?= $product->image->src?>" width="70" height="70">
                                </div>
                                <div class="col-sm-9 col-md-9 col-lg-9 mt-1">

                                    <div class="panel-group" id="accordion">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h6 class="panel-title" data-toggle="collapse" data-target="#collapse<?= $product->id?>"><span class="arrow-more-detail"><i class="fas fa-angle-down"></i><span>
                                                </h6>
                                            </div>
                                        <p><?= $product->title?></p>
                                            <div id="collapse<?= $product->id?>" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                <p><?= $product->body_html ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

</body>

<script>

    $('.instagram-body').on('click', 'img', function() {

        let medias = JSON.parse(JSON.stringify(<?= $row->settings->medias ?>));
        let post_id = this.id;
        let media = medias.filter(media => media.id == post_id)[0];
        let media_url = media.media_url;
        let timestamp = media.timestamp;
        let link = media.link
        let timeformatted_postdate = Date.parse(timestamp);
        let new_postdate = new Date(timeformatted_postdate);
        let postdate = new_postdate.toDateString();
        let token = '<?= \Altum\Middlewares\Csrf::get() ?>';
        let link_id = '<?= $row->link_id ?>';

        let tagged_product_key_arr = JSON.parse(JSON.stringify(<?= json_encode($tagged_product_key_arr) ?>));
        var tagged_products_result = JSON.parse(JSON.stringify(<?= $data->tagged_products_result ? $data->tagged_products_result : json_encode($data->tagged_products_result) ?>));

        let post_link = tagged_product_key_arr.indexOf(media.id) != -1 ? '<input type="text" name="post_connected_link" value="Linked to shopify product" class="form-control" required="required" disabled/>' : '<input type="text" name="post_connected_link" value="'+ link + '" class="form-control" required="required" />'

        medias.forEach(media => {

            $('#' + media.id).parent().parent().removeClass('image-border');
        })

        let html_text = `
            <input type="hidden" name="post_connected_link_id" value="${post_id}" />
            <div class="edit-header">
                <h6>Edit Links</h6>
                <p>Posted on : ${postdate}</p>
            </div>
            <div class="container image-edit-layout">
                <div class="image-caption">
                ${link ? '<b>LINKED</b></div>' : '</div>'} 
                <img src="${media_url}" width="200px" />
            </div>
            <div class="form-group post-connect-link">
                <label>Linkin Page</label>
                 ${post_link}
            </div>
            <div class="button-container">
                <button name="remove" class="btn btn-danger" id="remove">Remove Link</button>
                <button name="submit" class="btn btn-primary" id="save">Save</button>
            </div>
            <div class="more-link">
                <a href="#" data-toggle="modal" data-target="#more_link_modal"><strong>More Link</strong></a>
            </div>
        `;


        $(this).parent().parent().attr('class', 'image-border');
        $('.instagram-preview').html(html_text);

        $('.more-link').click(function(e) {

            localStorage.setItem('tag_post_id', post_id);
            localStorage.setItem('taggedFlag', false);
            $('#product_full_image').attr('src', media_url);

            let loop_content = ''
            tagged_products_result.forEach(tagged_product_item => {
                if(Object.keys(tagged_product_item)[0] == media.id) {

                    loop_content += '<div class="row tagged-product-details-list"><div class="col-sm-2 col-md-2 col-lg-2 mt-1 tag-number-list"><div class="cTG-tag"><span>' + Object.values(tagged_product_item)[0].tag_number + '</span></div><form name="delete_product_form_random" id="delete_product_form_random" method="post" role="form"><input type="hidden" name="token" value="' + token + '" required="required" /><input type="hidden" name="request_type" value="delete_shopify_tag" /><input type="hidden" name="link_id" value="' + link_id + '"><input type="hidden" name="post_id" value="'+ media.id +'" /><input type="hidden" name="shopify_product_vendor" value="'+ Object.values(tagged_product_item)[0].shopify_product_vendor +'" /><input type="hidden" name="variant_id" value="'+ Object.values(tagged_product_item)[0].shopify_product_variant_id +'" /><div class="trash-cTG-tag"><button type="submit"><i class="fa fa-trash" aria-hidden="true"></i></button></div></form></div><div class="col-sm-2 col-md-2 col-lg-2 mt-1">' + '<img src="' + Object.values(tagged_product_item)[0].shopify_product_image_url + '" width="50" /></div><div class="col-sm-8 col-md-8 col-lg-8 mt-1 tag-product-detail-list"><p>' + Object.values(tagged_product_item)[0].shopify_product_title + '</p><p>' + Object.values(tagged_product_item)[0].shopify_product_price + '</p></div></div>';
                }
            })

            let content_per_post = `
                ${tagged_product_key_arr.indexOf(media.id) !== -1 ? `
                    <div>
                        <div class="row">
                            <h6>Shopify Products</h6>
                        </div>
                        ${loop_content}
                        <div class="tag-another-product" data-toggle="modal" data-target="#shopify_products_lists" id="tag_another_product">
                            <p>+ Tag Another Product</p>
                        </div>
                    </div>
                    `: 
                    `<div class="link-description">
                        <div class="row">
                            <h6>Link Options</h6>
                            <small>Choose to either link your image to external websites or link to Marketplace products</small>
                        </div>

                        <div class="row">
                            <div class="col-sm-1 col-md-1 col-lg-1 mt-1">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="col-sm-10 col-md-10 col-lg-10 mt-1">
                                <p>Link to External Websites</p>
                                <small>Link your image to external websites</small>
                            </div>

                        </div>

                        <div class="row" data-toggle="modal" data-target="#shopify_products_lists" id="shopify_products_details">
                            <div class="col-sm-1 col-md-1 col-lg-1 mt-1">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div class="col-sm-10 col-md-10 col-lg-10 mt-1">
                                <p>Link to Marketplace Products</p>
                                <small>Tag Marketplace products in your image</small>
                            </div>

                        </div>
                    </div>
                `}

            `;

            $('.dynamic-content').html(content_per_post);

            $('.tag-number-list').mouseenter(function(e) {
                $('.cTG-tag, .trash-cTG-tag').toggle();
            });

            $('.tag-number-list').mouseleave(function(e) {
                $('.cTG-tag, .trash-cTG-tag').toggle();
            });

           
                
            $('.trash-cTG-tag').on('click', function(event) {

                event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirm'
                }).then((result) => {
                    if (result.isConfirmed) {

                        let data = $('form[name="delete_product_form_random"]').serialize();

                        $.ajax({
                            type: 'POST',
                            url: 'link-ajax',
                            data: data,
                            success: (data) => {
                                let notification_container = $(event.currentTarget).find('.notification-container');
                                notification_container.html('');

                                if (data.status == 'error') {
                                    display_notifications(data.message, 'error', notification_container);
                                }

                                else if(data.status == 'success') {

                                    Swal.fire(
                                        'Deleted!',
                                        'This product has been removed.',
                                        'success'
                                    )
                                    
                                    fade_out_redirect({ url: data.details.url, full: true });
                                    
                                }
                            },
                            dataType: 'json'
                        });
                    }
                });

            });

            

            $('#tag_another_product').click(function(e) {
                
                $('.more-link-modal').css('transform', 'translateX(-30%)');
                $('.shopify-products-lists').css('transform', 'translateX(78%)');
            })

            $('#shopify_products_details').click(function(e) {

                $('.more-link-modal').css('transform', 'translateX(-30%)');
                $('.shopify-products-lists').css('transform', 'translateX(78%)');
            })

        });

        $("#save").click(function(e) {

            $('#edit_instagram_profile')
            .ajaxForm({

                type: 'POST',
                url : 'link-ajax',
                dataType : 'json',
                success: (data) => {
                
                    if(data.status == 'error') {

                        let errormsg = "Your entered link has an invalid format!";
                        $('input[name="post_connected_link"]').val('');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: errormsg,
                        })
                    }

                    else if(data.status == 'success') {

                        /* Fade out refresh */
                        fade_out_redirect({ url: data.details.url, full: true });
                    }
                }
            });
        });

        $("#remove").click(function(e) {
            $('input[name="remove_status"]').val('remove');
            $('#edit_instagram_profile')
            .ajaxForm({

                type: 'POST',
                url : 'link-ajax',
                dataType : 'json',

                success: (data) => {
                
                    if(data.status == 'error') {

                        let errormsg = "Your entered link has an invalid format!";
                        $('input[name="post_connected_link"]').val('');

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: errormsg,
                        })
                    }

                    else if(data.status == 'success') {

                        /* Fade out refresh */
                        fade_out_redirect({ url: data.details.url, full: true });
                    }
                }
            });
        });

    });

    $("#save").click(function(e) {
   
        $('#edit_instagram_profile')
        .ajaxForm({

            type: 'POST',
            url : 'link-ajax',
            dataType : 'json',
            success: (data) => {
            
                if(data.status == 'error') {

                    let errormsg = "Your entered link has an invalid format!";
                    $('input[name="post_connected_link"]').val('');

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errormsg,
                    })
                }

                else if(data.status == 'success') {

                    /* Fade out refresh */
                    fade_out_redirect({ url: data.details.url, full: true });
                }
            }
        });
    });

    $("#remove").click(function(e) {
        
        let link_id = $('input[name="link_id"]').val();
        $('input[name="remove_status"]').val('remove');
        $('#edit_instagram_profile')
        .ajaxForm({

            type: 'POST',
            url : 'link-ajax',
            dataType : 'json',

            success: (data) => {
            
                if(data.status == 'error') {

                    let errormsg = "Your entered link has an invalid format!";
                    $('input[name="post_connected_link"]').val('');

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errormsg,
                    })
                }

                else if(data.status == 'success') {

                    /* Fade out refresh */
                    fade_out_redirect({ url: data.details.url, full: true });
                    $('#link_expanded_content' + link_id).attr('class', 'mt-3 collapse-show');
                }
            }
        });
    });

    $('input[name="shopify_store_name"]').on('input', function(e) {
        let shop_name = e.target.value;
        if(shop_name) {
            $('#shopify_connect').removeAttr('disabled');
        } else {
            $('#shopify_connect').attr('disabled', true);
        }
    })

    $("#shopify_connect").click(function(e) {

        let path = "<?= SITE_URL ?>";
        let shop_name = $('input[name="shopify_store_name"]').val();
        let redirect_path = path + "shopify_connect?shop=" + shop_name;

        if(shop_name) {

            $('input[name="shopify_store_name"]').val('');
            window.location.href = redirect_path;
            // window.open(redirect_path);
        }

    });

    $('.more-link').click(function(e) {

        let post_id = $('input[name="post_connected_link_id"]').val();
        localStorage.setItem('tag_post_id', post_id);
        localStorage.setItem('taggedFlag', false);
    });

    $('.tag-number-list').mouseenter(function(e) {
        $('.cTG-tag, .trash-cTG-tag').toggle();
    });

    $('.tag-number-list').mouseleave(function(e) {
        $('.cTG-tag, .trash-cTG-tag').toggle();
    });

    $('.trash-cTG-tag').on('click', function(event) {

        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confirm'
        }).then((result) => {
            if (result.isConfirmed) {

                let data = $('form[name="delete_product_form"]').serialize();

                $.ajax({
                    type: 'POST',
                    url: 'link-ajax',
                    data: data,
                    success: (data) => {
                        let notification_container = $(event.currentTarget).find('.notification-container');
                        notification_container.html('');

                        if (data.status == 'error') {
                            display_notifications(data.message, 'error', notification_container);
                        }

                        else if(data.status == 'success') {

                            Swal.fire(
                                'Deleted!',
                                'This products has been removed.',
                                'success'
                            )
                            
                            fade_out_redirect({ url: data.details.url, full: true });

                        }
                    },
                    dataType: 'json'
                });
            }
        });

    });

    $('#close_shopify_products_lists').on('click', function(e) {
        $('.more-link-modal').css('transform', 'translateX(0%)');

    })

    $('#shopify_products_details').click(function(e) {

        $('.more-link-modal').css('transform', 'translateX(-30%)');
        $('.shopify-products-lists').css('transform', 'translateX(78%)');
    })

    $('#tag_another_product').click(function(e) {
        
        $('.more-link-modal').css('transform', 'translateX(-30%)');
        $('.shopify-products-lists').css('transform', 'translateX(78%)');
    })

    $('.select-different-prouct').click(function(e) {

        $('.more-link-modal').css('transform', 'translateX(-30%)');
        $('.shopify-products-lists').css('transform', 'translateX(78%)');
    })

    let shopify_products_result = '<?= $data->shopify_products_result?>';
    let products = ''
    if(shopify_products_result) {
        console.log("yes");
        products = JSON.parse(<?= $data->shopify_products_result?>).products;
    }

    $('#product_full_image').click(function(e) {

        let margin_left = $('.more-link-modal').css('margin-left');
        margin_left = margin_left.replace('px', '');
        margin_left = parseFloat(margin_left);

        if(margin_left != 0) {

            localStorage.setItem('margin_left', margin_left);
        }
        var saved_margin_left = localStorage.getItem('margin_left');
        let left_pos = e.clientX - saved_margin_left - 35;
        let top_pos = e.clientY - 285 * $(window).height() / 937;

        let taggedFlag = JSON.parse(localStorage.getItem('taggedFlag')); 
        if(taggedFlag) {

            $('.cTG-tag').css({
                    "position": 'absolute',
                    "left": left_pos + 'px',
                    "top": top_pos + 'px'
                })
        }

    })

    $('.radio-select-container input[type="radio"]').click(function(e) {

        localStorage.setItem('taggedFlag', true);
        let val = $(this).val();
        let target_product_id = val.split('_')[1];
        let product = products.filter( function(product) {
            return product.id == target_product_id;
        })[0];

        $('#close_shopify_products_lists').trigger('click');
        $('.more-link-modal').css('transform', 'translateX(0%)');
        let product_option = '';
        // console.log("wwww", product.options[0]);

        // return;

        product.options[0].values.forEach(option => {
            product_option  += `<option>${option}</option>`;
        });
        let price = '';
        if(product.options[0].name != 'Title') {

            let price_arr = [];
            product.variants.forEach(variant => {
                let price = parseFloat(variant.price);
                price_arr.push(price)
            })
            var max_price = price_arr.reduce(function(a, b) {
                return Math.max(a, b);
            });
            var min_price = price_arr.reduce(function(a, b) {
                return Math.min(a, b);
            });
            price = '$' + max_price + ' - ' + '$' + min_price;
        } else {
            price = '$' + product.variants[0].price;
        }

        let token = '<?= \Altum\Middlewares\Csrf::get() ?>';
        let link_id = '<?= $row->link_id ?>';
        let tag_content = `
            <div class="selected-product-container">
                <div>
                    <h6>Tag Marketplace Product</h6>
                    <small>Click on image to adjust location of the tag.</small>
                </div>
                <div style="margin-top:10px;">
                    <p>Product</p>
                </div>
                <div class="row" style="border: ridge;">
                    <div class="col-sm-3 col-md-3 col-lg-3 mt-1">
                        <img src="${product.image.src}" width="50">
                    </div>
                    <div class="col-sm-9 col-md-9 col-lg-9 mt-1">
                        <p>${product.title}</p>
                        <small>${price}</small>
                    </div>
                </div>
                <div class="select-different-prouct">
                    <a href="#" data-toggle="modal" data-target="#shopify_products_lists">Choose different product</a>
                </div>
                <div class="product-options-container">
                    <p>Product Options</p>
                    <small>Select the options of products</small><br />
                    ${product.options[0].values[0] == 'Default Title' ? '<select class="form-control" name="product_options" disabled><option>No Options Found</option></select>' : `<span>${product.options[0].name}</span><select class="form-control" name="product_options">${product_option}</select>`}
                </div>
                <form name="tag_product_form" id="tag_product_form" method="post" role="form">
                    <input type="hidden" name="token" value="${token}" required="required" />
                    <input type="hidden" name="request_type" value="update" />
                    <input type="hidden" name="type" value="biolink" />
                    <input type="hidden" name="subtype" value="shopify_products" />
                    <input type="hidden" name="shopify_product_vendor" value="${product.vendor}">
                    <input type="hidden" name="link_id" value="${link_id}" />
                    <input type="hidden" name="shopify_product_id" value="${product.id}" />
                    <input type="hidden" name="shopify_product_url" value="${product.handle}" />
                    <input type="hidden" name="shopify_product_image_url" value="${product.image.src}" />
                    <input type="hidden" name="shopify_product_title" value="${product.title}" />
                    <input type="hidden" name="shopify_product_price" value="" />

                    <input type="hidden" name="shopify_product_option_key" value="${product.options[0].name}" />
                    <input type="hidden" name="shopify_product_option_value" value="" />
                    <input type="hidden" name="shopify_product_variant_id" value="" />
                    <input type="hidden" name="tag_post_id" value="">
                    <input type="hidden" name="tag_number" value="">
                    <input type="hidden" name="tag_position" value="" />
                    <div class="tag-button">
                        <button type="submit" class="btn btn-primary">Tag Product</button>
                    </div>
                </form>

            </div>

        `;

        $('.dynamic-content').html(tag_content);

        $('.select-different-prouct').click(function(e) {
            
            $('.more-link-modal').css('transform', 'translateX(-30%)');
            $('.shopify-products-lists').css('transform', 'translateX(78%)');
        });

        if($('.cTG-tag')) {
            $('.cTG-tag').remove();
        }
        let tag_div = $('<div class="cTG-tag">')
            .css({
                "position": 'absolute',
                "left": '198px',
                "top": '193px'
            })
            .appendTo($('#product_full_image_container'));

        let tag_span =  $('<span>')
            .html('<?= isset($new_tagged_num) ? $new_tagged_num : 1 ?>')
            .appendTo($('.cTG-tag'));
        
        $('.tag-button').on('click', function(e) {

            let product_option_val = $('select[name="product_options"]').val();
            let submit_price = '';
            let shopify_product_variant_id = '';
            if(product_option_val === 'No Options Found') {
                submit_price = product.variants[0].price;
                shopify_product_variant_id = product.variants[0].id
            } else {
                let product_variant = product.variants.filter(variant => variant.title == product_option_val)[0];
                submit_price = product_variant.price;
                shopify_product_variant_id = product_variant.id;
            }

            let tag_position_left = $('.cTG-tag').css('left');
            tag_position_left = tag_position_left.replace("px", "");
            tag_position_left = parseFloat(tag_position_left) + 38;
            tag_position_left += 'px';

            let tag_position_top = $('.cTG-tag').css('top');
            let tag_position = tag_position_left + '_' + tag_position_top;
            let tag_number = $('.cTG-tag span').text();
            let tag_post_id = localStorage.getItem('tag_post_id');

            $('input[name="shopify_product_option_value"]').val(product_option_val);
            $('input[name="tag_number"]').val(tag_number);
            $('input[name="tag_position"]').val(tag_position);
            $('input[name="tag_post_id"]').val(tag_post_id);
            $('input[name="shopify_product_price"]').val(submit_price);
            $('input[name="shopify_product_variant_id"]').val(shopify_product_variant_id);

            $('#tag_product_form')
                .ajaxForm({

                    type: 'POST',
                    url : 'link-ajax',
                    dataType : 'json',

                    success: (data) => {
                    
                        if(data.status == 'error') {

                            let errormsg = "Your entered link has an invalid format!"
                            swal(errormsg, "", "error", {
                                    timer: 2000
                            });
                            $('input[name="post_connected_link"]').val('');
                        }

                        else if(data.status == 'success') {

                            /* Fade out refresh */
                            fade_out_redirect({ url: data.details.url, full: true });
                            $('#link_expanded_content' + link_id).attr('class', 'mt-3 collapse-show');
                        }
                    }
                });

        });

    })

    $('#etsy_connect').click(function(e) {

        console.log("checking")
        let path = "<?= SITE_URL ?>";
        let redirect_path = path + "etsy_connect";
        window.location.href = redirect_path;
    })

</script>