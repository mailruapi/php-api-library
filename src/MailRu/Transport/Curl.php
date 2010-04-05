<?php

require_once 'MailRu.php';
require_once 'MailRu/ITransport.php';

class MailRu_Transport_Curl implements MailRu_ITransport {
    private $useragent;
    private $apiBaseUrl;

    public function __construct($apiBaseUrl = null) {
        $this->apiBaseURL = $apiBaseUrl ? $apiBaseUrl : self::DEFAULT_API_BASE_URL;
        $this->useragent = 'Mail.Ru API PHP5 Client v. ' . MailRu::LIBRARY_VERSION . ' (curl) ' . phpversion();
    }

    public function get($params) {
        $requestURL = $this->apiBaseURL . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestURL);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
