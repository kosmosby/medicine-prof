<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once dirname(__FILE__).'/OpenFireService.php';

class PhonesService{
    var $phoneUtil;
    var $ofService;

    public function __construct($ofService=null){
        $this->phoneUtil =  \libphonenumber\PhoneNumberUtil::getInstance();
        if($ofService==null) {
            $this->ofService = new OpenFireService();
        }else{
            $this->ofService = $ofService;
        }
    }

    public function findExistingContacts($user, $contactNames, $contactPhones){
        $arr = explode('@', $user);
        $phone = $arr[0];

        for($i = 0 ; $i < count($contactPhones); $i++){
            $contactPhones[$i] = explode("=", $contactPhones[$i]);
        }

        if($phone[0]!='+'){
            $phone = '+'.$phone;
        }

        try {
            $numberProto = $this->phoneUtil->parse($phone, null);
            $countryCode = $numberProto->getCountryCode();
            $regionCode = $this->phoneUtil->getRegionCodeForCountryCode($countryCode);
        } catch (\libphonenumber\NumberParseException $e) {
            echo json_encode(array('status'=>'BAD_PHONE'));
            exit;
        }

        $contacts = array();
        for($i=0;$i<count($contactPhones);$i++){
            $contacts[] = array("phone"=>$contactPhones[$i], "name"=>$contactNames[$i]);
        }
        $preparedPhones = array();

        foreach ($contacts as &$contact) {
            try {
                $contact['phoneCanonical'] = array();
                for($i = 0 ; $i < count($contact["phone"]); $i++) {
                    $contactPhone = $contact["phone"][$i];
                    $numberProto = $this->phoneUtil->parse($contactPhone, $regionCode);
                    $countryCode = $numberProto->getCountryCode();
                    $nationalNumber = $numberProto->getNationalNumber();
                    $preparedPhones[] = $countryCode . $nationalNumber;
                    $contact['phoneCanonical'][] = $countryCode . $nationalNumber;
                }

            } catch (\libphonenumber\NumberParseException $e) {
                //do nothing. just skip this phone.
            }
        }



        $result = $this->ofService->filterContacts($preparedPhones, $user);

        foreach ($contacts as $key => $contact) {
            $contactFound = false;
            for($i = 0 ; $i < count($contact["phoneCanonical"]); $i++) {
                if (in_array($contact["phoneCanonical"][$i], $result)) {
                    $contacts[$key]['jabberUsername'] = $contact["phoneCanonical"][$i] . "@medicine-prof.com";
                    $contacts[$key]['contactAdded'] = false;
                    $contacts[$key]['contactExists'] = true;
                    $contactFound = true;
                }
            }
            if(!$contactFound){
                $contacts[$key]['jabberUsername'] = null;
                $contacts[$key]['contactAdded'] = false;
                $contacts[$key]['contactExists'] = false;
            }
        }
        return array('status'=>'OK',
            'contacts'=>$contacts);
    }
}