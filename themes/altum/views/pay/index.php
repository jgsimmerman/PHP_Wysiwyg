<?php defined('ALTUMCODE') || die() ?>

<?php require THEME_PATH . 'views/partials/ads_header.php' ?>

<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-md-10 col-lg-8">

            <?php display_notifications() ?>

            <h2><?= sprintf($this->language->pay->header, $data->package->name) ?></h2>
            <div class="text-muted mb-5"><?= $this->language->pay->subheader ?></div>


            <?php if($data->package_id == 'free'): ?>

                <?php if($this->user->package_id == 'free'): ?>

                <div class="alert alert-info" role="alert"><?= $this->language->pay->free->free_already ?></div>

                <div class="text-center mt-5">
                    <a href="<?= url('package') ?>" class="btn btn-primary"><?= $this->language->pay->free->choose_another_package ?></a>
                </div>

            <?php else: ?>

                <div class="alert alert-info" role="alert"><?= $this->language->pay->free->other_package_not_expired ?></div>

                <div class="text-center mt-5">
                    <a href="<?= url('package') ?>" class="btn btn-primary"><?= $this->language->pay->free->choose_another_package ?></a>
                </div>
            <?php endif ?>

            <?php elseif($data->package_id == 'trial'): ?>

            <?php if($this->user->package_trial_done): ?>

                <div class="alert alert-warning" role="alert"><?= $this->language->pay->trial->trial_done ?></div>

                <div class="text-center mt-5">
                    <a href="<?= url('package') ?>" class="btn btn-primary"><?= $this->language->pay->trial->choose_another_package ?></a>
                </div>

            <?php else: ?>

                <?php if($this->user->package_id != 'free' && !$this->user->package_is_expired): ?>

                <div class="alert alert-info" role="alert"><?= $this->language->pay->trial->other_package_not_expired ?></div>

            <?php endif ?>

                <form action="<?= 'pay/' . $data->package_id ?>" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" />

                    <div class="text-center mt-5">
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-fw fa-stopwatch"></i> <?= $this->language->pay->trial->trial_start ?></button>
                    </div>

                </form>

            <?php endif ?>

            <?php else: ?>

            <?php
            /* Check for extra savings on the prices */
            $annual_price_savings = ceil(($data->package->monthly_price * 12) - $data->package->annual_price);

            ?>

                <div class="margin-top-6 mb-5"><i class="fa fa-fw fa-box-open mr-3"></i> <span class="h5 text-muted"><?= $this->language->pay->custom_package->payment_plan ?></span></div>

                <form action="<?= 'pay/' . $data->package_id ?>" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" />

                    <div class="row d-flex align-items-stretch">
                        <label class="col-6 custom-radio-box">

                            <input type="radio" id="monthly_price" name="payment_plan" value="monthly" class="custom-control-input" required="required">

                            <div class="card card-shadow zoomer h-100">
                                <div class="card-body">

                                    <div class="card-title text-center"><?= $this->language->pay->custom_package->monthly ?></div>

                                    <div class="mt-3 text-center">
                                        <span id="monthly_price_amount" class="custom-radio-box-main-text"><?= $data->package->monthly_price ?></span> <span><?= $this->settings->payment->currency ?></span>
                                    </div>

                                </div>
                            </div>

                        </label>

                        <label class="col-6 custom-radio-box">

                            <input type="radio" id="annual_price" name="payment_plan" value="annual" class="custom-control-input" required="required">

                            <div class="card card-shadow zoomer h-100">
                                <div class="card-body">

                                    <div class="card-title text-center"><?= $this->language->pay->custom_package->annual ?></div>

                                    <div class="mt-3 text-center">
                                        <span id="annual_price_amount" class="custom-radio-box-main-text"><?= $data->package->annual_price ?></span> <span><?= $this->settings->payment->currency ?></span>
                                        <div class="text-muted">
                                            <small><?= sprintf($this->language->pay->custom_package->annual_savings, $annual_price_savings, $this->settings->payment->currency) ?></small>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </label>
                    </div>

                    <?php if($this->settings->payment->codes_is_enabled): ?>
                        <input type="hidden" name="package_id" value="<?= $data->package_id ?>" />
                        <input type="hidden" name="monthly_price" value="<?= $data->package->monthly_price ?>" />
                        <input type="hidden" name="annual_price" value="<?= $data->package->annual_price ?>" />

                        <div class="form-group mt-4">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-fw fa-tags mr-3"></i> <input type="text" name="code" class="form-control form-control-lg" placeholder="<?= $this->language->pay->custom_package->code ?>" />
                            </div>

                            <div class="mt-2"><span id="code_help" class="text-muted"></span></div>
                        </div>

                    <?php ob_start() ?>
                        <script>
                            let timer = null;

                            $('input[name="code"]').on('change paste keyup', event => {

                                let code = $(event.currentTarget).val();
                                let global_token = $('input[name="global_token"]').val();
                                let package_id = $('input[name="package_id"]').val();
                                let monthly_price = $('input[name="monthly_price"]').val();
                                let annual_price = $('input[name="annual_price"]').val();
                                let is_valid = false;

                                clearTimeout(timer);

                                if(code.trim() == '') {
                                    $('#monthly_price_amount').html(monthly_price);
                                    $('#annual_price_amount').html(annual_price);
                                    $('#code_help').html('');
                                    $(event.currentTarget).removeClass('is-invalid').removeClass('is-valid');

                                    return;
                                }

                                timer = setTimeout(() => {
                                    $.ajax({
                                        type: 'POST',
                                        url: `${$('input[name="url"]').val()}pay/code`,
                                        data: {code, global_token, package_id},
                                        success: data => {

                                            if(data.status == 'success') {
                                                is_valid = true;

                                                /* Set the new discounted price */
                                                let new_monthly_price = nr(monthly_price - (monthly_price * data.details.discount / 100), 2);
                                                let new_annual_price = nr(annual_price - (annual_price * data.details.discount / 100), 2);

                                                $('#monthly_price_amount').html(new_monthly_price);
                                                $('#annual_price_amount').html(new_annual_price);
                                            } else {
                                                $('#monthly_price_amount').html(monthly_price);
                                                $('#annual_price_amount').html(annual_price);
                                            }

                                            $('#code_help').html(data.message);

                                            if(is_valid) {
                                                $(event.currentTarget).addClass('is-valid');
                                                $(event.currentTarget).removeClass('is-invalid');
                                            } else {
                                                $(event.currentTarget).addClass('is-invalid');
                                                $(event.currentTarget).removeClass('is-valid');
                                            }

                                        },
                                        dataType: 'json'
                                    });
                                }, 500);

                            });
                        </script>
                        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
                    <?php endif ?>

                    <div class="margin-top-6 mb-5"><i class="fa fa-fw fa-money-check-alt mr-3"></i> <span class="h5 text-muted"><?= $this->language->pay->custom_package->payment_processor ?></span></div>

                    <?php if(!$this->settings->paypal->is_enabled && !$this->settings->stripe->is_enabled): ?>
                        <div class="alert alert-info" role="alert">
                            <?= $this->language->pay->custom_package->no_processor ?>
                        </div>
                    <?php endif ?>

                    <div class="row d-flex align-items-stretch">

                        <?php if($this->settings->paypal->is_enabled): ?>
                            <label class="col-6 custom-radio-box">

                                <input type="radio" id="paypal_processor" name="payment_processor" value="paypal" class="custom-control-input" required="required">

                                <div class="card card-shadow zoomer h-100">
                                    <div class="card-body">

                                        <div class="card-title text-center"><?= $this->language->pay->custom_package->paypal_processor ?></div>

                                        <div class="mt-3 text-center">
                                            <span class="custom-radio-box-main-icon"><i class="fab fa-fw fa-paypal"></i></span>
                                        </div>

                                    </div>
                                </div>

                            </label>
                        <?php endif ?>

                        <?php if($this->settings->stripe->is_enabled): ?>
                            <label class="col-6 custom-radio-box">

                                <input type="radio" id="stripe_processor" name="payment_processor" value="stripe" class="custom-control-input" required="required">

                                <div class="card card-shadow zoomer h-100">
                                    <div class="card-body">

                                        <div class="card-title text-center"><?= $this->language->pay->custom_package->stripe_processor ?></div>

                                        <div class="mt-3 text-center">
                                            <span class="custom-radio-box-main-icon"><i class="fab fa-fw fa-stripe"></i></span>
                                        </div>

                                    </div>
                                </div>

                            </label>
                        <?php endif ?>
                    </div>

                    <div class="margin-top-6 mb-5"><i class="fa fa-fw fa-dollar-sign mr-3"></i> <span class="h5 text-muted"><?= $this->language->pay->custom_package->payment_type ?></span></div>

                    <div class="row d-flex align-items-stretch">
                        <?php if(in_array($this->settings->payment->type, ['one-time', 'both'])): ?>
                        <label class="col-6 custom-radio-box">
                            <input type="radio" id="one-time_type" name="payment_type" value="one-time" class="custom-control-input" required="required">

                            <div class="card card-shadow zoomer h-100">
                                <div class="card-body">

                                    <div class="card-title text-center"><?= $this->language->pay->custom_package->one_time_type ?></div>

                                    <div class="mt-3 text-center">
                                        <span class="custom-radio-box-main-icon"><i class="fa fa-fw fa-hand-holding-usd"></i></span>
                                    </div>

                                </div>
                            </div>
                        </label>
                        <?php endif ?>

                        <?php if(in_array($this->settings->payment->type, ['recurring', 'both'])): ?>
                        <label class="col-6 custom-radio-box" id="recurring_type_label">
                            <input type="radio" id="recurring_type" name="payment_type" value="recurring" class="custom-control-input" required="required">

                            <div class="card card-shadow zoomer h-100">
                                <div class="card-body">

                                    <div class="card-title text-center"><?= $this->language->pay->custom_package->recurring_type ?></div>

                                    <div class="mt-3 text-center">
                                        <span class="custom-radio-box-main-icon"><i class="fa fa-fw fa-sync-alt"></i></span>
                                    </div>

                                </div>
                            </div>
                        </label>
                        <?php endif ?>
                    </div>

                    <div class="margin-top-3 form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" name="accept" type="checkbox" required="required">
                            <?= sprintf(
                                $this->language->pay->accept,
                                '<a href="' . $this->settings->terms_and_conditions_url . '">' . $this->language->register->form->terms_and_conditions . '</a>',
                                '<a href="' . $this->settings->privacy_policy_url . '">' . $this->language->register->form->privacy_policy . '</a>'
                            ) ?>
                        </label>
                    </div>

                    <div class="text-center margin-top-6">
                        <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary"><?= $this->language->pay->custom_package->pay ?></button>
                    </div>
                </form>


            <?php

            /* Include only if the stripe redirect session was generated */
            if($data->stripe_session):

            ?>
                <script src="https://js.stripe.com/v3/"></script>

                <script>
                    let stripe = Stripe(<?= json_encode($this->settings->stripe->publishable_key) ?>);

                    stripe.redirectToCheckout({
                        sessionId: <?= json_encode($data->stripe_session->id) ?>,
                    }).then((result) => {

                        /* Nothing for the moment */

                    });
                </script>

            <?php endif ?>

            <?php endif ?>

        </div>
    </div>
</div>

<?php ob_start() ?>
    <script>
        $('[name="payment_processor"]:first').attr('checked', 'checked');
        $('[name="payment_type"]:first').attr('checked', 'checked');
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
