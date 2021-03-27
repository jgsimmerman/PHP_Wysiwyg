<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Altum\Middlewares\Authentication;

    class CustomerRequest extends Controller
    {
        public function index()
        {
            
            $webhook_payload = file_get_contents('php://input');
            $webhook_payload = json_decode($webhook_payload, true);

            $shop_id = $webhook_payload['shop_id'];
            $shop_domain = $webhook_payload['shop_domain'];
            $customer_id = $webhook_payload['customer']['id'];

            file_put_contents('./webhook.log', $shop_id. '--------'. $shop_domain .'~~~~~~~~~~~~' . $customer_id , FILE_APPEND | LOCK_EX);
        }
    }