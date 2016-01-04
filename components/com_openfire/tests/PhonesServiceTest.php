<?php

require_once __DIR__.'/../classes/PhonesService.php';
require_once __DIR__.'/../classes/OpenFireService.php';

class PhonesServiceTest extends PHPUnit_Framework_TestCase
{
    public function testSinglePhoneNotExistingContact()
    {
        $openFireService = $this->getMockBuilder('OpenFireService')
            ->disableOriginalConstructor()
            ->setMethods(array('filterContacts'))
            ->getMock();
        $openFireService->expects($this->once())
            ->method('filterContacts')
            ->with($this->equalTo(array('375333333333')), $this->equalTo('375333456789'))
            ->will($this->returnValue(array()));

        $phonesService = new PhonesService($openFireService);

        $result = $phonesService->findExistingContacts("375333456789", array("test"), array("375333333333"));

        $this->assertEquals("OK", $result["status"]);
        $this->assertEquals(Array
        (
            Array
            (
                "phone" => Array(375333333333),
                "name" => "test",
                "phoneCanonical" => Array(375333333333),
                "jabberUsername" => null,
                "contactAdded" => false,
                "contactExists" => false
            )

        ), $result["contacts"]);


    }

    public function testMultiplePhoneExistingContact()
    {
        $openFireService = $this->getMockBuilder('OpenFireService')
            ->disableOriginalConstructor()
            ->setMethods(array('filterContacts'))
            ->getMock();
        $openFireService->expects($this->once())
            ->method('filterContacts')
            ->with($this->equalTo(array('375333333333', '375293333333')), $this->equalTo('375333456789'))
            ->will($this->returnValue(array("375293333333")));

        $phonesService = new PhonesService($openFireService);

        $result = $phonesService->findExistingContacts("375333456789", array("test"), array("375333333333=375293333333"));

        $this->assertEquals("OK", $result["status"]);
        $this->assertEquals(Array
        (
            Array
            (
                "phone" => Array(375333333333, 375293333333),
                "name" => "test",
                "phoneCanonical" => Array(375333333333, 375293333333),
                "jabberUsername" => '375293333333@medicine-prof.net',
                "contactAdded" => false,
                "contactExists" => true
            )

        ), $result["contacts"]);


    }
}