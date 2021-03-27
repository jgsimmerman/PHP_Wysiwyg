<?php

    namespace Altum;

    class Youtube {

        public static $id;

        public function parse_channel_url(string $url): ?string {

            $parsed = parse_url(rtrim($url, '/'));

            if (isset($parsed['path']) && preg_match('/^\/channel\/(([^\/])+?)$/', $parsed['path'], $matches)) {

                $return = [
                    'type' => 'standard',
                    'url' => $matches[1]
                ];
                return json_encode($return);

            } else if (isset($parsed['path']) && preg_match('/^\/c\/(([^\/])+?)$/', $parsed['path'], $matches)) {

                $return = [
                    'type' => 'custom',
                    'url' => $matches[1]
                ];
                return json_encode($return);

            } else if(isset($parsed['path']) && preg_match('/^\/user\/(([^\/])+?)$/', $parsed['path'], $matches)) {

                $return = [
                    'type' => 'username',
                    'url' => $matches[1]
                ];

                return json_encode($return);

            } else if(isset($parsed['path']) && preg_match('/^\/(([^\/])+?)$/', $parsed['path'], $matches)) {

                $return = [
                    'type' => 'only_uname',
                    'url' => $matches[1]
                ];

                return json_encode($return);

            }

            throw new Exception("{$url} is not a valid YouTube channel URL");
            return null;
        }

        public static function get_id($channel_url, $nth_youtube) {

            $channel_id = '';
            $api_key = 'AIzaSyAl1n4RR6dlA2Fwn3bNfpVSuoKwottcpGI';

            $channel_url = self::parse_channel_url($channel_url);
            $formatted_channel_url = json_decode($channel_url);

            if ( $formatted_channel_url->type === "standard" ) {

                $channel_id = $formatted_channel_url->url;

            } else if ( ($formatted_channel_url->type === "custom") || ($formatted_channel_url->type === "username") || ($formatted_channel_url->type === "only_uname") ) {

                $channel_custom = $formatted_channel_url->url;
                $custom_url = "https://www.googleapis.com/youtube/v3/search?part=id%2Csnippet&q={$channel_custom}&type=channel&key={$api_key}";
                $json_custom_channel = file_get_contents($custom_url);
                $formatted_json_custom_channel = json_decode($json_custom_channel);
                $channel_id = $formatted_json_custom_channel->items[0]->snippet->channelId;

            }

            $channel_subscribe = self::subscribeYoutubeChannel($channel_id);
            
            $upload_url = "https://www.googleapis.com/youtube/v3/channels?id={$channel_id}&key={$api_key}&part=contentDetails";
            $json_upload = file_get_contents($upload_url);
            $formatted_json_upload = json_decode($json_upload);
            $upload_id = $formatted_json_upload->items[0]->contentDetails->relatedPlaylists->uploads;

            // https://www.googleapis.com/youtube/v3/playlistItems?playlistId={"uploads" Id}&key={API key}&part=snippet&maxResults=50

            $json_url="https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=".$upload_id."&key=".$api_key;
            $json = file_get_contents($json_url);
            $listFromYouTube=json_decode($json);
            
            if($nth_youtube < count($listFromYouTube->items)) {
                $id = $listFromYouTube->items[$nth_youtube]->snippet->resourceId->videoId;
            } else {
                $id = $listFromYouTube->items[0]->snippet->resourceId->videoId;
            }
            
            $returnArr = [
                'video_id' => $id,
                'channel_id' => $channel_id
            ];

            return json_encode($returnArr);
        }

        public static function subscribeYoutubeChannel($channel_id = null, $subscribe = true) {

            $subscribe_url = "https://pubsubhubbub.appspot.com/subscribe";
            $topic_url = "https://www.youtube.com/xml/feeds/videos.xml?channel_id={$channel_id}";
            $callback_url = url() .'app/helpers/subscribe.php';

            $data = array(
                'hub.mode' => $subscribe ? 'subscribe' : 'unsubscribe',
                'hub.callback' => $callback_url,
                'hub.lease_seconds'=>60*60*24*365,
                'hub.topic'=> str_replace(array(
                    "{$channel_id}"
                ), array(
                    $channel_id
                ), $topic_url)
            );
        
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data)
                )
            );
        
            $context  = stream_context_create($opts);
            @file_get_contents($subscribe_url, false, $context);
        
            return preg_match("/20/i", $http_response_header[0]) === 1;
        }
    }

?>