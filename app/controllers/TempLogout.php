<?php

namespace Altum\Controllers;

use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;

class TempLogout extends Controller {

    public function index() {

        Authentication::logout2();
        $status = $_GET['status'];
        $username = $_GET['username'];
        redirect('new_register?status='. $status . '&username=' .$username);
      
    }

}
