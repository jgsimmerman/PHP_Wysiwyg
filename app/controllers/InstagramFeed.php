<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;
    use Altum\Middlewares\Csrf;
    use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    class InstagramFeed extends Controller {

        public function index() {
            
            $instagram = new InstagramBasicDisplay([
                'appId'         => '3537622526297343',
                'appSecret'     => '6435e7c3d1c883c7b129f11b9226810b',
                'redirectUri'   => url(). 'instagramfeed'
            ]);

            // Get the OAuth callback code
            $code = $_GET['code'];

            // Get the short lived access token (valid for 1 hour)
            $token = $instagram->getOAuthToken($code, true);

            // Exchange this token for a long lived token (valid for 60 days)
            $token = $instagram->getLongLivedToken($token, true);

            // Set user access token
            $instagram->setAccessToken($token);

            // Get the users profile
            $profile = $instagram->getUserProfile();
            $profile_name = $profile->username;
            $profile_pic_url = $this->getPhotos($profile_name);

            //Get  the Instagram Feed
            $medias = $instagram->getUserMedia('me', 12);

            
            /*  Get Likes and Comments Count Scraper Package 

            $instagramfeed = new \InstagramScraper\Instagram();
            $medias = $instagramfeed->getMedias($profile_name, 12);
            $mediaArr = array();
            
            foreach ($medias as $media) {

                $account = $media->getOwner();
                $id = $media->getId();
                $shortcode = $media->getShortCode();
                $timestamp = $media->getCreatedTime();
                $caption = $media->getCaption();
                $commentsCnt = $media->getCommentsCount();
                $likesCnt = $media->getLikesCount();
                $permanlink = $media->getLink();
                $imageUrl= $media->getImageHighResolutionUrl();
                $media_type = $media->getType();
                $ds_user_Id = $account->getId();
                $username = $account->getUsername();
                $fullname = $account->getFullName();
                $profile_img_url = $account->getProfilePicUrl();

                $media_obj = [
                    
                    'id'                => $id,
                    'shortcode'         => $shortcode,
                    'timestamp'         => $timestamp,
                    'caption'           => $caption,
                    'commentsCnt'       => $commentsCnt,
                    'likesCnt'          => $likesCnt,
                    'permanlink'        => $permanlink,
                    'imageUrl'          => $imageUrl,
                    'media_type'        => $media_type,
                    'ds_user_Id'        => $ds_user_Id,
                    'username'          => $username,
                    'fullname'          => $fullname,
                    'profile_img_url'   => $profile_img_url

                ];
                array_push($mediaArr, $media_obj);
            }

            */
            $db_username = Database::simple_get('name', 'users', ['user_id' => $this->user->user_id]);
            $dir = $db_username;

            if( is_dir(UPLOADS_PATH . 'instagramfeeds/' . $dir) === false ) {
                mkdir(UPLOADS_PATH . 'instagramfeeds/' . $dir);
            }

            $mediaArr = array();
            foreach ($medias->data as $key => $media) {

                if ($media->media_type == 'IMAGE' || $media->media_type == 'image' ) {

                    $caption = $media->caption;
                    $id = $media->id;
                    $media_type = $media->media_type;
                    $media_url = $media->media_url;
                    $permalink = $media->permalink;
                    $timestamp = $media->timestamp;
                    $username = $media->username;
    
                    /*  Resize image part...

                        Function that works well with images.
                        It takes the image and reduces its size to best fit. i.e If you have an image
                        that is 200 X 100 and you want a thumbnail of 75 X 50,
                        it first resizes the image to 100 X 50
                        and then takes out a portion 75 X 50 from then center of the input image.
                        So loads of image information is retained.
                        The corollary also holds if your input image is 100 X 200
                        it first resizes image to 75 X 150 and then takes out a
                        portion 75 X 75 from the centre
                        The advantage here is that function decides on whether
                        resize is by width or height itself.
                        it also decides whether to use the height or the width as the base start point
                        in the case that athumbnail is rectangular

                    */
                    $resized_image_name = string_generate(15);
                    $filein = $media_url;
                    
                    $fileout = UPLOADS_PATH . 'instagramfeeds/' . $dir. '/' .$resized_image_name .'.gif';
                    $file_path = url() . 'uploads/' . 'instagramfeeds/' . $dir .'/' .$resized_image_name .'.gif';
                    list($width, $height) = getimagesize($filein);
                    $size = min($width, $height);

                    $imagethumbsize_w = $size; // thumbnail size (area cropped in middle of image)
                    $imagethumbsize_h = $size; // thumbnail size (area cropped in middle of image)
                    $this->resize_then_crop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, /*rgb*/ "255", "255", "255" );

                    $media_obj = [
                        'caption'           => $caption,
                        'id'                => $id,
                        'media_type'        => $media_type,
                        'media_url'         => $file_path,
                        'permalink'         => $permalink,
                        'timestamp'         => $timestamp,
                        'username'          => $username,
                        'link'              => ''
                    ];
                    array_push($mediaArr, $media_obj);

                } else {
                    continue;
                }
            }
            
            $settings = json_encode([
                'profile_name' => $profile_name,
                'profile_pic_url' => $profile_pic_url,
                'medias' => json_encode($mediaArr)
            ]);

            $link_id = \Altum\Middlewares\Csrf::get('link_id');
            if(!$project_id = Database::simple_get('project_id', 'links', ['user_id' => $this->user->user_id, 'link_id' => $link_id, 'type' => 'biolink', 'subtype' => 'base'])) {
                die();
            }
            
            $new_order = 0;
            $url = string_generate(10);
            $type = 'biolink';
            $subtype = 'instagramfeed';

            /* Generate random url if not specified */
            while(Database::exists('link_id', 'links', ['url' => $url])) {
                $url = string_generate(10);
            }
            $loc_url = Database::simple_get('url', 'links', ['user_id' => $this->user->user_id, 'link_id' => $link_id, 'type' => 'biolink', 'subtype' => 'base']);
            $location_url = url() .$loc_url;

            if(!$loc_url = Database::simple_get('url', 'links', ['user_id' => $this->user->user_id, 'biolink_id' => $link_id, 'type' => 'biolink', 'subtype' => 'instagramfeed'])) {
               
                $stmt = Database::$database->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `order`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssssss', $project_id, $link_id, $this->user->user_id, $type, $subtype, $url, $location_url, $settings, $new_order, \Altum\Date::$date);
                $stmt->execute();
                $stmt->close();
                
            } else {

                $stmt = Database::$database->prepare("UPDATE `links` SET `settings` = ? WHERE `link_id` = ? AND `user_id` = ? AND `subtype` = ?");
                $stmt->bind_param('ssss', $settings, $link_id, $this->user->user_id, $subtype);
                $stmt->execute();
                $stmt->close();

            }

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            redirect('link/' . $link_id .'?tab=links');
        }

        private function getPhotos($profile_name) {
            
            $userUrl = 'https://i.instagram.com/api/v1/users/' .$profile_name .'/'. 'usernameinfo/';
            $profile_pic_url = json_decode($this->send_request($userUrl))->user->profile_pic_url;
            return $profile_pic_url;

            $pk = json_decode($this->send_request($userUrl))->user->pk;
        
            $resendUrl = "https://i.instagram.com/api/v1/feed/user/" . $pk . "/?ranked_content=true";
        
            $response = json_decode($this->send_request($resendUrl))->items;
        
            foreach ($response as $key => $val) {
                $post_url = $val->image_versions2->candidates[0]->url;
                $post_caption = $val->caption->text;
            }
        
        }

        private function send_request($url) {
                
            $csrftoken = '3LKJo4LMk0C1JJxY7d8KFqE2aZMODL5i;';
            $ds_user_id = '42639427405;';
            $sessionid = '42639427405%3AgfwE32t0b1PCtc%3A4;';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "User-Agent: Instagram 10.26.0 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US)",
                    "Cookie: ig_did=94093436-C55B-45B6-9B26-F9FA3658ECD5; mid=X3jT2QALAAGjbIxw0fc95CMvo0Ds; shbid=8404; ig_cb=1; rur=VLL; shbts=1602039940.1944256; csrftoken=" .$csrftoken ." ds_user_id=" .$ds_user_id ." sessionid=" .$sessionid. " urlgen=\"{\\\"103.254.155.26\\\": 59253\\054 \\\"61.75.61.129\\\": 4766}:1kQE1W:rz642umgWxq11YtVvvuNrDOBrC4\"; ig_did=88B2D02D-0C06-4294-9C59-1FE9412D4FB8; mid=X336dwAEAAFCaxj4gHPf3oCA68n0; ig_nrcb=1; csrftoken=NFUOPen18GTiN9YkO3fejPLVI9botzkW; rur=VLL; ds_user_id=17806930180; urlgen=\"{\\\"103.254.155.26\\\": 59253\\054 \\\"61.75.61.129\\\": 4766}:1kQE2L:c6t9-1RvXQY0f6c8uQJkbroaf7I\""
                ),
            ));
        
            $response = curl_exec($curl);
        
            curl_close($curl);
            return $response;
        }
            
        function resize_then_crop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue) {

            // Get new dimensions
            list($width, $height) = getimagesize($filein);
            // $new_width = $width * $percent;
            // $new_height = $height * $percent;

            if(preg_match("/.jpg/i", "$filein"))
            {
                $format = 'image/jpeg';
            }
            if (preg_match("/.gif/i", "$filein"))
            {
                $format = 'image/gif';
            }
            if(preg_match("/.png/i", "$filein"))
            {
                $format = 'image/png';
            }
            
            switch($format)
            {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($filein);
                    break;
                case 'image/gif';
                    $image = imagecreatefromgif($filein);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($filein);
                    break;
            }

            $width = $imagethumbsize_w ;
            $height = $imagethumbsize_h ;
            list($width_orig, $height_orig) = getimagesize($filein);

            if ($width_orig < $height_orig) {

                $height = ($imagethumbsize_w / $width_orig) * $height_orig;

            } else {
                
                $width = ($imagethumbsize_h / $height_orig) * $width_orig;
            }

            if ($width < $imagethumbsize_w) {
                //if the width is smaller than supplied thumbnail size
                $width = $imagethumbsize_w;
                $height = ($imagethumbsize_w / $width_orig) * $height_orig;
            }

            if ($height < $imagethumbsize_h) {
                //if the height is smaller than supplied thumbnail size

                $height = $imagethumbsize_h;
                $width = ($imagethumbsize_h / $height_orig) * $width_orig;
            }

            $thumb = imagecreatetruecolor($width , $height); 
            $bgcolor = imagecolorallocate($thumb, $red, $green, $blue);  
            ImageFilledRectangle($thumb, 0, 0, $width, $height, $bgcolor);
            imagealphablending($thumb, true);

            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
            $thumb2 = imagecreatetruecolor($imagethumbsize_w, $imagethumbsize_h);
            // true color for best quality
            $bgcolor = imagecolorallocate($thumb2, $red, $green, $blue);  
            // ImageFilledRectangle($thumb2, 0, 0, $imagethumbsize_w, $imagethumbsize_h, $white);
            imagealphablending($thumb2, true);

            $w1 =($width/2) - ($imagethumbsize_w/2);
            $h1 = ($height/2) - ($imagethumbsize_h/2);

            imagecopyresampled($thumb2, $thumb, 0,0, $w1, $h1, $imagethumbsize_w, $imagethumbsize_h, $imagethumbsize_w, $imagethumbsize_h);

            if ($fileout !="") {
                imagegif($thumb2, $fileout); //write to file
            }
            // header('Content-type: image/gif');
            // imagegif($thumb2); //output to browser
        }

    }