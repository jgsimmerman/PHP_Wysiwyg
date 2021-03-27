<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Altum\Middlewares\Authentication;

    class CustomerErasure extends Controller
    {
        public function index()
        {
            
            $webhook_payload = file_get_contents('php://input');
            $webhook_payload = json_decode($webhook_payload, true);

            file_put_contents('./customererasre.log', json_encode($webhook_payload) , FILE_APPEND | LOCK_EX);

        }
    }