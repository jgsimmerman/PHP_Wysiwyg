<?php defined('ALTUMCODE') || die() ?>

<?php require THEME_PATH . 'views/partials/ads_header.php' ?>

<div class="container">
    <div class="d-flex flex-column align-items-center">
        <div class="col-xs-12 col-sm-10 col-md-8 col-lg-5">
            <?php display_notifications() ?>

            <div class="card border-0">
                <div class="card-body p-5">
                    <h4 class="card-title"><?= $this->language->register->header ?></h4>

                    <form action="" method="post" class="mt-4" role="form">
                        <div class="form-group">
                            <label><?= $this->language->register->form->name ?></label>
              
                            <input type="text" name="name" class="form-control" value="<?= $_GET['username'] ?>" placeholder="<?= $_GET['username'] ?>" required="required" readonly="true" />
                            <input type="hidden" name="status" value="temp">
                        </div>

                        <div class="form-group">
                            <label><?= $this->language->register->form->email ?></label>
                            <input type="text" name="email" class="form-control" value="<?= $data->values['email'] ?>" placeholder="<?= $this->language->register->form->email_placeholder ?>" required="required" />
                        </div>

                        <div class="form-group">
                            <label><?= $this->language->register->form->password ?></label>
                            <input type="password" name="password" class="form-control" value="<?= $data->values['password'] ?>" placeholder="<?= $this->language->register->form->password_placeholder ?>" required="required" />
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" name="submit" class="btn btn-primary btn-block"><?= $this->language->register->form->register ?></button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <small><a href="login" class="text-muted" role="button"><?= $this->language->register->login ?></a></small>
        </div>
    </div>
</div>
