<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Altum\Middlewares\Authentication;

    class ShopErasure extends Controller
    {
        public function index()
        {
            $webhookContent = "";

            $webhook = fopen('php://input' , 'rb');
            while (!feof($webhook)) {
                $webhookContent .= fread($webhook, 4096);
            }
            fclose($webhook);

            error_log($webhookContent);
        }
    }