<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/22/15
 * Time: 2:42 PM
 */
require_once dirname(__FILE__).'/PhoneVerifier.php';
require_once dirname(__FILE__).'/HttpClient.php';
require_once dirname(__FILE__).'/PhonesDao.php';
require_once dirname(__FILE__).'/SmsSenderLetsads.php';
require_once dirname(__FILE__).'/VerificationCodeGenerator.php';

class OpenFireService
{
    var $phoneVerifier;

    public function __construct(){
        $db = JFactory::getDbo();
        $phonesDao = new PhonesDao($db);
        $verificationCodeGenerator = new VerificationCodeGenerator();
        $httpClient = new HttpClient();
        $smsSender = new SmsSenderLetsads('375336377561', 'matrix1939', $httpClient);
        $this->phoneVerifier = new PhoneVerifier();
        $this->phoneVerifier->phonesDao = $phonesDao;
        $this->phoneVerifier->smsSender = $smsSender;
        $this->phoneVerifier->codeGenerator = $verificationCodeGenerator;
    }
    public function registerPhone($phoneNumber, $ipAddr, $name){
        return $this->phoneVerifier->sendVerificationCode($phoneNumber, $ipAddr, $name);
    }

    public function verifyCode($phoneNumber, $code){
        $verificationResult = $this->phoneVerifier->verifyCode($phoneNumber, $code);
        if( $verificationResult){
            return createOrUpdateUser($phoneNumber, $code, $verificationResult['name']);
        }else{
            return array('status'=>'BAD_CODE');
        }
    }

    public function createOrUpdateUser($login, $password, $name){
        $link = mysql_connect('localhost', 'root', 'staSPE8e');
        mysql_select_db('openfire', $link);
        $query = "SELECT 1 FROM ofUser WHERE username='".mysql_real_escape_string($login)."'";
        $res = mysql_query($query, $link);
        $userExists = mysql_result($res, 0);
        if($userExists=='1'){
            mysql_query("UPDATE ofUser
                        SET plainPassword='".mysql_real_escape_string($password)."',
                         name='".mysql_real_escape_string($name)."',
                         encryptedPassword=NULL
                         WHERE username='".mysql_real_escape_string($login)."'", $link);
        }else{
            $insertDate = round(microtime(true) * 1000);
            mysql_query("INSERT INTO ofUser
                         (username, name, plainPassword, encryptedPassword, creationDate, modificationDate)
                         VALUES(
                         '".mysql_real_escape_string($login)."',
                         '".mysql_real_escape_string($name)."',
                         '".mysql_real_escape_string($password)."',
                         NULL,
                         '$insertDate',
                         '$insertDate'
                         )", $link);
        }
        mysql_close($link);
        return array("status"=>"OK", "user"=>$login."@medicine-prof.com", "password"=>$password);
    }

    public function filterContacts($phones, $user){
        $user = mysql_real_escape_string($user);
        $result = array();
        if(count($phones)==0){
            return $result;
        }
        $link = mysql_connect('localhost', 'root', 'staSPE8e');
        mysql_select_db('openfire', $link);
        foreach($phones as $key=>$value){
            $phones[$key] = '\''.mysql_real_escape_string($value).'\'';
        }
        $query = "SELECT u.username
                  FROM ofUser u
                  WHERE u.username in (".implode(',', $phones).")
                    AND NOT EXISTS ( SELECT 1 FROM ofRoster r
                                     WHERE r.username=u.username
                                        AND r.jid='$user')
        ";
        $res = mysql_query($query, $link);
        $i = 0;
        while(($val=mysql_result($res,$i++))!=null){
            $result[] = $val;
        }

        mysql_close($link);
        return $result;
    }
}