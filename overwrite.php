<?php
    use WhizApi\Services\WhizApiService;
    use WhizApi\Models\WhizApiConfigModel;

    $_key = WhizApiConfigModel::get_option_value('whiz_api_token');
    if ( !function_exists('wp_mail') ){
        function wp_mail($to, $subject, $message, $headers = '', $attachments = []){;
            try {
                $whiz_api = new WhizApiService();
                if (is_array($to)){
                    foreach($to as $to_){
                        $resp = [];
                        $resp[] = $whiz_api->send_email($to_, $subject, $message);
                    }
                }else{
                    $list = explode(",", $to);
                    foreach($list as $to_){
                        $resp = [];
                        $resp[] = $whiz_api->send_email($to_, $subject, $message);
                    }
                }
            }catch(Exception $e){
                return false;
            }
            return $resp;
        }
    }


