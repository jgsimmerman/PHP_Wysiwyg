<?php

namespace Altum\Controllers;

use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Logger;
use Google;
use Altum\Middlewares\Authentication;

class ShopifyConnect extends Controller {

    public function index() {

        $shop = $_GET['shop'];

        // Api key for LinkInbio2 App
        $api_key = "b0fd77dfa5619bc150dbfc5d4d78142a";
        $scopes = "read_orders,write_orders,read_customers,read_products,write_products,write_checkouts,read_checkouts";
        $redirect_uri =  url(). "app/helpers/generate_token.php";
        $nonce = string_generate(10);

        $shopify_link_id = \Altum\Middlewares\Csrf::get('shopify_link_id');
        $user_id = \Altum\Middlewares\Csrf::get('user_id');

        if(!$nonce_db = Database::simple_get('nonce', 'shopify_tokens', ['user_id' => $user_id, 'shopify_link_id' => $shopify_link_id])) {
               
            $stmt = Database::$database->prepare("INSERT INTO `shopify_tokens` (`user_id`, `shopify_link_id`, `nonce`) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $user_id, $shopify_link_id, $nonce);
            $stmt->execute();
            $stmt->close();
            
        } else {

            $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `nonce` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
            $stmt->bind_param('sss', $nonce, $user_id, $shopify_link_id);
            $stmt->execute();
            $stmt->close();

        }

        // Build install/approval URL to redirect to
        $install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri) ."&state=" .$nonce;

        header("Location: " . $install_url);
        die();

    }

}
