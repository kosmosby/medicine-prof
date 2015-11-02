<?php

/**
 * Created by PhpStorm.
 * User: neurons
 * Date: 8/22/15
 * Time: 2:22 PM
 */
class PhonesDao
{
    var $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function createEntry($phoneNumber, $verificationCode, $ipAddress, $name){
        $this->db->setQuery(
            "INSERT INTO #__openfire_phones
            (phone, code, verified, ip_addr, name)
            VALUES
            ({$this->db->quote($phoneNumber)},
            {$this->db->quote($verificationCode)},
            0,
            {$this->db->quote($ipAddress)},
            {$this->db->quote($name)})");
        $this->db->query();
    }

    public function isCodeValid($verificationCode, $phoneNumber){
        $this->db->setQuery(
            "SELECT * FROM  #__openfire_phones
             WHERE phone={$this->db->quote($phoneNumber)}
                AND code={$this->db->quote($verificationCode)}
                AND verified=0");
        return $this->db->loadAssoc();
    }

    public function updateVerificationCode($verificationCode, $phoneNumber){
        $this->db->setQuery(
            "UPDATE #__openfire_phones
             SET verified=1
             WHERE phone={$this->db->quote($phoneNumber)}
                AND code={$this->db->quote($verificationCode)}
                AND verified=0");
        $this->db->query();
    }
}