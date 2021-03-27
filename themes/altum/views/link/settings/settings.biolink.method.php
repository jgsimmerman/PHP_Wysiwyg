<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>

<div class="row">
    <div class="col-12 col-lg-7">

        <div class="d-flex justify-content-between">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] == 'settings') ? 'active' : null ?>" id="settings-tab" data-toggle="pill" href="#settings" role="tab" aria-controls="settings" aria-selected="true"><?= $this->language->link->header->settings_tab ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'links'? 'active' : null ?>" id="links-tab" data-toggle="pill" href="#links" role="tab" aria-controls="links" aria-selected="false"><?= $this->language->link->header->links_tab ?></a>
                </li>
            </ul>

            <div class="custom-notification-container">

                <?php switch ($data->tawkchat_result):
                        case "is_validate": ?>
                        <div class="chat-notification">
                            <i class="far fa-comment" aria-hidden="true" style="font-size: 2em;"></i>
                        </div>
                        <?php break; ?>
                    <?php case "not_validate": ?>
                        <div class="chat-notification">
                            <i class="far fa-comment" aria-hidden="true" style="font-size: 2em;"></i>
                            <div class="chat-notification-badge" data-toggle="tooltip" title="<?= $this->language->notification->chat_notification->tooltip->not_validate_widget_code?>"></div>
                        </div>
                        <?php break; ?>
                    <?php case 'empty_widget_code': ?>
                        <div class="chat-notification">
                            <i class="far fa-comment" aria-hidden="true" style="font-size: 2em;"></i>
                            <div class="chat-notification-badge" data-toggle="tooltip" title="<?= $this->language->notification->chat_notification->tooltip->empty_widget_code?>"></div>
                        </div>
                        <?php break; ?>
                    <?php default: ?>
                        <?php break; ?>
                <?php endswitch; ?>

                <div class="shopify-notification">
                    <i class="fab fa-shopify" aria-hidden="true" style="font-size: 2em;"></i>
                </div>
                <div class="instagram-notification">
                    <i class="fab fa-instagram" aria-hidden="true" style="font-size: 2em;"></i>
                </div>
            </div>
            
            <div class="dropdown">
                <button type="button" data-toggle="dropdown" class="btn btn-primary rounded-pill dropdown-toggle dropdown-toggle-simple"><i class="fa fa-fw fa-plus-circle"></i> <?= $this->language->project->links->create ?></button>

                <div class="dropdown-menu dropdown-menu-right">
                    <?php $biolink_link_types = require APP_PATH . 'includes/biolink_link_types_list.php'; ?>

                    <?php foreach($biolink_link_types as $key): ?>
                    <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_biolink_<?= $key ?>">
                        <i class="fa fa-fw fa-circle fa-sm mr-1" style="color: <?= $this->language->link->biolink->{$key}->color ?>"></i>

                        <?= $this->language->link->biolink->{$key}->name ?>
                    </a>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade <?= !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] == 'settings') ? 'show active' : null ?>" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                <div class="card border-0">
                    <div class="card-body">

                        <form name="update_biolink" action="" method="post" role="form" enctype="multipart/form-data">
                            <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" />
                            <input type="hidden" name="request_type" value="update" />
                            <input type="hidden" name="type" value="biolink" />
                            <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />

                            <div class="notification-container"></div>

                            <div class="form-group" style="display: <?= $data->link->active? 'block;' :'none;' ?>">
                                <label><i class="fa fa-fw fa-link fa-sm mr-1"></i> <?= $this->language->link->settings->url ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <?php if(count($data->domains)): ?>
                                            <select name="domain_id" class="appearance-none select-custom-altum form-control input-group-text">
                                                <option value="" <?= $data->link->domain ? 'selected="selected"' : null ?>><?= url() ?></option>
                                                <?php foreach($data->domains as $row): ?>
                                                    <option value="<?= $row->domain_id ?>" <?= $data->link->domain && $row->domain_id == $data->link->domain->domain_id ? 'selected="selected"' : null ?>><?= $row->url ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        <?php else: ?>
                                            <span class="input-group-text"><?= url() ?></span>
                                        <?php endif ?>
                                    </div>

                                    <input type="text" class="form-control" name="url" placeholder="<?= $this->language->link->settings->url_placeholder ?>" value="<?= $data->link->url ?>" />
                                </div>
                                <small class="text-muted"><?= $this->language->link->settings->url_help ?></small>
                            </div>

                            <?php

                            /* Check if we have avatar or we show the default */
                            if(empty($data->link->settings->image) || !file_exists(UPLOADS_PATH . 'avatars/' . $data->link->settings->image)) {
                                $data->link->settings->image_url = SITE_URL . ASSETS_URL_PATH . 'images/avatar_default.png';
                            } else {
                                $data->link->settings->image_url = SITE_URL . UPLOADS_URL_PATH . 'avatars/' . $data->link->settings->image;
                            }

                            ?>

                            <div class="form-group image_upload_sect">
                                <div class="m-1 d-flex flex-column align-items-center justify-content-center">
                                    <label aria-label="<?= $this->language->link->settings->image ?>" class="clickable" id="profile_avatar">
                                        <img id="image_file_preview" src="<?= $data->link->settings->image_url ?>" data-default-src="<?= $data->link->settings->image_url ?>" data-empty-src="<?= SITE_URL . ASSETS_URL_PATH . 'images/avatar_default.png' ?>" class="img-fluid link-image-preview" />
                                        <input id="upload_image" type="file" name="image" class="form-control" style="display:none;" />
                                        <input type="hidden" name="image_delete" value="0" class="form-control" />
                                    </label>
                                  
                                    <div id="image_file_status" <?= empty($data->link->settings->image) ? 'style="display: none;"' : null ?>>
                                        <button type="button" id="image_file_remove" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?= $this->language->link->settings->image_remove ?>"><i class="fa fa-fw fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>



                            <div class="form-group">
                                <label for="settings_title"><i class="fa fa-fw fa-heading fa-sm mr-1"></i> <?= $this->language->link->settings->title ?></label>
                                <input type="text" id="settings_title" name="title" class="form-control" value="<?= $data->link->settings->title ?>" required="required" />
                            </div>

                            <div class="form-group">
                                <label for="settings_description"><i class="fa fa-fw fa-pen-fancy fa-sm mr-1"></i> <?= $this->language->link->settings->description ?></label>
                                <input type="text" id="settings_description" name="description" class="form-control" value="<?= $data->link->settings->description ?>" />
                            </div>

                            <div class="form-group custom-control custom-switch mr-3 mb-3 <?= !$this->user->package_settings->verified ? 'container-disabled': null ?>">
                                <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="display_verified"
                                        name="display_verified"
                                    <?= !$this->user->package_settings->verified ? 'disabled="disabled"': null ?>
                                    <?= $data->link->settings->display_verified ? 'checked="true"' : null ?>
                                >
                                <label class="custom-control-label clickable" for="display_verified"><?= $this->language->link->settings->display_verified ?></label>
                            </div>

                            <div class="form-group">
                                <label for="settings_text_color"><i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= $this->language->link->settings->text_color ?></label>
                                <input type="hidden" id="settings_text_color" name="text_color" class="form-control" value="<?= $data->link->settings->text_color ?>" required="required" />
                                <div id="settings_text_color_pickr"></div>
                            </div>

                            <div class="form-group">
                                <label for="settings_background_type"><i class="fa fa-fw fa-fill fa-sm mr-1"></i> <?= $this->language->link->settings->background_type ?></label>
                                <select id="settings_background_type" name="background_type" class="form-control">
                                    <?php foreach($biolink_backgrounds as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= $data->link->settings->background_type == $key ? 'selected="selected"' : null?>><?= $this->language->link->settings->{'background_type_' . $key} ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div id="background_type_preset" class="row">
                                <?php foreach($biolink_backgrounds['preset'] as $key): ?>
                                    <label for="settings_background_type_preset_<?= $key ?>" class="m-0 col-4 mb-4">
                                        <input type="radio" name="background" value="<?= $key ?>" id="settings_background_type_preset_<?= $key ?>" class="d-none" <?= $data->link->settings->background == $key ? 'checked="checked"' : null ?>/>

                                        <div class="link-background-type-preset link-body-background-<?= $key ?>"></div>
                                    </label>
                                <?php endforeach ?>
                            </div>

                            <div class="<?= !$this->user->package_settings->custom_backgrounds ? 'container-disabled': null ?>">
                                <div id="background_type_gradient">
                                    <div class="form-group">
                                        <label for="settings_background_type_gradient_color_one"><?= $this->language->link->settings->background_type_gradient_color_one ?></label>
                                        <input type="hidden" id="settings_background_type_gradient_color_one" name="background[]" class="form-control" value="<?= $data->link->settings->background->color_one ?? '' ?>" />
                                        <div id="settings_background_type_gradient_color_one_pickr"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="settings_background_type_gradient_color_two"><?= $this->language->link->settings->background_type_gradient_color_two ?></label>
                                        <input type="hidden" id="settings_background_type_gradient_color_two" name="background[]" class="form-control" value="<?= $data->link->settings->background->color_two ?? '' ?>" />
                                        <div id="settings_background_type_gradient_color_two_pickr"></div>
                                    </div>
                                </div>

                                <div id="background_type_color">
                                    <div class="form-group">
                                        <label for="settings_background_type_color"><?= $this->language->link->settings->background_type_color ?></label>
                                        <input type="hidden" id="settings_background_type_color" name="background" class="form-control" 
                                        value="<?= is_string($data->link->settings->background) ? $data->link->settings->background: '' ?>" />
                                        <div id="settings_background_type_color_pickr"></div>
                                    </div>
                                </div>

                                <div id="background_type_image">
                                    <div class="form-group">
                                        <label><?= $this->language->link->settings->background_type_image ?></label>
                                        <?php if(is_string($data->link->settings->background) && file_exists(UPLOADS_PATH . 'backgrounds/' . $data->link->settings->background)): ?>
                                            <img id="background_type_image_preview" src="<?= SITE_URL . UPLOADS_URL_PATH . 'backgrounds/' . $data->link->settings->background ?>" data-default-src="<?= SITE_URL . UPLOADS_URL_PATH . 'backgrounds/' . $data->link->settings->background ?>" class="link-background-type-image img-fluid" />
                                        <?php endif ?>
                                        <input id="background_type_image_input" type="file" name="background" class="form-control" />
                                        <p id="background_type_image_status" style="display: none;">
                                            <span class="text-muted"><?= $this->language->link->settings->image_status ?></span>
                                            <span id="background_type_image_remove" class="clickable" data-toggle="tooltip" title="<?= $this->language->link->settings->image_remove ?>"><i class="fa fa-fw fa-trash-alt"></i></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Branding part -->

                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#analytics_container" aria-expanded="false" 
                            aria-controls="analytics_container">
                                <?= $this->language->link->settings->analytics_header ?>
                            </button>

                            <div class="collapse" id="analytics_container">
                                <div class="<?= !$this->user->package_settings->google_analytics ? 'container-disabled': null ?>">
                                    <div class="form-group">
                                        <label><i class="fab fa-fw fa-google fa-sm mr-1"></i> <?= $this->language->link->settings->google_analytics ?></label>
                                        <input id="google_analytics" type="text" class="form-control" name="google_analytics" value="<?= $data->link->settings->google_analytics ?? '' ?>" />
                                        <small class="text-muted"><?= $this->language->link->settings->google_analytics_help ?></small>
                                    </div>
                                </div>

                                <div class="<?= !$this->user->package_settings->facebook_pixel ? 'container-disabled': null ?>">
                                    <div class="form-group">
                                        <label><i class="fab fa-fw fa-facebook fa-sm mr-1"></i> <?= $this->language->link->settings->facebook_pixel ?></label>
                                        <input id="facebook_pixel" type="text" class="form-control" name="facebook_pixel" value="<?= $data->link->settings->facebook_pixel ?? '' ?>" />
                                        <small class="text-muted"><?= $this->language->link->settings->facebook_pixel_help ?></small>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#seo_container" aria-expanded="false" aria-controls="seo_container">
                                <?= $this->language->link->settings->seo_header ?>
                            </button>

                            <div class="collapse" id="seo_container">
                                <div class="<?= !$this->user->package_settings->seo ? 'container-disabled': null ?>">
                                    <div class="form-group">
                                        <label><i class="fa fa-fw fa-heading fa-sm mr-1"></i> <?= $this->language->link->settings->seo_title ?></label>
                                        <input id="seo_title" type="text" class="form-control" name="seo_title" value="<?= $data->link->settings->seo->title ?? '' ?>" />
                                        <small class="text-muted"><?= $this->language->link->settings->seo_title_help ?></small>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fa fa-fw fa-paragraph fa-sm mr-1"></i> <?= $this->language->link->settings->seo_meta_description ?></label>
                                        <input id="seo_meta_description" type="text" class="form-control" name="seo_meta_description" value="<?= $data->link->settings->seo->meta_description ?? '' ?>" />
                                        <small class="text-muted"><?= $this->language->link->settings->seo_meta_description_help ?></small>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#utm_container" aria-expanded="false" aria-controls="utm_container">
                                <?= $this->language->link->settings->utm_header ?>
                            </button>

                            <div class="collapse" id="utm_container">
                                <div class="<?= !$this->user->package_settings->utm ? 'container-disabled': null ?>">
                                    <small class="text-muted"><?= $this->language->link->settings->utm_campaign ?></small>

                                    <div class="form-group">
                                        <label><?= $this->language->link->settings->utm_medium ?></label>
                                        <input id="utm_medium" type="text" class="form-control" name="utm_medium" value="<?= $data->link->settings->utm->medium ?? '' ?>" />
                                    </div>

                                    <div class="form-group">
                                        <label><?= $this->language->link->settings->utm_source ?></label>
                                        <input id="utm_source" type="text" class="form-control" name="utm_source" value="<?= $data->link->settings->utm->source ?? '' ?>" />
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#socials_container" aria-expanded="false" aria-controls="socials_container">
                                <?= $this->language->link->settings->socials_header ?>
                            </button>

                            <div class="collapse" id="socials_container">
                                <div class="<?= !$this->user->package_settings->socials ? 'container-disabled': null ?>">
                                    <div class="form-group">
                                        <label for="settings_socials_color"><i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= $this->language->link->settings->socials_color ?></label>
                                        <input type="hidden" id="settings_socials_color" name="socials_color" class="form-control" value="<?= $data->link->settings->socials_color ?>" required="required" />
                                        <div id="settings_socials_color_pickr"></div>
                                    </div>

                                    <?php $biolink_socials = require APP_PATH . 'includes/biolink_socials.php'; ?>

                                    <?php foreach($biolink_socials as $key => $value): ?>
                                    <div class="form-group socials-container">
                                        <label><i class="<?= $this->language->link->settings->socials->{$key}->icon ?> fa-fw fa-sm mr-1"></i> <?= $this->language->link->settings->socials->{$key}->name ?></label>
                                        <input type="text" class="form-control" name="socials[<?= $key ?>]" placeholder="<?= $this->language->link->settings->socials->{$key}->placeholder ?>" value="<?= $data->link->settings->socials->{$key} ?? '' ?>" />
                                    </div>
                                    <?php endforeach ?>

                                </div>
                            </div>


                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#alt_socials_container" aria-expanded="false" aria-controls="alt_socials_container">
                                <?= $this->language->link->settings->alt_socials_header ?>
                            </button>

                            <div class="collapse" id="alt_socials_container">
                                <div class="<?= !$this->user->package_settings->socials ? 'container-disabled': null ?>">

                                    <?php $biolink_alt_socials = require APP_PATH . 'includes/biolink_alt_socials.php'; ?>

                                    <?php foreach($biolink_alt_socials as $key => $value): ?>
                                    
                                    <div class="form-group socials-container">
                                        <label>
                                            <?php if($this->language->link->settings->alt_socials->{$key}->icon): ?>
                                                <i class="<?= $this->language->link->settings->alt_socials->{$key}->icon ?> fa-fw fa-sm mr-1"></i>
                                                <?php else: ?>
                                                    <object data="<?= SITE_URL . ASSETS_URL_PATH . 'images/alt_socials/'. $key .'.svg' ?>" type="image/svg+xml"  width="14px"></object>
                                            <?php endif; ?>
                                            <?= $this->language->link->settings->alt_socials->{$key}->name ?></label>
                                        <input type="text" class="form-control" name="alt_socials[<?= $key ?>]" placeholder="<?= $this->language->link->settings->alt_socials->{$key}->placeholder ?>" value="<?= $data->link->settings->alt_socials->{$key} ?? '' ?>" />
                                    </div>
                                    <?php endforeach ?>

                                </div>
                            </div>


                            <button class="btn btn-block btn-gray-300 my-4" type="button" data-toggle="collapse" data-target="#fonts_container" aria-expanded="false" aria-controls="fonts_container">
                                <?= $this->language->link->settings->fonts_header ?>
                            </button>

                            <div class="collapse" id="fonts_container">
                                <div class="<?= !$this->user->package_settings->fonts ? 'container-disabled': null ?>">
                                    <?php $biolink_fonts = require APP_PATH . 'includes/biolink_fonts.php'; ?>

                                    <div class="form-group">
                                        <label for="settings_font"><i class="fa fa-fw fa-pen-nib fa-sm mr-1"></i> <?= $this->language->link->settings->font ?></label>
                                        <select id="settings_font" name="font" class="form-control">
                                            <?php foreach($biolink_fonts as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= $data->link->settings->font == $key ? 'selected="selected"' : null?>><?= $value['name'] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
    
            <div class="tab-pane fade <?= isset($_GET['tab']) && $_GET['tab'] == 'links'? 'show active' : null ?>" id="links" role="tabpanel" aria-labelledby="links-tab">

                <?php if($data->link_links_result->num_rows): ?>
                    <?php while($row = $data->link_links_result->fetch_object()): ?>
                    <?php $row->settings = json_decode($row->settings) ?>

                        <div class="link card border-0 <?= $row->is_enabled ? null : 'custom-row-inactive' ?> my-4" data-link-id="<?= $row->link_id ?>">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="custom-row-side-controller">
                                        <span data-toggle="tooltip" title="<?= $this->language->link->links->link_sort ?>">
                                            <i class="fa fa-fw fa-bars text-muted custom-row-side-controller-grab drag"></i>
                                        </span>
                                    </div>

                                    <div class="col-1 mr-2 p-0 d-none d-lg-block">

                                        <span class="fa-stack fa-1x" data-toggle="tooltip" title="<?= $this->language->link->biolink->{$row->subtype}->name ?>">
                                            <i class="fa fa-circle fa-stack-2x" style="color: <?= $this->language->link->biolink->{$row->subtype}->color ?>"></i>
                                            <i class="fas <?= $this->language->link->biolink->{$row->subtype}->icon ?> fa-stack-1x fa-inverse"></i>
                                        </span>

                                    </div>
 
                                    <div class="col-7 col-md-8">
                                        <div class="d-flex flex-column">
                                            <a  href="#"
                                                data-toggle="collapse"
                                                data-target="#link_expanded_content<?= $row->link_id ?>"
                                                aria-expanded="false"
                                                aria-controls="link_expanded_content<?= $row->link_id ?>"
                                            >
                                                <strong class="link_title"><?= in_array($row->subtype, ['spotify', 'youtube', 'youtube_live', 'vimeo', 'tiktok', 'twitch', 'soundcloud', 'text', 'mail', 'pdf', 'instagramfeed']) ? $this->language->link->biolink->{$row->subtype}->name : $row->settings->name ?></strong>
                                            </a>

                                            <span class="d-flex align-items-center">
                                                <?php if(!empty($row->location_url)): ?>
                                                <img src="https://www.google.com/s2/favicons?domain=<?= parse_url($row->location_url)['host'] ?>" class="img-fluid mr-1" />
                                                <span class="d-inline-block text-truncate">
                                                    <a href="<?= $row->location_url ?>" class="text-muted" title="<?= $row->location_url ?>"><?= $row->location_url ?></a>
                                                </span>
                                                <?php elseif(!empty($row->url)): ?>
                                                <img src="https://www.google.com/s2/favicons?domain=<?= url($row->url) ?>" class="img-fluid mr-1" />
                                                <span class="d-inline-block text-truncate">

                                                    <a href="<?= url($row->url) ?>" class="text-muted" title="<?= url($row->url) ?>"><?= url($row->url) ?></a>
                                                </span>
                                                <?php endif ?>
                                            </span>

                                        </div>
                                    </div>
                                
                                    <div class="col-3 col-md-2">
                                        <?php if(!in_array($row->subtype, ['mail', 'text', 'youtube', 'vimeo', 'tiktok', 'twitch', 'spotify', 'soundcloud', 'pdf', 'instagramfeed'])): ?>
                                        <a href="<?= url('link/' . $row->link_id . '/statistics') ?>">
                                            <span data-toggle="tooltip" title="<?= $this->language->project->links->clicks ?>" class="badge badge-primary"><i class="fa fa-fw fa-chart-bar mr-1"></i> <?= nr($row->clicks) ?></span>
                                        </a>
                                        <?php endif ?>
                                    </div>

                                    <div class="col-1 d-flex justify-content-end">
                                        <div class="dropdown">
                                            <a href="#" data-toggle="dropdown" class="text-secondary dropdown-toggle dropdown-toggle-simple">
                                                <i class="fa fa-ellipsis-v"></i>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="#"
                                                        class="dropdown-item"
                                                        data-toggle="collapse"
                                                        data-target="#link_expanded_content<?= $row->link_id ?>"
                                                        aria-expanded="false"
                                                        aria-controls="link_expanded_content<?= $row->link_id ?>"
                                                    >
                                                        <i class="fa fa-fw fa-pencil-alt"></i> <?= $this->language->global->edit ?>
                                                    </a>

                                                    <?php if($row->subtype !== 'mail'): ?>
                                                        <a href="<?= url('link/' . $row->link_id . '/statistics') ?>" class="dropdown-item"><i class="fa fa-fw fa-chart-bar"></i> <?= $this->language->link->statistics->link ?></a>
                                                    <?php endif ?>

                                                    <a href="#" class="dropdown-item" id="biolink_link_is_enabled_<?= $data->link->link_id ?>" data-row-id="<?= $row->link_id ?>">
                                                        <i class="fa fa-fw fa-bell"></i> <?= $this->language->link->links->switch_status ?>
                                                    </a>

                                                    <?php if($row->subtype == 'link'): ?>
                                                        <a href="#" class="dropdown-item" data-duplicate="true" data-row-id="<?= $row->link_id ?>"><i class="fa fa-fw fa-copy"></i> <?= $this->language->link->links->duplicate ?></a>
                                                    <?php endif ?>

                                                    <a href="#" class="dropdown-item" data-delete="<?= $this->language->global->info_message->confirm_delete ?>" data-row-id="<?= $row->link_id ?>"><i class="fa fa-fw fa-times"></i> <?= $this->language->global->delete ?></a>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($row->subtype == 'instagramfeed' && isset($_SESSION['save_status'])): ?>
                                    <div class="collapse mt-3 show" id="link_expanded_content<?= $row->link_id ?>" data-link-subtype="<?= $row->subtype ?>">
                                        <?php require THEME_PATH . 'views/link/settings/' . $row->subtype . '_form.settings.biolink.method.php' ?>
                                    </div>
                                    <?php $_SESSION['save_status'] = false; ?>
                                <?php else: ?>
                                    <div class="collapse mt-3" id="link_expanded_content<?= $row->link_id ?>" data-link-subtype="<?= $row->subtype ?>">
                                        <?php require THEME_PATH . 'views/link/settings/' . $row->subtype . '_form.settings.biolink.method.php' ?>
                                    </div>
                                <?php endif;?>

                            </div>
                        </div>

                    <?php endwhile ?>
                <?php else: ?>

                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <img src="<?= SITE_URL . ASSETS_URL_PATH . 'images/no_data.svg' ?>" class="col-10 col-md-6 col-lg-4 mb-3" alt="<?= $this->language->link->links->no_data ?>" />
                        <h2 class="h4 text-muted"><?= $this->language->link->links->no_data ?></h2>
                    </div>

                <?php endif ?>

            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5 mt-5 mt-lg-0 d-flex justify-content-center">
        <div class="biolink-preview-container">
            <div class="biolink-preview">
                <div class="biolink-preview-iframe-container">
       
                    <iframe id="biolink_preview_iframe" class="biolink-preview-iframe container-disabled-simple" src="<?= url($data->link->url . '?preview&link_id=' . $data->link->link_id) ?>" data-url-prepend="<?= url() ?>" data-url-append="<?= '?preview&link_id=' . $data->link->link_id ?>"></iframe>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php $html = ob_get_clean() ?>


<?php ob_start() ?>
<script src="<?= SITE_URL . ASSETS_URL_PATH . 'js/libraries/sortable.js' ?>"></script>
<script src="<?= SITE_URL . ASSETS_URL_PATH . 'js/libraries/pickr.min.js' ?>"></script>
<script>
    /* Settings Tab */
    /* Initiate the color picker */
    let pickr_options = {
        comparison: false,

        components: {
            preview: true,
            opacity: true,
            hue: true,
            comparison: false,
            interaction: {
                hex: true,
                rgba: false,
                hsla: false,
                hsva: false,
                cmyk: false,
                input: true,
                clear: false,
                save: true
            }
        }
    };


     $(document).ready(function(){
        
        $image_crop = $('#image_demo').croppie({
            enableExif: true,
            viewport: {
            width:200,
            height:200,
            type:'square' //circle
            },
            enableZoom: true,
            boundary:{
            width:300,
            height:300
            }
        });

        /*  Image Crop initial plan

        var container  = $("#profile_avatar");
        var file_input = $("#profile_avatar #upload_image");
 
        function FChange() {
 
            alert("f1 changed");
            
            // $(this).remove();
            // $("<input id='upload_image' type='file' name='image' class='form-control' style='display:none;' />").change(FChange).appendTo(container);
 
            $('#image_file_status').show();
 
            var reader = new FileReader();
            reader.onload = function (event) {
                $image_crop.croppie('bind', {
                    url: event.target.result
                    }).then(function(){
                        console.log('jQuery bind complete');
                    });
            }
            reader.readAsDataURL(this.files[0]);
            $('#uploadimageModal').modal('show');
 
        }
 
        file_input.change(FChange);
        
        */

        $('#upload_image').on('change', function(){

            $('#image_file_status').show();
            
            var reader = new FileReader();
            reader.onload = function (event) {
                $image_crop.croppie('bind', {
                    url: event.target.result
                    }).then(function(){
                        console.log('jQuery bind complete');
                    });
            }
            $('input[name="image_delete"]').val(false);
            reader.readAsDataURL(this.files[0]);
            $('#uploadimageModal').modal('show');
        });

    }); 

    function generate_image_preview(input) {


        if(input.files && input.files[0]) {
            let reader = new FileReader();

            reader.onload = event => {
                
                $('#image_file_preview').attr('src', event.target.result);
                $('#biolink_preview_iframe').contents().find('#image').attr('src', event.target.result).show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#image_file_input').on('change', event => {
        $('#image_file_status').show();

        $('[data-toggle="tooltip"]').tooltip();

        $('input[name="image_delete"]').val(false);

        generate_image_preview(event.currentTarget);
    });

    $('#image_file_remove').on('click', () => {

        let default_src = $('#image_file_preview').attr('data-default-src');
        let empty_src = $('#image_file_preview').attr('data-empty-src');

        /* Check if new avatar is selected and act accordingly */
        if($('#upload_image').get(0).files.length > 0) {

            /* Check if we had a non default image previously */
            if(default_src == empty_src) {

                $('#image_file_preview').attr('src', empty_src);
                $('#upload_image').replaceWith($('#upload_image').val('').clone(true));
                $('#biolink_preview_iframe').contents().find('#image').hide();
                $('input[name="image_delete"]').val(true);
                $('#image_file_status').hide();

            } else {

                $('#image_file_preview').attr('src', empty_src);
                $('#upload_image').replaceWith($('#upload_image').val('').clone(true));
                $('#biolink_preview_iframe').contents().find('#image').hide();
                $('input[name="image_delete"]').val(true);
                $('#image_file_status').hide();
            }

        } else {

            $('#image_file_preview').attr('src', empty_src);
            $('#biolink_preview_iframe').contents().find('#image').hide();
            $('input[name="image_delete"]').val(true);
            $('#image_file_status').hide();
        }

        let form = document.querySelector('form');
        let data = new FormData(form);
        let notification_container = $('.notification-container');

        $.ajax({
            type: 'POST',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            url: 'link-ajax',
            data: data,
            success: (data) => {

            },
            dataType: 'json'
        });

    });



    /* Preview handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#biolink_preview_iframe').contents().find('#title').text($(event.currentTarget).val());
    });

    $('#settings_description').on('change paste keyup', event => {
        $('#biolink_preview_iframe').contents().find('#description').text($(event.currentTarget).val());
    });
    $('#display_verified').on('change', event => {
        
        if($('#biolink_preview_iframe').contents().find('#switchTip')[0] === undefined) {
            $('#biolink_preview_iframe').contents().find('#switchTip1').toggle();
        } else {
            $('#biolink_preview_iframe').contents().find('#switchTip').toggle();
        }
        var el = $('#biolink_preview_iframe').contents().find('#switchTip')
    });


    /* Text Color Handler */
    let settings_text_color_pickr = Pickr.create({
        el: '#settings_text_color_pickr',
        default: $('#settings_text_color').val(),
        ...{
            comparison: false,

            components: {
                preview: true,
                opacity: false,
                hue: true,
                comparison: false,
                interaction: {
                    hex: true,
                    rgba: false,
                    hsla: false,
                    hsva: false,
                    cmyk: false,
                    input: true,
                    clear: false,
                    save: true
                }
            }
        }
    });

    settings_text_color_pickr.on('change', hsva => {
        $('#settings_text_color').val(hsva.toHEXA().toString());

        $('#biolink_preview_iframe').contents().find('header').css('color', hsva.toHEXA().toString());
        $('#biolink_preview_iframe').contents().find('#branding').css('color', hsva.toHEXA().toString());
    });

    /* Socials Color Handler */
    let settings_socials_color_pickr = Pickr.create({
        el: '#settings_socials_color_pickr',
        default: $('#settings_socials_color').val(),
        ...pickr_options
    });

    settings_socials_color_pickr.on('change', hsva => {
        $('#settings_socials_color').val(hsva.toHEXA().toString());
        $('#biolink_preview_iframe').contents().find('#socials a svg').css('color', hsva.toHEXA().toString());
        console.log("wooow: ", $('#biolink_preview_iframe').contents().find('#socials .alt_socials').contents());
        // $('#biolink_preview_iframe').contents().find('#socials .alt_socials').contents().find('svg').css('fill', hsva.toHEXA().toString());
    });

    /* Background Type Handler */
    let background_type_handler = () => {
        let type = $('#settings_background_type').find(':selected').val();

        /* Show only the active background type */
        $(`div[id="background_type_${type}"]`).show();
        $(`div[id="background_type_${type}"]`).find('[name^="background"]').removeAttr('disabled');

        /* Disable the other possible types so they dont get submitted */
        let background_type_containers = $(`div[id^="background_type_"]:not(div[id$="_${type}"])`);

        background_type_containers.hide();
        background_type_containers.find('[name^="background"]').attr('disabled', 'disabled');
    };

    background_type_handler();

    $('#settings_background_type').on('change', background_type_handler);

    /* Preset Baclground Preview */
    $('#background_type_preset input[name="background"]').on('change', event => {
        let value = $(event.currentTarget).val();

        $('#biolink_preview_iframe').contents().find('body').attr('class', `link-body link-body-background-${value}`).attr('style', '');
    });

    /* Gradient Background */
    let settings_background_type_gradient_color_one_pickr = Pickr.create({
        el: '#settings_background_type_gradient_color_one_pickr',
        default: $('#settings_background_type_gradient_color_one').val(),
        ...pickr_options
    });

    settings_background_type_gradient_color_one_pickr.on('change', hsva => {
        $('#settings_background_type_gradient_color_one').val(hsva.toHEXA().toString());

        let color_one = $('#settings_background_type_gradient_color_one').val();
        let color_two = $('#settings_background_type_gradient_color_two').val();

        $('#biolink_preview_iframe').contents().find('body').attr('class', 'link-body').attr('style', `background-image: linear-gradient(135deg, ${color_one} 10%, ${color_two} 100%);`);
    });

    let settings_background_type_gradient_color_two_pickr = Pickr.create({
        el: '#settings_background_type_gradient_color_two_pickr',
        default: $('#settings_background_type_gradient_color_two').val(),
        ...pickr_options
    });

    settings_background_type_gradient_color_two_pickr.on('change', hsva => {
        $('#settings_background_type_gradient_color_two').val(hsva.toHEXA().toString());

        let color_one = $('#settings_background_type_gradient_color_one').val();
        let color_two = $('#settings_background_type_gradient_color_two').val();

        $('#biolink_preview_iframe').contents().find('body').attr('class', 'link-body').attr('style', `background-image: linear-gradient(135deg, ${color_one} 10%, ${color_two} 100%);`);
    });

    /* Color Background */
    let settings_background_type_color_pickr = Pickr.create({
        el: '#settings_background_type_color_pickr',
        default: $('#settings_background_type_color').val(),
        ...pickr_options
    });

    settings_background_type_color_pickr.on('change', hsva => {
        $('#settings_background_type_color').val(hsva.toHEXA().toString());

        $('#biolink_preview_iframe').contents().find('body').attr('class', 'link-body').attr('style', `background: ${hsva.toHEXA().toString()};`);
    });

    /* Image Background */
    function generate_background_preview(input) {
        if(input.files && input.files[0]) {
            let reader = new FileReader();

            reader.onload = event => {
                $('#background_type_image_preview').attr('src', event.target.result);
                $('#biolink_preview_iframe').contents().find('body').attr('class', 'link-body').attr('style', `background: url(${event.target.result});`);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#background_type_image_input').on('change', event => {
        $('#background_type_image_status').show();

        generate_background_preview(event.currentTarget);
    });

    $('#background_type_image_remove').on('click', () => {
        $('#background_type_image_preview').attr('src', $('#background_type_image_preview').attr('data-default-src'));
        $('#biolink_preview_iframe').contents().find('body').attr('class', 'link-body').attr('style', `background: url(${$('#background_type_image_preview').attr('data-default-src')});`);

        $('#background_type_image_input').replaceWith($('#background_type_image_input').val('').clone(true));
        $('#background_type_image_status').hide();
    });

    /* Display branding switcher */
    $('#display_branding').on('change', event => {
        if($(event.currentTarget).is(':checked')) {
            $('#biolink_preview_iframe').contents().find('#branding').show();
        } else {
            $('#biolink_preview_iframe').contents().find('#branding').hide();
        }
    });

    /* Branding change */
    $('#branding_name').on('change paste keyup', event => {
        $('#biolink_preview_iframe').contents().find('#branding').text($(event.currentTarget).val());
    });

    $('#branding_url').on('change paste keyup', event => {
        $('#biolink_preview_iframe').contents().find('#branding').attr('src', ($(event.currentTarget).val()));
    });


    $(document).ready(function() {

        // function autoSave() {}

        $('.crop_image').on('click', function(event) {
            
            $image_crop.croppie('result', {
                type: 'canvas',
                size: 'viewport'
            }).then(function(response){

                $('#image_file_preview').attr('src', response);
                $('#biolink_preview_iframe').contents().find('#image').attr('src', response).show();
                $.ajax({
                    url:"app/controllers/upload.php",
                    type: "POST",
                    data:{"image": response},
                    success:function(data)
                    {
                        $('#uploadimageModal').modal('hide');
                    }
                });
            })

            setTimeout(() => {
                
                let form = document.querySelector('form');
                let data = new FormData(form);
                let notification_container = $('.notification-container');

                $.ajax({
                    type: 'POST',
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    cache: false,
                    url: 'link-ajax',
                    data: data,
                    success: (data) => {
    
                    },
                    dataType: 'json'
                });
            }, 1000);
            

        })
        $(".pcr-save").on('click', function() {

            let form = document.querySelector('form');
            let data = new FormData(form);
            let notification_container = $('.notification-container');

            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,
                success: (data) => {

                },
                dataType: 'json'
            });

        })
        $("form[name='update_biolink'] .form-group:not(.image_upload_sect, .socials-container), #display_branding, #background_type_preset").on('change', function() {

            let form = document.querySelector('form[name="update_biolink"]');
            let data = new FormData(form);
            let notification_container = $('.notification-container');
            let url = $("input[name='url']").val();
            let full_ori_url = $("#link_full_url").attr("href");
            let url_slices = full_ori_url.split('/');
            let ori_url = url_slices[url_slices.length - 1];
            let full_new_url = full_ori_url.replace(ori_url, url);

            // let href = el.attr('href').val();

            $("#link_full_url").attr("href", full_new_url);
            $("#link_full_url").html(full_new_url);
            $("#link_full_url_copy").attr("data-clipboard-text", full_new_url);
            
            // $('#biolink_preview_iframe').contents().find('#socials')

            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,
                success: (data) => {

                    if(data.message[0] === "url_exist") {

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'This url is already exist!',
                        })

                        $("input[name='url']").val(ori_url);
                        $("#link_full_url").attr("href", full_ori_url);
                        $("#link_full_url").html(full_ori_url);
                    }
                    
                },
                dataType: 'json'
            });

        })

        $("form[name='update_biolink'] .socials-container").on('change', function() {

            let form = document.querySelector('form[name="update_biolink"]');
            let data = new FormData(form);
            let notification_container = $('.notification-container');
            let url = $("input[name='url']").val();
            let full_ori_url = $("#link_full_url").attr("href");
            let url_slices = full_ori_url.split('/');
            let ori_url = url_slices[url_slices.length - 1];
            let full_new_url = full_ori_url.replace(ori_url, url);

            // let href = el.attr('href').val();

            $("#link_full_url").attr("href", full_new_url);
            $("#link_full_url").html(full_new_url);
            $("#link_full_url_copy").attr("data-clipboard-text", full_new_url);

            // $('#biolink_preview_iframe').contents().find('#socials')

            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,
                success: (data) => {

                    if(data.message[0] === "url_exist") {

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'This url is already exist!',
                        })

                        $("input[name='url']").val(ori_url);
                        $("#link_full_url").attr("href", full_ori_url);
                        $("#link_full_url").html(full_ori_url);
                    }
                    
                    update_main_url();
                },
                dataType: 'json'
            });

        })

        $("form[name='update_biolink_'] .start_date, form[name='update_biolink_'] .end_date").focusout(function() {

            let form = document.querySelector('form[name="update_biolink_"]');
            let data = new FormData(form);
            let notification_container = $('.notification-container');

            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,
                success: (data) => {
                    console.log("success")
                },
                dataType: 'json'
            });

        });

        $('form[name="update_biolink_"]').on('focusout', event => {

            let form = $(event.currentTarget)[0];
            let data = new FormData(form);
            let notification_container = $(event.currentTarget).find('.notification-container')

            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                url: 'link-ajax',
                data: data,
                success: (res) => {

                    if( res.status==="success" && res.details && res.details.link_id && res.details.title_value ) {
                        
                        $('.link[data-link-id = "' + res.details.link_id + '"] .link_title').html(res.details.title_value);
                    }

                    if( res.status === 'success' && res.details && res.details.subtype === 'youtube_live') {

                        update_main_url();
                    }
                },
                dataType: 'json'
            });

        })

        $('form[name="update_biolink_"]').keypress(function (event) {

            let keyCode = event.keyCode ? event.keyCode : event.which;
            if(keyCode == 13) {
                
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
                    success: (res) => {

                        if( res.status==="success" && res.details && res.details.link_id && res.details.title_value ) {

                            $('.link[data-link-id = "' + res.details.link_id + '"] .link_title').html(res.details.title_value);
                        }
                    },
                    dataType: 'json'
                });

                event.preventDefault();
            }
        })

    });  

