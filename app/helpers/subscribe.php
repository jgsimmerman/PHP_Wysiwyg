<?php

    $subscribe_flag = false;

    if (isset($_GET['hub_challenge'])) {
        
        echo $_REQUEST['hub_challenge'];
        $subscribe_flag = true;
        $logMsg = "Subscription; user: ";
        // file_put_contents('./server_subscribe.log', $logMsg, FILE_APPEND | LOCK_EX);

    } else {

        $subscribe_flag = false;
        $video = parseYoutubeUpdate(file_get_contents('php://input'));
        $formatted_video = json_decode($video);

        $logMsg = json_encode($video);
        // file_put_contents('./curldelete.log', $logMsg, FILE_APPEND | LOCK_EX);

    }

    function parseYoutubeUpdate($data) {

        $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        $video_id = substr((string)$xml->entry->id, 9);
        $channel_id = substr((string)$xml->entry->author->uri, 32);
        $published = (string)$xml->entry->published;

        return json_encode([
            'video_id'=>$video_id,
            'channel_id'=>$channel_id,
            'published'=>$published
        ]);
    }

    if (!$subscribe_flag) {

        $video_id = $formatted_video->video_id;
        $channel_id = $formatted_video->channel_id;

        $logMsg = $video_id . strval(empty($video_id));
        $logMsg = $channel_id;

        // file_put_contents('./curl.log', $logMsg, FILE_APPEND | LOCK_EX);

        if (empty($video_id)) {

            $data = '{
                "video_id": "removed_video",
                "channel_id": "removed_video_channel"
            }';
        } else {

            $data = '{
                "video_id": "'.$video_id.'",
                "channel_id": "'.$channel_id.'"
              }';
        }

   
        $ch = curl_init();
            
        curl_setopt_array($ch, array(
            CURLOPT_URL => "https://staging.linkinbio.is/subscribe",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$data,
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                // "Accept: application/json"
            ),
        ));

        // curl_setopt_array($ch, array(
        //     CURLOPT_URL => "https://linkinbio.is/subscribe",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS =>$data,
        //     // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE),
        //     CURLOPT_HTTPHEADER => array(
        //         "Content-Type: application/json",
        //         // "Accept: application/json"
        //     ),
        // ));

        $response = curl_exec($ch);
        curl_close($ch);

    }

?>
