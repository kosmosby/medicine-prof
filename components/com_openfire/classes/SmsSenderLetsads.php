<?php
class SmsSenderLetsads{
    var $user;
    var $password;
    var $httpClient;

    public function __construct($user, $password, $httpClient=null){
        $this->user = $user; //375336377561
        $this->password = $password; //matrix1939
        $this->httpClient = $httpClient;
    }

    public function sms_send($from, $to, $text)
    {
        $sUrl = 'http://letsads.com/api';
        $sXML =
            '<?xml version="1.0" encoding="UTF-8"?>
  <request>
      <auth>
        <login>'.$this->user.'</login>
        <password>'.$this->password.'</password>
        </auth>
        <message>
          <from>' . $from . '</from>
          <text>' . $text . '</text>
          <recipient>' . $to . '</recipient>
        </message>
  </request>';

        $responseText = $this->httpClient->post($sUrl, $sXML);
        $status = "UNKNOWN";
        if ($responseText) {
            $response = new SimpleXMLElement($responseText);
            if($response->name=='Complete'){
                $status='OK';
            }
            if($response->name=='Error'){
                $status = $response->description;
            }
        }
        return $status;
    }
}


