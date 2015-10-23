<?php
class HttpClient{
    public function post($url, $postData){
        $rCurl = curl_init($url);
        curl_setopt($rCurl, CURLOPT_HEADER, 0);
        curl_setopt($rCurl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurl, CURLOPT_POST, 1);
        $sAnswer = curl_exec($rCurl);
        curl_close($rCurl);
        return $sAnswer;
    }
}