</script>
<script>

    /* Links tab */
    let sortable = Sortable.create(document.getElementById('links'), {
        animation: 150,
        handle: '.drag',
        onUpdate: (event) => {
            let global_token = $('input[name="global_token"]').val();

            let links = [];
            $('#links > .link').each((i, elm) => {
                let link = {
                    link_id: $(elm).data('link-id'),
                    order: i
                };

                links.push(link);
            });

            $.ajax({
                type: 'POST',
                url: 'link-ajax',
                data: {
                    request_type: 'order',
                    links,
                    global_token
                },
                dataType: 'json'
            });

            /* Refresh iframe */
            $('#biolink_preview_iframe').attr('src', $('#biolink_preview_iframe').attr('src'));
        }
    });

    /* Fontawesome icon picker */
    $('[role="iconpicker"]').on('change', event => {


        $(event.currentTarget).closest('.form-group').find('input').val(event.icon).trigger('change');

        let form = $(event.currentTarget).closest('form[name="update_biolink_"]')[0];
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
            success: (res) => {

                if( res.status==="success" && res.details && res.details.link_id && res.details.title_value ) {
                    
                    $('.link[data-link-id = "' + res.details.link_id + '"] .link_title').html(res.details.title_value);
                }
            },
            dataType: 'json'
        });

        event.preventDefault();
            
    });

    /* Status change handler for the links */
    $('[id^="biolink_link_is_enabled_"]').on('click', event => {
        ajax_call_helper(event, 'link-ajax', 'is_enabled_toggle', () => {

            let link_id = $(event.currentTarget).data('row-id');

            $(event.currentTarget).closest('.link').toggleClass('custom-row-inactive');

            /* Refresh iframe */
            $('#biolink_preview_iframe').attr('src', $('#biolink_preview_iframe').attr('src'));

        });
    });

    /* Duplicate link handler for the links */
    $('[data-duplicate="true"]').on('click', event => {
        ajax_call_helper(event, 'link-ajax', 'duplicate', (event, data) => {

            fade_out_redirect({ url: data.details.url, full: true });

        });
    });

    /* When an expanding happens for a link settings */
    $('[id^="link_expanded_content"]').on('show.bs.collapse', event => {
        let link_subtype = $(event.currentTarget).data('link-subtype');
        let link_id = $(event.currentTarget.querySelector('input[name="link_id"]')).val();
        let biolink_link = $('#biolink_preview_iframe').contents().find(`[data-link-id="${link_id}"]`);

        switch (link_subtype) {

            case 'text':
                let title_text_color_pickr_element = event.currentTarget.querySelector('.title_text_color_pickr');
                let description_text_color_pickr_element = event.currentTarget.querySelector('.description_text_color_pickr');

                if(title_text_color_pickr_element) {
                    let color_input = event.currentTarget.querySelector('input[name="title_text_color"]');

                    /* Color Handler */
                    let color_pickr = Pickr.create({
                        el: title_text_color_pickr_element,
                        default: $(color_input).val(),
                        ...pickr_options
                    });

                    color_pickr.off().on('change', hsva => {
                        $(color_input).val(hsva.toHEXA().toString());

                        biolink_link.find('h2').css('color', hsva.toHEXA().toString());

                        let form = $(color_input).parent().parent()[0];
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

                                console.log("update success")

                            },
                            dataType: 'json'
                        });

                    });

                }

                if(description_text_color_pickr_element) {

                    let color_input = event.currentTarget.querySelector('input[name="description_text_color"]');

                    /* Color Handler */
                    let color_pickr = Pickr.create({
                        el: description_text_color_pickr_element,
                        default: $(color_input).val(),
                        ...pickr_options
                    });

                    color_pickr.off().on('change', hsva => {

                        $(color_input).val(hsva.toHEXA().toString());

                        biolink_link.find('p').css('color', hsva.toHEXA().toString());

                        let form = $(color_input).parent().parent()[0];
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

                                console.log("update success")

                            },
                            dataType: 'json'
                        });
                    });
                }

                break;

            default:

                biolink_link = biolink_link.find('a');
                let text_color_pickr_element = event.currentTarget.querySelector('.text_color_pickr');
                let background_color_pickr_element = event.currentTarget.querySelector('.background_color_pickr');

                /* Schedule Handler */
                let schedule_handler = () => {
                    if($(event.currentTarget.querySelector('input[name="schedule"]')).is(':checked')) {
                        $(event.currentTarget.querySelector('.schedule_container')).show();
                    } else {
                        $(event.currentTarget.querySelector('.schedule_container')).hide();
                    }
                };

                $(event.currentTarget.querySelector('input[name="schedule"]')).off().on('change', schedule_handler);

                schedule_handler();

                /* Initiate the datepicker */
                $.fn.datepicker.language['altum'] = <?= json_encode(require APP_PATH . 'includes/datepicker_translations.php') ?>;
                $('[name="start_date"],[name="end_date"]').datepicker({
                    classes: 'datepicker-modal',
                    language: 'altum',
                    dateFormat: 'yyyy-mm-dd',
                    timeFormat: 'hh:ii:00',
                    autoClose: true,
                    timepicker: true,
                    toggleSelected: false,
                    minDate: new Date(),
                });


                let outside_event = event;
                $(event.currentTarget.querySelector('input[name="name"]')).off().on('change paste keyup', event => {
                    biolink_link.text($(event.currentTarget).val());

                    $(outside_event.currentTarget.querySelector('input[name="icon"]')).trigger('change');
                });

                $(event.currentTarget.querySelector('input[name="icon"]')).off().on('change paste keyup', event => {
                    let icon = $(event.currentTarget).val();

                    if(!icon) {
                        biolink_link.find('svg').remove();
                    } else {

                        biolink_link.find('svg,i').remove();
                        biolink_link.prepend(`<i class="${icon} mr-1"></i>`);

                    }

                });

                if(text_color_pickr_element) {
                    let color_input = event.currentTarget.querySelector('input[name="text_color"]');

                    /* Background Color Handler */
                    let color_pickr = Pickr.create({
                        el: text_color_pickr_element,
                        default: $(color_input).val(),
                        id: "text_color_picker",
                        ...pickr_options
                    });

                    color_pickr.off().on('change', hsva => {
                        $(color_input).val(hsva.toHEXA().toString());

                        biolink_link.css('color', hsva.toHEXA().toString());

                        let form = $(color_input).parent().parent().parent()[0];
                        let data = new FormData(form);
                        let notification_container = $('.notification-container');

                        $.ajax({
                            type: 'POST',
                            enctype: 'multipart/form-data',
                            processData: false,
                            contentType: false,
                            cache: false,
                            url: 'link-ajax',
                            data: data,
                            success: (data) => {
                                console.log("success")
                            },
                            dataType: 'json'
                        });

                    });

                }

                if(background_color_pickr_element) {
                    let color_input = event.currentTarget.querySelector('input[name="background_color"]');

                    /* Background Color Handler */
                    let color_pickr = Pickr.create({
                        el: background_color_pickr_element,
                        default: $(color_input).val(),
                        id: 'background_color_picker',
                        ...pickr_options
                    });

                    color_pickr.off().on('change', hsva => {


                        $(color_input).val(hsva.toHEXA().toString());

                        /* Change the background or the border color */
                        if(biolink_link.css('background-color') != 'rgba(0, 0, 0, 0)') {
                            biolink_link.css('background-color', hsva.toHEXA().toString());
                        } else {
                            biolink_link.css('border-color', hsva.toHEXA().toString());
                        }

                        let form = $(color_input).parent().parent().parent()[0];
                        let data = new FormData(form);
                        let notification_container = $('.notification-container');

                        $.ajax({
                            type: 'POST',
                            enctype: 'multipart/form-data',
                            processData: false,
                            contentType: false,
                            cache: false,
                            url: 'link-ajax',
                            data: data,
                            success: (data) => {
                                console.log("success")
                            },
                            dataType: 'json'
                        });

                    });

                }

                $(event.currentTarget.querySelector('input[name="outline"]')).off().on('change', event => {

                    let outline = $(event.currentTarget).is(':checked');

                    if(outline) {
                        /* From background color to border */
                        let background_color = biolink_link.css('background-color');

                        biolink_link.css('background-color', 'transparent');
                        biolink_link.css('border', `.1rem solid ${background_color}`);
                    } else {
                        /* From border to background color */
                        let border_color = biolink_link.css('border-color');

                        biolink_link.css('background-color', border_color);
                        biolink_link.css('border', 'none');
                    }

                });

                $(event.currentTarget.querySelector('select[name="border_radius"]')).off().on('change', event => {

                    let border_radius = $(event.currentTarget).find(':selected').val();

                    switch(border_radius) {
                        case 'straight':

                            biolink_link.removeClass('link-btn-round link-btn-rounded');

                            break;

                        case 'round':

                            biolink_link.removeClass('link-btn-rounded').addClass('link-btn-round');

                            break;

                        case 'rounded':

                            biolink_link.removeClass('link-btn-round').addClass('link-btn-rounded');

                            break;
                    }

                });

                let current_animation = $(event.currentTarget.querySelector('select[name="animation"]')).val();

                $(event.currentTarget.querySelector('select[name="animation"]')).off().on('change', event => {

                    let animation = $(event.currentTarget).find(':selected').val();

                    switch(animation) {
                        case 'false':

                            biolink_link.removeClass(`animated ${current_animation}`);
                            current_animation = false;

                            break;

                        default:

                            biolink_link.removeClass(`animated ${current_animation}`).addClass(`animated ${animation}`);
                            current_animation = animation;

                            break;
                    }

                });

                $(event.currentTarget.querySelector('select[name="animation_speed"]')).off().on('change', event => {

                    let animation_duration = $(event.currentTarget).find(':selected').val();

                    switch(animation_duration) {
                        case 'faster':
                            biolink_link.css('animation-duration', '0.7s')
                            break;
                        case 'fast':
                            biolink_link.css('animation-duration', '1s')
                            break;
                        case 'normal':
                            biolink_link.css('animation-duration', '2s')
                            break;
                        case 'slow':
                            biolink_link.css('animation-duration', '3s')
                            break;
                        case 'slower':
                            biolink_link.css('animation-duration', '4s')
                            break;
                        default:
                            break;
                    }

                });

        }

    })

</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
