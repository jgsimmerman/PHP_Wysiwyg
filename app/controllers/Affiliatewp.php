<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;

    class Affiliatewp extends Controller {

        public function index() {

            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);
		
            $user_id = $rawData->user_id;
            $affiliate_id = $rawData->affiliate_id;

            Database::insert_wp('affiliate_info', [
                'userwp_id' => $user_id,
                'affiliate_id' => $affiliate_id,
            ]);

            exit(1);

        }
    }

?>