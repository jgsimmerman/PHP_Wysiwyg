<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;
    use MaxMind\Db\Reader;
    use Altum\Middlewares\Csrf;
    use Altum\Response;

    class YoutubeSubscribe extends Controller {

        public function index() {

            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);

            if(!empty($rawData)) {
                
                $video_id = $rawData->video_id;
                $channel_id = $rawData->channel_id;
                $api_key = 'AIzaSyAl1n4RR6dlA2Fwn3bNfpVSuoKwottcpGI';

                if ($video_id === "removed_video") {

                    $logMsg = $video_id;

                    $datatable = new \Altum\DataTable();
                    $datatable->set_accepted_columns(['biolink_id', 'domain_id', 'subtype', 'url', 'location_url', 'settings', 'start_date', 'end_date', 'video_id', 'channel_id']);
                    $result = Database::$database->query("SELECT `subtype`, `location_url`, `video_id`, `channel_id` FROM `links` WHERE `subtype` = 'youtube'");
                    
                    while($row = $result->fetch_object()):

                        $youtube_id = $row->video_id;
                        $channel_id =$row->channel_id;

                        $logMsg = $youtube_id;
                        // file_put_contents('./curlremovecheck_old.log', $logMsg, FILE_APPEND | LOCK_EX);

                        $check_url = "https://www.googleapis.com/youtube/v3/videos?part=id&id={$youtube_id}&key={$api_key}";
                        $json = file_get_contents($check_url);
                        $listFromYouTube=json_decode($json);
                        $youtube_item = $listFromYouTube->items[0];

                        if($youtube_item == null ) {

                            $upload_url = "https://www.googleapis.com/youtube/v3/channels?id={$channel_id}&key={$api_key}&part=contentDetails";
                            $json_upload = file_get_contents($upload_url);
                            $formatted_json_upload = json_decode($json_upload);
                            $upload_id = $formatted_json_upload->items[0]->contentDetails->relatedPlaylists->uploads;
                            
                            $logMsg = $upload_id;
                            // file_put_contents('./curlremovetest_new.log', $logMsg, FILE_APPEND | LOCK_EX);

                            // https://www.googleapis.com/youtube/v3/playlistItems?playlistId={"uploads" Id}&key={API key}&part=snippet&maxResults=50
                            
                            $json_url="https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=".$upload_id."&key=".$api_key;
                            $json = file_get_contents($json_url);
                            $listFromYouTube=json_decode($json);
                            $new_video_id = $listFromYouTube->items[1]->snippet->resourceId->videoId;

                            $logMsg_ = $new_video_id;
                            // file_put_contents('./curlremovetest_new.log', $logMsg_, FILE_APPEND | LOCK_EX);
                            $latest_youtube_url = "https://youtu.be/" . $new_video_id;

                            $datatable = new \Altum\DataTable();
                            $datatable->set_accepted_columns(['user_id', 'video_id']);

                            // $result = Database::$database->query("SELECT `user_id` FROM `links` WHERE `video_id` = '{$youtube_id}'");

                            // while($row = $result -> fetch_object()):
        
                            //     $user_id = $row->user_id;
                            //     $mail = Database::simple_get('email', 'users', ['user_id' => $user_id]);
                            //     // file_put_contents('./channelid.log', $mail, FILE_APPEND | LOCK_EX);

                            //     Database::insert('sendmails', [
                            //         'email'   => $mail,
                            //         'status'  => 'removed'
                            //     ]);

                            //     $msg = "A video of biolink has been removed from the channel you subscribed!";
                            //     $msg = wordwrap($msg,70);
                            //     mail($mail,"Youtube",$msg);
        
                            // endwhile;
        
                            Database::update(
                                'links',
                                [
                                    'location_url' => $latest_youtube_url,
                                    'video_id' => $new_video_id
                                ],
                                [
                                    'video_id' => $youtube_id
                                ]
                            );

                        }
                        
                    endwhile;

                } else {

                    $datatable = new \Altum\DataTable();
                    $datatable->set_accepted_columns(['biolink_id', 'user_id', 'video_id', 'channel_id']);
                    $result = Database::$database->query("SELECT `user_id` FROM `links` WHERE `channel_id` = '{$channel_id}'");
                    while($row = $result -> fetch_object()):

                        $user_id = $row->user_id;
                        $mail = Database::simple_get('email', 'users', ['user_id' => $user_id]);
                        // file_put_contents('./dboperation.log', $mail, FILE_APPEND | LOCK_EX);

                        // Database::insert('sendmails', [
                        //     'email'   => $mail,
                        //     'status'  => 'update'
                        // ]);

                        // $msg = "A new video has been uploaded in the channel you subscribed!";
                        // $msg = wordwrap($msg, 70);
                        // mail($mail, "Youtube", $msg);

                    endwhile;

                    $latest_youtube_url = "https://youtu.be/" . $video_id;
    
                    Database::update(
                        'links',
                        [
                            'location_url' => $latest_youtube_url,
                            'video_id' => $video_id
                        ],
                        [
                            'channel_id' => $channel_id
                        ]
                    );

                }
                
            }
    
            exit(1);

        }
    }

?>