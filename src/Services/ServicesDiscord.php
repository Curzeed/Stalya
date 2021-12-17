<?php

namespace App\Services;

class ServicesDiscord
{
    function logout($url, $data=array()) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));
        $response = curl_exec($ch);
        return json_decode($response);
    }
}