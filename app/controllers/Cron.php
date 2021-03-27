<?php

    namespace Altum\Controllers;

    use Altum\Database\Database;
    use Altum\Date;

    class Cron extends Controller {

        public function index() {

            set_time_limit(0);

            $fp = fopen('php://input', 'r');
            $origin_rawData = stream_get_contents($fp);
            $rawData = json_decode($origin_rawData);

            if(!empty($rawData)) {
                
                $key = $rawData->key;
            }
            
            /* Make sure the key is correct */
            if( $key != $this->settings->cron->key ) {
                
                die();

            } else {

                $datatable = new \Altum\DataTable();
                $datatable->set_accepted_columns(['user_id', 'date', 'temp_status']);
                $result = Database::$database->query("SELECT `user_id`, `date`, `temp_status` FROM `users` WHERE `temp_status` = 1");

                while($row = $result -> fetch_object()):

                    $current_date = Date::$date;
                    $db_date = $row->date;
                    $time_interval = Date::check_temp_expiration($current_date, $db_date);

                    if ($time_interval > 900 ) {

                        $stmt = Database::$database->prepare("DELETE FROM `users` WHERE `date` = ?");
                        $stmt->bind_param('s', $db_date);
                        $stmt->execute();
                        $stmt->close();

                    }

                    // file_put_contents('./cron.log', $time_interval. '--------'. $current_date, FILE_APPEND | LOCK_EX);

                endwhile;

            }

            die();
        }

    }
