<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/22/15
 * Time: 12:21 PM
 */
class VerificationCodeGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate(){
        require_once 'classes/VerificationCodeGenerator.php';
        $codeGenerator = new VerificationCodeGenerator();
        $code = $codeGenerator->generate();
        $this->assertEquals($codeGenerator->codeLength, strlen($code));
        $this->assertTrue(preg_match('/[A-Z0-9]+/', $code) == 1);
    }
}
