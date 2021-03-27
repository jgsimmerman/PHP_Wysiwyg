<?php defined('ALTUMCODE') || die() ?>

<div class="my-3">
    
    <div class="instagram-body">
        <div class="container">

            <div class="intro-instagram-image">
                <h6>Tap on any image to learn more</h6>
            </div>

            <?php
                $marketplace = $data->marketplace;
                $tagged_product_key_arr = array();
                $total_carts = 0;
                $medias = $data->link->medias;
                $tagged_products_result = json_decode($data->tagged_products_result);
                if($tagged_products_result) {

                    foreach($tagged_products_result as $key => $tagged_product) {
                        
                        array_push($tagged_product_key_arr, key($tagged_product));
                        $tagged_product_obj = get_object_vars($tagged_product);
                        $tagged_product = $tagged_product_obj[key($tagged_product_obj)];
                        $total_carts += intval($tagged_product->num_add_to_cart_products);
                    }
                }

                $cnt = 0;
                $maxcol = 0;
                
                while ($cnt < count($medias)) {
            ?>
                    <?php if(($maxcol == 0 && $medias[$cnt]->link) || ($maxcol == 0 && in_array($medias[$cnt]->id, $tagged_product_key_arr))) : ?>
                        <div class="row row-container" style="display: flex; align-items: center;">
                    <?php endif; ?>
                    <?php if(($medias[$cnt]->media_url && $medias[$cnt]->link) || in_array($medias[$cnt]->id, $tagged_product_key_arr)) :?>
                        <div class="col-sm-4 col-xs-4 col-md-4 col-lg-4" style="width:33%;">
                            <div id="<?= 'container_' . $medias[$cnt]->id ?>">
        
                                <?php if(in_array($medias[$cnt]->id, $tagged_product_key_arr)): ?>

                                    <?php
                                        foreach($tagged_products_result as $key => $tagged_product) {
                                            if($medias[$cnt]->id === key($tagged_product)) {
                                                $key_tagged_product = key($tagged_product);
                                                $tagged_product_val = get_object_vars($tagged_product)[$key_tagged_product];
                                                $tagged_product_url = $tagged_product_val->shopify_product_url;
                                                if($marketplace === 'shopify') {
                                                ?>
                                                    <a href="<?= "https://".$tagged_product_val->shopify_product_vendor .'.myshopify.com/' . 'products/'.$tagged_product_url?>">
                                                        <img src="<?= $medias[$cnt]->media_url ?>" class="image-iframe-responsive" id="<?= $medias[$cnt]->id ?>" width="240" />
                                                    </a>
                                                <?php } elseif($marketplace === 'etsy') { ?>
                                                    <a href="<?= $tagged_product_url?>">
                                                        <img src="<?= $medias[$cnt]->media_url ?>" class="image-iframe-responsive" id="<?= $medias[$cnt]->id ?>" width="240" />
                                                    </a>
                                            <?php 
                                                }
                                            }
                                        }
                                            ?>
             
                                    <?php else: ?>
                                        <a href="<?= $medias[$cnt]->link?>">
                                            <img src="<?= $medias[$cnt]->media_url ?>" class="image-iframe-responsive" id="<?= $medias[$cnt]->id ?>" width="240" />
                                        </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php $maxcol++; endif; ?>
                    <?php if($maxcol == 3) : ?>
                        </div>
                    <?php $maxcol = 0; endif; ?>

            <?php $cnt++; } ?>

            <div class="add-to-cart-background">
            </div>

        </div>
    </div>
</div>

