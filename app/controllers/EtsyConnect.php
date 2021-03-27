<?php

namespace Altum\Controllers;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Service\Etsy;
use Altum\Middlewares\Authentication;
use Altum\Captcha;
use Altum\Database\Database;
use Altum\Language;
use Altum\Middlewares\Csrf;
use Altum\Logger;
use Altum\Routing\Router;
use Altum\Response;

?>

<style>
    .loginEtsy {
        position: relative;
        top: 4em;
        text-align: center;
    }
</style>

<?php

class EtsyConnect extends Controller {

    public function index()
    {
        Authentication::guard();
        $serviceFactory = new \OAuth\ServiceFactory();
        // Create a new instance of the URI class with the current URI, stripping the query string
        $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
        $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $currentUri->setQuery('');
        
        // Session storage
        $storage = new Session();
        $servicesCredentials['etsy']['key'] = "nohr7j8dl2h4q2my3f2gfsyx";
        $servicesCredentials['etsy']['secret'] = "bbohjvxhzz";
        // $currentURL = 'http://localhost/linkinbio/' . 'etsy_connect';
        $currentURL = 'https://staging.linkinbio.is/' . 'etsy_connect';
        // $currentURL = 'https://linkinbio.is/' . 'etsy_connect';


        // Setup the credentials for the requests
        $credentials = new Credentials(
            $servicesCredentials['etsy']['key'],
            $servicesCredentials['etsy']['secret'],
            $currentURL
        );

        // Instantiate the Etsy service using the credentials, http client and storage mechanism for the token
        /** @var Etsy $etsyService */
        $etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
        
        if (!empty($_GET['oauth_token'])) {
            $token = $storage->retrieveAccessToken('Etsy');
            $access_token = $token->getRequestTokenSecret();
            // This was a callback request from Etsy, get the token
            $etsyService->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $access_token
            );
        
            // Send a request now that we have access token
            $result = json_decode($etsyService->request('/private/users/__SELF__'));
            $shop = json_decode($etsyService->request('/private/shops/__SELF__'));
            $shopName = $shop->results[0]->shop_name;
            $requestURL = '/shops' .'/' . $shopName. '/listings/active?includes=MainImage';
            $listings = json_decode($etsyService->request($requestURL));
            $nonce = string_generate(10);

            // $listings = json_decode($etsyService->request('/listings/active?fields=listing_id,user_id,title,price,quantity,sku,url'));
            
            $products_arr = [];
            foreach($listings->results as $key => $listing) {
                $product = [
                    'id' => $listing->listing_id,
                    'title' => $listing->title,
                    'body_html' => $listing->description,
                    'vendor' => $shopName,
                    'handle' => $listing->url,
                    'variants' => [
                        [
                            'id' => $listing->user_id,
                            'product_id' => $listing->listing_id,
                            'title' => $listing->title,
                            'price' => $listing->price,
                            'sku' => $listing->sku,
                            'url' => $listing->url,
                        ]
                    ],
                    'options' => [
                        [
                            'id' => $listing->user_id,
                            'product_id' => $listing->listing_id,
                            'name' => 'Title',
                            'position' => 1,
                            'values' => ['Default Title']
                        ]
                    ],
                    'image' => [
                        'id' => $listing->user_id,
                        'product_id' => $listing->listing_id,
                        'position' => 1,
                        'src' => $listing->MainImage->url_fullxfull,
                    ]
                ];
                array_push($products_arr, $product);
            }
            
            $prodictObj = new \stdClass();
            $prodictObj->products = $products_arr;
            $products_ = json_encode($prodictObj);
            $products = json_encode($products_);
            $marketplace = 'etsy';

            $shopify_link_id = \Altum\Middlewares\Csrf::get('shopify_link_id');
            $user_id = \Altum\Middlewares\Csrf::get('user_id');

            if(!$shop_db = Database::simple_get('shop', 'shopify_tokens', ['user_id' => $user_id, 'shopify_link_id' => $shopify_link_id])) {
               
                $stmt = Database::$database->prepare("INSERT INTO `shopify_tokens` (`user_id`, `shopify_link_id`, `access_token`, `nonce`, `shop`, `products`, `marketplace`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssss', $user_id, $shopify_link_id, $access_token, $nonce, $shopName ,$products, $marketplace);
                $stmt->execute();
                $stmt->close();
                
            } else {
    
                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `access_token` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
                $stmt->bind_param('sss', $access_token, $user_id, $shopify_link_id);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `nonce` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
                $stmt->bind_param('sss', $nonce, $user_id, $shopify_link_id);
                $stmt->execute();
                $stmt->close();
                
                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `shop` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
                $stmt->bind_param('sss', $shopName, $user_id, $shopify_link_id);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `products` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
                $stmt->bind_param('sss', $products, $user_id, $shopify_link_id);
                $stmt->execute();
                $stmt->close();

                $stmt = Database::$database->prepare("UPDATE `shopify_tokens` SET `marketplace` = ? WHERE `user_id` = ? AND `shopify_link_id` = ?");
                $stmt->bind_param('sss', $marketplace, $user_id, $shopify_link_id);
                $stmt->execute();
                $stmt->close();
            }

            $biolink_id = Database::simple_get('biolink_id', 'links', ['link_id' => $shopify_link_id]);

            $_SESSION['save_status'] = true;
            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
            $redirectURL = url('link/' . $biolink_id . '?tab=links');

            header("Location: " . $redirectURL);
            die();

        } elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
            $response = $etsyService->requestRequestToken();
            $extra = $response->getExtraParams();
            $url = $extra['login_url'];
            header('Location: ' . $url);
        } else {
            $url = $currentURL. '?go=go';
            echo "<div class='loginEtsy'><a href='$url'><h2>Continue to Login with Etsy!</h2></a></div>";
        }
        exit(1);
    }
}