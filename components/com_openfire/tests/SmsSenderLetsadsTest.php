<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/21/15
 * Time: 5:54 PM
 */
require_once 'classes/SmsSenderLetsads.php';
require_once 'classes/HttpClient.php';
class SmsSenderLetsadsTest extends PHPUnit_Framework_TestCase
{
    public function testErrorResponse(){

        $httpClientStub = $this->getMockBuilder('HttpClient')->getMock();
        $httpClientStub->method('post')->willReturn(
            '<?xml version="1.0" encoding="UTF-8"?>
            <response>
                <name>Error</name>
                <description>INVALID_FROM</description>
            </response>');
        $smsSender = new SmsSenderLetsads('user', 'password', $httpClientStub);
        $this->assertEquals('INVALID_FROM', $smsSender->sms_send('aaa', 'bbb', 'ccc'));
    }

    public function testOkResponse(){
        $httpClientStub = $this->getMockBuilder('HttpClient')->getMock();
        $httpClientStub->method('post')->willReturn(
            '<?xml version="1.0" encoding="UTF-8"?>
<response><name>Complete</name><description>2 messages put into queue</description><sms_id>55006990</sms_id><sms_id>55006991</sms_id></response>
');
        $smsSender = new SmsSenderLetsads('user', 'password', $httpClientStub);
        $this->assertEquals('OK', $smsSender->sms_send('aaa', 'bbb', 'ccc'));
    }

    public function testRequestBody(){
        $httpClientStub = $this->getMockBuilder('HttpClient')
            ->setMethods(array('post'))
            ->getMock();
        $httpClientStub->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('http://letsads.com/api'),
                $this->equalTo('<?xml version="1.0" encoding="UTF-8"?>
  <request>
      <auth>
        <login>user</login>
        <password>password</password>
        </auth>
        <message>
          <from>' . 'aaa' . '</from>
          <text>' . 'ccc' . '</text>
          <recipient>' . 'bbb' . '</recipient>
        </message>
  </request>')
            );

        $smsSender = new SmsSenderLetsads('user', 'password', $httpClientStub);
        $smsSender->sms_send('aaa', 'bbb', 'ccc');
    }
}