<div class="shopcart-container" id="shopcart-container">
    <div class="cart-header">
        <h5>Shopping Cart</h5>
        <div><i class="fa fa-times"></i></div>
    </div>
    <div class="cart-body">
        <div class="container cart-container">
            <?php
            if($tagged_products_result):
                foreach($tagged_products_result as $key => $tagged_product):
                
                    $tagged_product_obj = get_object_vars($tagged_product);
                    $tagged_product = $tagged_product_obj[key($tagged_product_obj)];
                    if($tagged_product->num_add_to_cart_products != 0):
                ?>
                        <div class="row">
                            <div class="col-sm-3 col-md-3 col-lg-3 mt-1">
                                <img src="<?= $tagged_product->shopify_product_image_url ?>" width="100" />
                            </div>
                            <div class="col-sm-9 col-md-9 col-lg-9 mt-1 cart-product-detail">
                                <h6><?= $tagged_product->shopify_product_title ?></h6>
                                <p><?= $tagged_product->shopify_product_option_value?></p>
                            </div>

                            <div class="quantity-field quantity-field<?= $tagged_product->shopify_product_variant_id?>" >
                                <form name="num_cart_product_from" id="num_cart_product_from" method="post" role="form">
                                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
                                    <input type="hidden" name="request_type" value="update" />
                                    <input type="hidden" name="type" value="biolink" />
                                    <input type="hidden" name="subtype" value="num_cart_products" />
                                    <input type="hidden" name="post_id" value="<?= key($tagged_product_obj) ?>" />
                                    <input type="hidden" name="shopify_product_vendor" value="<?= $tagged_product->shopify_product_vendor?>" />
                                    <input type="hidden" name="shopify_link_id" value="<?= $data->link->link_id?>" />
                                    <input type="hidden" name="shopify_product_id" value="<?= $tagged_product->shopify_product_id ?>" />
                                    <input type="hidden" name="shopify_product_price" value="<?= $tagged_product->shopify_product_price ?>" />
                                    <input type="hidden" name="shopify_product_option_key" value="<?= $tagged_product->shopify_product_option_key ?>" />
                                    <input type="hidden" name="shopify_product_option_value" value="<?= $tagged_product->shopify_product_option_value?>">
                                    <input type="hidden" name="shopify_product_variant_id" value="<?= $tagged_product->shopify_product_variant_id?>">
                                    <input type="hidden" name="num_add_to_cart_products" value="<?= $tagged_product->num_add_to_cart_products?>">

                                    <button class="value-button decrease-button" onclick="decreaseValue(this)" title="Azalt">-</button>
                                    <div class="number">
                                        <?= $tagged_product->num_add_to_cart_products?>
                                    </div>
                                    <button class="value-button increase-button" onclick="increaseValue(this, 20)" title="Arrtır">+</button>

                                </form>
                            </div>
                            <div class="total-price total-price<?= key($tagged_product_obj)?><?= $tagged_product->shopify_product_variant_id?>">
                                <?php
                                    $price_per_product = floatval($tagged_product->shopify_product_price);
                                    $num_add_to_cart_products = floatval($tagged_product->num_add_to_cart_products);
                                    $total_price = $price_per_product * $num_add_to_cart_products;
                                ?>
                                <p>$<?= $total_price?><p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="btn btn-primary continue-checkout-button">Continue</button>
    </div>
</div>

<?php if($total_carts > 0): ?>
    <div class="shopcart-button" role="button">
        <span class="shopcart-button-count"><?= $total_carts?></span>
        <div class="shopcart-button-icon">
            <i class="fa fa-shopping-cart"></i>
        </div>
    </div>
