<?php

class ActionResponse {

    public function response($code = 200, $status = "", $message = "") {
        http_response_code($code);
        if ($code != 200 && !empty($status) && !empty($message)) {
            $response = array("error_code" => $status, "text" => $message);
            echo json_encode($response, JSON_PRETTY_PRINT);
        }
    }

}