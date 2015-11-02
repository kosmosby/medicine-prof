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

    public function sendVerificationCode($phoneNumber, $ipAddress=null, $name){
        $verificationCode = $this->codeGenerator->generate();
        $result = $this->smsSender->sms_send('Test', $phoneNumber, $verificationCode);
        if( $result == 'OK'){
            $this->phonesDao->createEntry($phoneNumber, $verificationCode, $ipAddress, $name);
        }
        return $result;
    }

    public function verifyCode($phoneNumber, $verificationCode){
        $result = $this->phonesDao->isCodeValid($verificationCode, $phoneNumber);
        if($result){
            $this->phonesDao->updateVerificationCode($verificationCode, $phoneNumber);
        }
        return $result;
    }

}