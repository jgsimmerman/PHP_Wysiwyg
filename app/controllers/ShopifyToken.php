<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Csrf;
    use Altum\Middlewares\Authentication;

    class ShopifyToken extends Controller {

        public function index() {

            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);

            if(!empty($rawData)) {
                
                $access_token = $rawData->access_token;
                $nonce = $rawData->nonce;
                $shop = $rawData->shop;
            }

            $products = $this->shopify_call($access_token, $shop, "/admin/products.json", array(), 'GET');
            $tagged_products = NULL;
            $marketplace = 'shopify';
            
            if($user_id = Database::simple_get('user_id', 'shopify_tokens', ['nonce' => $nonce])) {
               
                $shopify_link_id = Database::simple_get('shopify_link_id', 'shopify_tokens', ['nonce' => $nonce]);
                $biolink_id = Database::simple_get('biolink_id', 'links', ['user_id' => $user_id, 'link_id' => $shopify_link_id]);

                $products = json_encode($products['response']);

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `access_token` = ? WHERE `nonce` = ?");
                $stmt->bind_param('ss', $access_token, $nonce);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `shop` = ? WHERE `nonce` = ?");
                $stmt->bind_param('ss', $shop, $nonce);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `products` = ? WHERE `nonce` = ?");
                $stmt->bind_param('ss', $products, $nonce);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `tagged_products` = ? WHERE `nonce` = ?");
                $stmt->bind_param('ss', $tagged_products, $nonce);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `marketplace` = ? WHERE `nonce` = ?");
                $stmt->bind_param('ss', $marketplace, $nonce);
                $stmt->execute();
                $stmt->close();

                // file_put_contents('./result.log', $products, FILE_APPEND | LOCK_EX);
                echo $biolink_id;
                die();

            } else {

                echo "No Access Token!";
                die();
            }

        }

        private function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array())
        {
            // Build URL
            $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
            if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);
        
            // Configure cURL
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        
            // Setup headers
            $request_headers[] = "";
            if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
        
            if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
                if (is_array($query)) $query = http_build_query($query);
                curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
            }
            
            // Send request to Shopify and capture any errors
            $response = curl_exec($curl);
            $error_number = curl_errno($curl);
            $error_message = curl_error($curl);
        
            // Close cURL to be nice
            curl_close($curl);
        
            // Return an error is cURL has a problem
            if ($error_number) {

                return $error_message;
            } else {
        
                // No error, return Shopify's response by parsing out the body and the headers
                $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
        
                // Convert headers into an array
                $headers = array();
                $header_data = explode("\n",$response[0]);
                $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
                array_shift($header_data); // Remove status, we've already set it above
                foreach($header_data as $part) {
                    $h = explode(":", $part);
                    $headers[trim($h[0])] = trim($h[1]);
                }
                // Return headers and Shopify's response
                return array('headers' => $headers, 'response' => $response[1]);
            }
        }
    }