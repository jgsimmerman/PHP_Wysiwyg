<?php

namespace Altum\Controllers;

use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;

class CheckoutCurl extends Controller {

    public function index() {

        $post_url = $_GET['post_url'];
        $access_token = $_GET['access_token'];
        $order_info = $_GET['order_info'];
        $order_info = json_decode($order_info);
        $order_content = '';

        foreach($order_info as $key => $order_item) {
            $order_item_obj = get_object_vars($order_item);
            $variant_id = key($order_item);
            $quantity = $order_item_obj[key($order_item)];

            $order_content .= '{
                "variant_id": "'.$variant_id.'",
                "quantity": "'.$quantity.'"
              },';
        }

        $order_content = substr($order_content, 0, -1);

        $data = '{
            "checkout": {
                "line_items": ['
                    .$order_content.
                ']
            }
        }';

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $post_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
            "X-Shopify-Access-Token: {$access_token}",
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $web_url = $response->checkout->web_url;

        header("Location: " . $web_url);

    }

}