<?php endif; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>

    let medias = JSON.parse(JSON.stringify(<?= json_encode($medias) ?>));
    var tagged_products_result = JSON.parse(JSON.stringify(<?= $data->tagged_products_result ? $data->tagged_products_result : json_encode($data->tagged_products_result) ?>));
    // console.log("product results:", tagged_products_result);

    $(".instagram-body #add_to_cart_modal").on("click", "img", function(e) {

        let post_id = e.target.id;
        let media = medias.filter(media => media.id == post_id)[0];
        let tag_points_content = '';
        let tag_product_image_content = '';
        let form_content1 = '';
        let form_content2 = '';

        tagged_products_result.forEach(tagged_product_item => {
            if(Object.keys(tagged_product_item)[0] == media.id) {

                let tagged_product = Object.values(tagged_product_item)[0];
                let tag_position = tagged_product.tag_position;
                let left_pos = tag_position.split('_')[0];
                let left_right = tag_position.split('_')[1]
                tag_points_content += '<div class="bio-cTG-tag" style="position:absolute; left:' + left_pos + '; top:' + left_right + '" data-id="' + tagged_product.shopify_product_variant_id + '"><span>' + tagged_product.tag_number + '</span></div>';

                tag_product_image_content += '<img class = "bio-cTG-img" src="' + tagged_product.shopify_product_image_url + '" width="70" data-id="' + tagged_product.shopify_product_variant_id + '" />';
            }
        });

        for(let i = 0; i < tagged_products_result.length; i++) {

            if(Object.keys(tagged_products_result[i])[0] == media.id) {
                let tagged_product = Object.values(tagged_products_result[i])[0];

                form_content1 += '<h5>' + tagged_product.shopify_product_title + '</h5>' + '<p>$' + tagged_product.shopify_product_price + '</p>' + '<h6>' + tagged_product.shopify_product_option_key + '</h6>' + '<input type="radio" name="shopify_product_option_value" value="' + tagged_product.shopify_product_option_value + '" checked>' + '<label for="shopify_product_option_value">' + tagged_product.shopify_product_option_value + '</label></br><h6>Product Description</h6>';

                form_content2 += '<input type="hidden" name="shopify_product_vendor" value="' + tagged_product.shopify_product_vendor + '" /><input type="hidden" name="shopify_product_id" value="' + tagged_product.shopify_product_id + '" /><input type="hidden" name="shopify_product_variant_id" value="' + tagged_product.shopify_product_variant_id + '" /><input type="hidden" name="shopify_product_option_key" value="' + tagged_product.shopify_product_option_key + '" />';

                break;
            }
        }

        let token = '<?= \Altum\Middlewares\Csrf::get() ?>';
        let cart_content = `
            <div class="container">
                <div class="row">
                    <div class="col-sm-7 col-md-7 col-lg-7 mt-1 bio-tag-list-img">
                        <img src="${media.media_url}" width="400" />
                        ${tag_points_content}
                    </div>
                    <div class="col-sm-1 col-md-1 col-lg-1 mt-1 product-thumbnail">
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 mt-1">
                        <div class="cart-product-list">
                            <form name="cart_product_from" id="cart_product_from" method="post" role="form">
                                ${form_content1}

                                <input type="hidden" name="token" value="${token}" required="required" />
                                <input type="hidden" name="request_type" value="update" />
                                <input type="hidden" name="type" value="biolink" />
                                <input type="hidden" name="subtype" value="cart_products" />
                                <input type="hidden" name="post_id" value="${media.id}" />
                                <input type="hidden" name="shopify_link_id" value="<?= $data->link->link_id?>" />
                                ${form_content2}
        
                                <div class="add-to-cart-submit">
                                    <button type="submit" class="btn btn-dark">Add to Cart</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="close-add-to-cart-container"><i class="fa fa-times"></i></div>
                </div>
            </div>
        `;

        $('.add-to-cart-container').html(cart_content);
        $('.product-thumbnail').html(tag_product_image_content);

        $('.product-thumbnail img:first-child').addClass('dynamic-thumbnail-cart-img');
        $('.bio-tag-list-img').find('div:first').addClass('dynamic-selected-cart-tag');

        $('.add-to-cart-container').show();
        $('.add-to-cart-background').show();

        $('.close-add-to-cart-container').on('click', function(e) {
            $('.add-to-cart-background').hide();
            $('.add-to-cart-container').hide();
        });

        function add_form_content(id) {

            let variant_id = id;
            let dynamic_cart_form_content = '';
            tagged_products_result.forEach(tagged_product_item => {
                let tagged_product = Object.values(tagged_product_item)[0];
                if(tagged_product.shopify_product_variant_id == variant_id) {

                    dynamic_cart_form_content = `

                        <form name="cart_product_from" id="cart_product_from" method="post" role="form">
                            <h5>${tagged_product.shopify_product_title}</h5>
                            <p>$${tagged_product.shopify_product_price}</p>
                            <h6>${tagged_product.shopify_product_option_key}</h6>
                            <input type="radio" name="shopify_product_option_value" value="${tagged_product.shopify_product_option_value}" checked>
                            <label for="shopify_product_option_value">${tagged_product.shopify_product_option_value}</label>
                            </br>
                            <h6>Product Description</h6>

                            <input type="hidden" name="shopify_product_vendor" value="${tagged_product.shopify_product_vendor}" />
                            <input type="hidden" name="shopify_product_id" value="${tagged_product.shopify_product_id}" />
                            <input type="hidden" name="shopify_product_variant_id" value="${tagged_product.shopify_product_variant_id}" />
                            <input type="hidden" name="shopify_product_option_key" value="${tagged_product.shopify_product_option_key}" />

                            <input type="hidden" name="token" value="${token}" required="required" />
                            <input type="hidden" name="request_type" value="update" />
                            <input type="hidden" name="type" value="biolink" />
                            <input type="hidden" name="subtype" value="cart_products" />
                            <input type="hidden" name="post_id" value="${media.id}" />
                            <input type="hidden" name="shopify_link_id" value="<?= $data->link->link_id?>" />
    
                            <div class="add-to-cart-submit">
                                <button type="submit" class="btn btn-dark">Add to Cart</button>
                            </div>
                        </form>
                    `;
                }
            });
            $('.cart-product-list').empty();
            $('.cart-product-list').html(dynamic_cart_form_content);
            
            $('.add-to-cart-submit').on('click', function(e) {
        
                $('form[name="cart_product_from"]').on('submit', event => {

                    let base_url = '<?= url()?>';
                    $.ajax({
                        type: 'POST',
                        url: `${base_url}link-ajax`,
                        data: $(event.currentTarget).serialize(),
                        success: (data) => {
                            let notification_container = $(event.currentTarget).find('.notification-container');

                            if (data.status == 'error') {
                                notification_container.html('');

                                display_notifications(data.message, 'error', notification_container);
                            } else if (data.status == 'success') {

                                let add_to_cart_products = data.details.total_carts;
                                $('.shopcart-button-count').text(add_to_cart_products);
                                location.reload();
                                // display_notifications(data.message, 'success', notification_container);

                            }
                        },
                        dataType: 'json'
                    });

                    event.preventDefault();
                })

            });

        }

        function bio_cTG_clear() {
            
            $('.bio-cTG-tag').each(function(e) {
                $(this).removeClass('dynamic-selected-cart-tag');
                let _uid = $(this).data('id');
                $(`.bio-cTG-img[data-id="${_uid}"]`).removeClass('dynamic-thumbnail-cart-img');
            });
        }

        $('.bio-cTG-tag').on('click', function(e) {

            bio_cTG_clear();
            let _uid = $(this).data('id');
            $(this).addClass('dynamic-selected-cart-tag');
            $(`.bio-cTG-img[data-id="${_uid}"]`).addClass('dynamic-thumbnail-cart-img');
            add_form_content(_uid);
        })
        
        $('.bio-cTG-img').on('click', function(e) {

            bio_cTG_clear();
            let _uid = $(this).data('id');
            $(this).addClass('dynamic-thumbnail-cart-img');
            $(`.bio-cTG-tag[data-id="${_uid}"]`).addClass('dynamic-selected-cart-tag');
            add_form_content(_uid);
        })
        
        $('.add-to-cart-submit').on('click', function(e) {
        
            $('form[name="cart_product_from"]').on('submit', event => {

                let base_url = '<?= url()?>';
                $.ajax({
                    type: 'POST',
                    url: `${base_url}link-ajax`,
                    data: $(event.currentTarget).serialize(),
                    success: (data) => {
                        let notification_container = $(event.currentTarget).find('.notification-container');

                        if (data.status == 'error') {
                            notification_container.html('');

                            display_notifications(data.message, 'error', notification_container);
                        } else if (data.status == 'success') {

                            let add_to_cart_products = data.details.total_carts;
                            $('.shopcart-button-count').text(add_to_cart_products);
                            location.reload();
                            // display_notifications(data.message, 'success', notification_container);

                        }
                    },
                    dataType: 'json'
                });

                event.preventDefault();
            })
        });

    });

    $('.shopcart-button').on('click', function() {
        $('.shopcart-container').css('transform', 'translateX(0)')
    })

    $('.cart-header div').on('click', function(e) {
        $('.shopcart-container').css('transform', 'translateX(100%)')
    })


    $('.continue-checkout-button').on('click', function(e) {

        var order_info = [];
        var _vendor = '';
        var access_token = '<?= $data->access_token ?>';
        var total_price = 0;
        $('form[name="num_cart_product_from"]').each(function(e) {

            var _uvid = $(this).find('input[name="shopify_product_variant_id"]').val();
            var _vprice = $(this).find('input[name="num_add_to_cart_products"]').val();
            _vendor = $(this).find('input[name="shopify_product_vendor"]').val();

            var order_item = {};
            order_item[_uvid] = _vprice;
            order_info.push(order_item);
            total_price += parseFloat(_vprice);
        });

        if(Math.round(total_price) != 0) {

            let path = "<?= SITE_URL ?>";
            let post_url = 'https://' + _vendor + '.myshopify.com/admin/api/2020-10/checkouts.json';
            let redirect_path = path + "checkout_curl?post_url=" + post_url + "&access_token=" + access_token + "&order_info=" + encodeURIComponent(JSON.stringify(order_info));
            window.location.href = redirect_path;
        } 
    })


    function increaseValue(button, limit) {

        const numberInput = button.parentElement.querySelector('.number');
        var value = parseInt(numberInput.innerHTML, 10);
        if(isNaN(value)) value = 0;
        if(limit && value >= limit) return;
        numberInput.innerHTML = value + 1;
        $('input[name="num_add_to_cart_products"]').val(numberInput.innerHTML);

        $('form[name="num_cart_product_from"]').on('submit', event => {

            let base_url = '<?= url()?>';
            $.ajax({
                type: 'POST',
                url: `${base_url}link-ajax`,
                data: $(event.currentTarget).serialize(),
                success: (data) => {
                    let notification_container = $(event.currentTarget).find('.notification-container');

                    if (data.status == 'error') {
                        notification_container.html('');

                        display_notifications(data.message, 'error', notification_container);
                    } else if (data.status == 'success') {
                        $('.continue-checkout-button').attr('disabled', false);

                        let total_products = data.details.total_carts;
                        let sub_total_products = data.details.sub_total_carts;
                        let post_id = data.details.post_id;
                        let variant_id = data.details.variant_id;
                        let price_per_product = $('.quantity-field' + variant_id + ' input[name="shopify_product_price"]').val();
                        let total_price = parseFloat(price_per_product) * parseFloat(numberInput.innerHTML);
                        $('.total-price' + post_id + variant_id).text('$' + total_price);
                        $('.shopcart-button-count').text(total_products);

                        // display_notifications(data.message, 'success', notification_container);
                    }
                },
                dataType: 'json'
            });
            event.preventDefault();
        });
    }

    function decreaseValue(button) {

        const numberInput = button.parentElement.querySelector('.number');
        var value = parseInt(numberInput.innerHTML, 10);
        if(isNaN(value)) value = 0;  
        if(value < 1) return;
        numberInput.innerHTML = value - 1;
        $('input[name="num_add_to_cart_products"]').val(numberInput.innerHTML);

        $('form[name="num_cart_product_from"]').on('submit', event => {

            let base_url = '<?= url()?>';
            $.ajax({
                type: 'POST',
                url: `${base_url}link-ajax`,
                data: $(event.currentTarget).serialize(),
                success: (data) => {
                    let notification_container = $(event.currentTarget).find('.notification-container');

                    if (data.status == 'error') {

                        notification_container.html('');
                        display_notifications(data.message, 'error', notification_container);

                    } else if (data.status == 'success') {

                        let total_products = data.details.total_carts;
                        if(total_products == 0) {
                            
                            $('.continue-checkout-button').attr('disabled', true);
                            $('.shopcart-button').css('display', 'none');
                        }
                        let sub_total_products = data.details.sub_total_carts;
                        let post_id = data.details.post_id;
                        let variant_id = data.details.variant_id;
                        let price_per_product = $('.quantity-field' + variant_id + ' input[name="shopify_product_price"]').val();
                        let total_price = parseFloat(price_per_product) * parseFloat(numberInput.innerHTML);
                        $('.total-price' + post_id + variant_id).text('$' + total_price);
                        $('.shopcart-button-count').text(total_products);

                        // display_notifications(data.message, 'success', notification_container);
                    }
                },
                dataType: 'json'
            });
            event.preventDefault();
        })
    }

</script>