<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/22/15
 * Time: 12:04 PM
 */
class VerificationCodeGenerator
{
    var $codeLength = 4;
    var $validCharacters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    public function generate(){
        $result='';
        $charactersLength = strlen($this->validCharacters);
        for($i = 0 ; $i < $this->codeLength ; $i++){
            $result .= $this->validCharacters[rand(0, $charactersLength - 1)];
        }
        return $result;
    }
}