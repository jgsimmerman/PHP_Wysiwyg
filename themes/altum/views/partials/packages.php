<?php defined('ALTUMCODE') || die() ?>

<?php

use Altum\Middlewares\Authentication;

?>

<?php $packages_result = $this->database->query("SELECT * FROM `packages` WHERE `status` = 1"); ?>


<div class="pricing-container">
    <div class="pricing">

        <?php

        /* Detect if we need to show the Free plan to the user */
        if($this->settings->package_free->status):

            ?>
            <div class="pricing-plan shadow-lg">
                <div class="pricing-header">
                    <span class="pricing-name"><?= $this->settings->package_free->name ?></span>

                    <div class="pricing-price">
                        <span class="pricing-price-amount"><?= $this->language->package->free->price ?></span>
                    </div>

                    <div class="pricing-details">&nbsp;</div>
                </div>

                <div class="pricing-body">
                    <ul class="pricing-features">
                        <?php if($this->settings->package_free->settings->projects_limit == -1): ?>
                            <li>
                                <div><?= $this->language->global->package_settings->unlimited_projects_limit ?></div>
                            </li>
                        <?php else: ?>
                            <li>
                                <div><?= sprintf($this->language->global->package_settings->projects_limit, $this->settings->package_free->settings->projects_limit) ?></div>
                            </li>
                        <?php endif ?>

                        <?php if($this->settings->package_free->settings->biolinks_limit == -1): ?>
                            <li>
                                <div><?= $this->language->global->package_settings->unlimited_biolinks_limit ?></div>
                            </li>
                        <?php else: ?>
                            <li>
                                <div><?= sprintf($this->language->global->package_settings->biolinks_limit, $this->settings->package_free->settings->biolinks_limit) ?></div>
                            </li>
                        <?php endif ?>

                        <?php if($this->settings->links->shortener_is_enabled): ?>
                            <?php if($this->settings->package_free->settings->links_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_links_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->links_limit, $this->settings->package_free->settings->links_limit) ?></div>
                                </li>
                            <?php endif ?>
                        <?php endif ?>

                        <?php if($this->settings->links->domains_is_enabled): ?>
                            <?php if($this->settings->package_free->settings->domains_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_domains_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->domains_limit, $this->settings->package_free->settings->domains_limit) ?></div>
                                </li>
                            <?php endif ?>
                        <?php endif ?>

                        <?php foreach($data->simple_package_settings as $package_setting): ?>
                            <li>
                                <div class="<?= $this->settings->package_free->settings->{$package_setting} ? null : 'text-muted' ?>">
                                    <span data-toggle="tooltip" title="<?= $this->language->global->package_settings->{$package_setting . '_help'} ?>"><?= $this->language->global->package_settings->{$package_setting} ?></span>
                                </div>

                                <i class="fa fa-fw fa-sm <?= $this->settings->package_free->settings->{$package_setting} ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                            </li>
                        <?php endforeach ?>
                    </ul>

                    <?php if(Authentication::check() && $this->user->package_id == 'free'): ?>
                        <button class="btn btn-lg btn-block btn-secondary pricing-button"><?= $this->language->package->button->already_free ?></button>
                    <?php else: ?>
                        <a href="<?= Authentication::check() ? url('pay/free') : url('register?redirect=pay/free') ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= $this->language->package->button->choose ?></a>
                    <?php endif ?>
                </div>
            </div>

        <?php endif ?>

        <?php if($this->settings->payment->is_enabled): ?>

            <?php if($this->settings->package_trial->status): ?>

                <div class="pricing-plan shadow-lg">
                    <div class="pricing-header">
                        <span class="pricing-name"><?= $this->settings->package_trial->name ?></span>

                        <div class="pricing-price">
                            <span class="pricing-price-amount"><?= $this->language->package->trial->price ?></span>
                        </div>

                        <div class="pricing-details">&nbsp;</div>
                    </div>

                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <?php if($this->settings->package_trial->settings->projects_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_projects_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->projects_limit, $this->settings->package_trial->settings->projects_limit) ?></div>
                                </li>
                            <?php endif ?>

                            <?php if($this->settings->package_trial->settings->biolinks_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_biolinks_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->biolinks_limit, $this->settings->package_trial->settings->biolinks_limit) ?></div>
                                </li>
                            <?php endif ?>

                            <?php if($this->settings->links->shortener_is_enabled): ?>
                                <?php if($this->settings->package_trial->settings->links_limit == -1): ?>
                                    <li>
                                        <div><?= $this->language->global->package_settings->unlimited_links_limit ?></div>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <div><?= sprintf($this->language->global->package_settings->links_limit, $this->settings->package_trial->settings->links_limit) ?></div>
                                    </li>
                                <?php endif ?>
                            <?php endif ?>

                            <?php if($this->settings->links->domains_is_enabled): ?>
                                <?php if($this->settings->package_trial->settings->domains_limit == -1): ?>
                                    <li>
                                        <div><?= $this->language->global->package_settings->unlimited_domains_limit ?></div>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <div><?= sprintf($this->language->global->package_settings->domains_limit, $this->settings->package_trial->settings->domains_limit) ?></div>
                                    </li>
                                <?php endif ?>
                            <?php endif ?>

                            <?php foreach($data->simple_package_settings as $package_setting): ?>
                                <li>
                                    <div class="<?= $this->settings->package_trial->settings->{$package_setting} ? null : 'text-muted' ?>"><?= $this->language->global->package_settings->{$package_setting} ?></div>

                                    <i class="fa fa-fw fa-sm <?= $this->settings->package_trial->settings->{$package_setting} ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                </li>
                            <?php endforeach ?>
                        </ul>

                        <?php if(Authentication::check() && $this->user->package_trial_done): ?>
                            <button class="btn btn-lg btn-block btn-secondary pricing-button"><?= $this->language->package->button->disabled ?></button>
                        <?php else: ?>
                            <a href="<?= Authentication::check() ? url('pay/trial') : url('register?redirect=pay/trial') ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= $this->language->package->button->choose ?></a>
                        <?php endif ?>
                    </div>
                </div>

            <?php endif ?>

            <?php while($package = $packages_result->fetch_object()): ?>
                <?php $package->settings = json_decode($package->settings) ?>

                <div class="pricing-plan shadow-lg">
                    <div class="pricing-header">
                        <span class="pricing-name"><?= $package->name ?></span>

                        <div class="pricing-price">
                            <span class="pricing-price-currency"><?= $this->settings->payment->currency ?></span>
                            <span class="pricing-price-amount"><?= $package->monthly_price ?></span>
                            <span class="pricing-price-period"><?= $this->language->package->display->per_month ?></span>
                        </div>

                        <div class="pricing-details"><?= sprintf($this->language->package->display->annual_price, $package->annual_price, $this->settings->payment->currency) ?></div>
                    </div>

                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <?php if($package->settings->projects_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_projects_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->projects_limit, $package->settings->projects_limit) ?></div>
                                </li>
                            <?php endif ?>

                            <?php if($package->settings->biolinks_limit == -1): ?>
                                <li>
                                    <div><?= $this->language->global->package_settings->unlimited_biolinks_limit ?></div>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div><?= sprintf($this->language->global->package_settings->biolinks_limit, $package->settings->biolinks_limit) ?></div>
                                </li>
                            <?php endif ?>

                            <?php if($this->settings->links->shortener_is_enabled): ?>
                                <?php if($package->settings->links_limit == -1): ?>
                                    <li>
                                        <div><?= $this->language->global->package_settings->unlimited_links_limit ?></div>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <div><?= sprintf($this->language->global->package_settings->links_limit, $package->settings->links_limit) ?></div>
                                    </li>
                                <?php endif ?>
                            <?php endif ?>

                            <?php if($this->settings->links->domains_is_enabled): ?>
                                <?php if($package->settings->domains_limit == -1): ?>
                                    <li>
                                        <div><?= $this->language->global->package_settings->unlimited_domains_limit ?></div>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <div><?= sprintf($this->language->global->package_settings->domains_limit, $package->settings->domains_limit) ?></div>
                                    </li>
                                <?php endif ?>
                            <?php endif ?>

                            <?php foreach($data->simple_package_settings as $package_setting): ?>
                                <li>
                                    <div class="<?= $package->settings->{$package_setting} ? null : 'text-muted' ?>"><?= $this->language->global->package_settings->{$package_setting} ?></div>

                                    <i class="fa fa-fw fa-sm <?= $package->settings->{$package_setting} ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                </li>
                            <?php endforeach ?>
                        </ul>

                        <a href="<?= Authentication::check() ? url('pay/' . $package->package_id) : url('register?redirect=pay/' . $package->package_id) ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= $this->language->package->button->choose ?></a>
                    </div>
                </div>

            <?php endwhile ?>

        <?php endif ?>

    </div>
</div>
