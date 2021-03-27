<?php

    // Api key for LinkInbio2 App
    $api_key = "b0fd77dfa5619bc150dbfc5d4d78142a";
    $shared_secret = "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";

    $params = $_GET; // Retrieve all request parameters
    $hmac = $_GET['hmac'];
    $nonce = $_GET['state'];

    $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
    ksort($params); // Sort params lexographically

    $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

    // Use hmac data to check that the response is from Shopify or not
    if (hash_equals($hmac, $computed_hmac)) {

        // Set variables for our request
        $query = array(
            "client_id" => $api_key,
            "client_secret" => $shared_secret,
            "code" => $params['code']
        );

        // Generate access token URL
        $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

        // Configure curl client and execute request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $access_token_url);
        curl_setopt($ch, CURLOPT_POST, count($query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);
        $access_token = $result['access_token'];
        $shop = explode(".", $params['shop'])[0];

        $data = '{
            "access_token": "'.$access_token.'",
            "nonce": "'.$nonce.'",
            "shop": "'.$shop.'"
          }';

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => "https://staging.linkinbio.is/shopify_token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $biolink_id = $response;

        $redirect_url = "https://staging.linkinbio.is/link/" .$biolink_id ."?tab=links";
        header("Location: " . $redirect_url);
        // $shop_admin = "https://" .$shop . ".myshopify.com/admin";
        // header("Location: " .$shop_admin);

        // $ch = curl_init();
        // curl_setopt_array($ch, array(
        //     CURLOPT_URL => "https://linkinbio.is/shopify_token",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS =>$data,
        //     CURLOPT_HTTPHEADER => array(
        //         "Content-Type: application/json",
        //     ),
        // ));

        // $response = curl_exec($ch);
        // curl_close($ch);
        // $biolink_id = $response;

        // $redirect_url = "https://linkinbio.is/link/" .$biolink_id ."?tab=links";
        // header("Location: " . $redirect_url);

    } else {
        // Someone is trying to be shady!
        die('This request is NOT from Shopify!');
    }