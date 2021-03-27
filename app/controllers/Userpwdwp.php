<?php

    namespace Altum\Controllers;

    use Altum\Captcha;
    use Altum\Database\Database;
    use Altum\Language;
    use Altum\Logger;
    use Google;
    use Altum\Middlewares\Authentication;

    class Userpwdwp extends Controller
    {

        public function index()
        {
            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);
            
            $password_origin = $rawData->password;
            file_put_contents('./newpwd.log', $password_origin , FILE_APPEND | LOCK_EX);
            $name = $rawData->name;
            $username = str_replace('.', '', $name);
            $password = password_hash($password_origin, PASSWORD_DEFAULT);

            if(Database::exists('user_id', 'users', ['name' => $username])) {
                    
                Database::update(
                    'users',
                    [
                        'password' => $password,
                    ],
                    [
                        'name' => $username
                    ]
                );

                print_r("user package update success");
                exit(1);
            }

        }

    }