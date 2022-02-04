<?php

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ServicesDiscord
    /**
     * @param string $code
     * @return array
     */
{
    public function tryGetToken(string $code): array
    {
        $fields = array(
            'client_id' => $_SERVER['DISCORD_CLIENT_ID'],
            'client_secret' => $_SERVER['DISCORD_CLIENT_SECRET'],
            'code' => $code,
            'redirect_uri' => UrlGeneratorInterface::ABSOLUTE_URL.'/profile',
            'grant_type' => 'authorization_code'
        );

        $ch = curl_init();

        if ($_SERVER['APP_ENV'] === 'dev') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($ch, CURLOPT_URL, $_SERVER['DISCORD_API_BASE_URL'] . 'oauth2/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));

        $result = json_decode(curl_exec($ch), true);
        $result['result_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $access_token
     * @return array
     * @throws \Exception
     */
    public function getUserInfos(string $access_token): array
    {
        if (empty($access_token))
            throw new \Exception("Token invalide");

        $ch = curl_init();

        if ($_SERVER['APP_ENV'] === 'dev') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($ch, CURLOPT_URL, $_SERVER['DISCORD_API_BASE_URL'] . '/users/@me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));

        $result = json_decode(curl_exec($ch), true);
        $result['result_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result;
    }

    function logout($access_token)
    {
        $url = $_SERVER['DISCORD_API_BASE_URL'] . 'token/revoke';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => http_build_query(array($access_token)),
        ));
        $response = curl_exec($ch);
        return json_decode($response);
    }

    function tryGetToken2(string $code){
        $fields = array(
            'client_id' => $_SERVER['DISCORD_CLIENT_ID'],
            'client_secret' => $_SERVER['DISCORD_CLIENT_SECRET'],
            'code' => $code,
            'redirect_uri' => UrlGeneratorInterface::ABSOLUTE_URL . '/profile',
            'grant_type' => 'authorization_code'
        );
        $client = HttpClient::create();


    }
}