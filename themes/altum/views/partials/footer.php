<?php defined('ALTUMCODE') || die() ?>

<footer class="footer <?= \Altum\Routing\Router::$controller_key == 'index' ? 'm-0' : null ?>">
    <div class="container">
        <div class="row">

            <div class="row col">
                <div class="col-md-4">
                    <a class="navbar-brand m-0 p-0" href="<?= url() ?>">
                        <?php if($this->settings->logo != ''): ?>
                            <img src="<?= SITE_URL . UPLOADS_URL_PATH . 'logo/' . $this->settings->logo ?>" class="footer-logo" alt="<?= $this->language->global->accessibility->logo_alt ?>" />
                        <?php else: ?>
                            <?= $this->settings->title ?>
                        <?php endif ?>
                    </a>
                </div>

                <div class="col-md-8 mt-3 mt-md-0">

                    <div>
                        <div><?= 'Copyright &copy; ' . date('Y') . ' ' . $this->settings->title . '.' ?></div>
                    </div>

                    <div class="mt-3">
                        <?php foreach(require APP_PATH . 'includes/admin_socials.php' as $key => $value): ?>

                            <?php if(isset($this->settings->socials->{$key}) && !empty($this->settings->socials->{$key})): ?>
                            <span class="mr-2">
                                <a target="_blank" href="<?= sprintf($value['format'], $this->settings->socials->{$key}) ?>" title="<?= $value['name'] ?>" class="no-underline">
                                    <i class="<?= $value['icon'] ?> fa-fw fa-lg"></i>
                                </a>
                            </span>
                        <?php endif ?>

                        <?php endforeach ?>
                    </div>

                </div>
            </div>

            <div class="col-12 col-sm-4 mt-3 mt-sm-0">
                <?php foreach($data->pages as $data): ?>
                    <a href="<?= $data->url ?>" target="<?= $data->target ?>"><?= $data->title ?></a><br />
                <?php endforeach ?>

                <?php if(count(\Altum\Language::$languages) > 1): ?>
                    <div class="dropdown">
                        <a class="dropdown-toggle clickable" id="language_switch" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw fa-language fa-sm mr-1 text-muted"></i> <?= $this->language->global->language ?></a>

                        <div class="dropdown-menu" aria-labelledby="language_switch">
                            <h6 class="dropdown-header"><?= $this->language->global->choose_language ?></h6>
                            <?php foreach(\Altum\Language::$languages as $language_name): ?>
                                <a class="dropdown-item" href="<?= url((\Altum\Routing\Router::$controller_key == 'index' ? 'index' : $_GET['altum']) . '?language=' . $language_name) ?>">
                                    <?php if($language_name == \Altum\Language::$language): ?>
                                        <i class="fa fa-fw fa-sm fa-check mr-1 text-success"></i>
                                    <?php else: ?>
                                        <i class="far fa-fw fa-sm fa-circle mr-1 text-muted"></i>
                                    <?php endif ?>

                                    <?= $language_name ?>
                                </a>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>

                <?php if(count(\Altum\ThemeStyle::$themes) > 1): ?>
                    <div class="dropdown">
                        <a class="dropdown-toggle clickable" id="theme_style_switch" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw fa-palette fa-sm mr-1 text-muted"></i> <?= $this->language->global->theme_style ?></a>

                        <div class="dropdown-menu" aria-labelledby="theme_style_switch">
                            <?php foreach(\Altum\ThemeStyle::$themes as $key => $value): ?>
                                <a class="dropdown-item" data-choose-theme-style="<?= $key ?>" href="#">
                                    <?php if($key == \Altum\ThemeStyle::get()): ?>
                                        <i class="fa fa-fw fa-sm fa-check mr-1 text-success"></i>
                                    <?php else: ?>
                                        <i class="far fa-fw fa-sm fa-circle mr-1 text-muted"></i>
                                    <?php endif ?>

                                    <?= $this->language->global->{'theme_style_' . $key} ?>
                                </a>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <?php ob_start() ?>
                    <script>
                        $('[data-choose-theme-style]').on('click', event => {

                            set_cookie('theme_style', $(event.currentTarget).data('choose-theme-style'), 30);

                            location.reload();

                            event.preventDefault();
                        })
                    </script>
                    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

                <?php endif ?>
            </div>

        </div>
    </div>
</footer>
