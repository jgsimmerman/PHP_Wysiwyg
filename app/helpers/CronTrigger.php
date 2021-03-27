<?php

    set_time_limit(0);

    parse_str($argv[1], $params);

    // file_put_contents('./cronjob.log', $params['key'], FILE_APPEND | LOCK_EX);

    $data = '{
        "key": "'.$params['key'].'"
      }';

      
    $ch = curl_init();
                
    curl_setopt_array($ch, array(
        CURLOPT_URL => "https://linkinbio.is/cron",
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
