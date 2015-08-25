<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/22/15
 * Time: 11:43 AM
 */
class PhoneVerifier
{
    var $smsSender;
    var $phonesDao;
    var $codeGenerator;

    public function sendVerificationCode($phoneNumber, $ipAddress=null){
        $verificationCode = $this->codeGenerator->generate();
        $result = $this->smsSender->sms_send('Test', $phoneNumber, $verificationCode);
        if( $result == 'OK'){
            $this->phonesDao->createEntry($phoneNumber, $verificationCode, $ipAddress);
        }
        return $result;
    }

    public function verifyCode($phoneNumber, $verificationCode){
        if($this->phonesDao->isCodeValid($verificationCode, $phoneNumber)){
            $this->phonesDao->updateVerificationCode($verificationCode, $phoneNumber);
            return true;
        }else{
            return false;
        }
    }

}