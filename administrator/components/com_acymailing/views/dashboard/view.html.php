<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.0.0
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class dashboardViewDashboard extends acymailingView{

	function display($tpl = null){

		$config = acymailing_config();
		$doc = JFactory::getDocument();


		$acyToolbar = acymailing::get('helper.toolbar');
		$acyToolbar->help('dashboard');
		$acyToolbar->setTitle(JText::_('ACY_CPANEL'), 'dashboard');
		$acyToolbar->display();

		$db = JFactory::getDBO();

		$userQuery = 'SELECT (confirmed + enabled) AS addition, COUNT(subid) AS total FROM #__acymailing_subscriber GROUP BY addition';
		$db->setQuery($userQuery);
		$userResult = $db->loadObjectList('addition');

		$userStats = new stdClass();
		$userStats->nbUnconfirmedAndDisabled = (empty($userResult[0]->total) ? 0 : $userResult[0]->total);
		$userStats->nbConfirmed = (empty($userResult[1]->total) ? 0 : $userResult[1]->total);
		$userStats->nbConfirmed += (empty($userResult[2]->total) ? 0 : $userResult[2]->total);
		$userStats->total = $userStats->nbConfirmed + $userStats->nbUnconfirmedAndDisabled;

		$userStats->confirmedPercent = (empty($userStats->total) ? 0 : round((($userStats->nbConfirmed * 100) / $userStats->total), 0, PHP_ROUND_HALF_UP));

		$listsQuery = 'SELECT COUNT(DISTINCT listid) AS total FROM #__acymailing_listsub WHERE status=1';
		$db->setQuery($listsQuery);
		$atLeastOneSub = $db->loadResult();

		$db->setQuery('SELECT COUNT(listid) FROM #__acymailing_list WHERE type = "list"');
		$nbLists = $db->loadResult();

		$listStats = new stdClass();
		$listStats->atLeastOneSub = $atLeastOneSub;
		$listStats->noSub = $nbLists - $atLeastOneSub;
		$listStats->total = $nbLists;

		$listStats->subscribedPercent = (empty($nbLists) ? 0 : round((($atLeastOneSub * 100) / $nbLists), 0, PHP_ROUND_HALF_UP));

		$nlQuery = 'SELECT count(mailid) AS total, published FROM #__acymailing_mail WHERE type = "news" GROUP BY published';
		$db->setQuery($nlQuery);
		$nlResult = $db->loadObjectList('published');

		$nlStats = new stdClass();
		$nlStats->nbUnpublished = (empty($nlResult[0]->total) ? 0 : $nlResult[0]->total);
		$nlStats->nbpublished = (empty($nlResult[1]->total) ? 0 : $nlResult[1]->total);
		$nlStats->total = $nlStats->nbpublished + $nlStats->nbUnpublished;

		$nlStats->publishedPercent = (empty($nlStats->total) ? 0 : round((($nlStats->nbpublished * 100) / $nlStats->total), 0, PHP_ROUND_HALF_UP));


		$this->assignRef('nlStats', $nlStats);
		$this->assignRef('userStats', $userStats);
		$this->assignRef('listStats', $listStats);
		$this->assignRef('config', $config);




		$geolocParam = $config->get('geolocation');
		if(!empty($geolocParam) && $geolocParam != 1){
			$condition = '';
			if(strpos($geolocParam, 'creation') !== false){
				$condition = " WHERE geolocation_type='creation'";
			}

			$db = JFactory::getDBO();
			$query = 'SELECT geolocation_type, geolocation_subid, geolocation_country_code, geolocation_city, geolocation_country, geolocation_state';
			$query .= ' FROM #__acymailing_geolocation'.$condition.' GROUP BY geolocation_subid ORDER BY geolocation_created DESC LIMIT 100';
			$db->setQuery($query);
			$geoloc = $db->loadObjectList();

			if(!empty($geoloc)){
				$markCities = array();
				$diffCountries = false;
				$dataDetails = array();
				$addresses = array();
				foreach($geoloc as $mark){
					$indexCity = array_search($mark->geolocation_city, $markCities);
					if($indexCity === false){
						array_push($markCities, $mark->geolocation_city);
						array_push($dataDetails, 1);
						$addresses[] = $mark->geolocation_city.' '.$mark->geolocation_state.' '.$mark->geolocation_country;
					}else{
						$dataDetails[$indexCity] += 1;
					}

					if(!$diffCountries){
						if(!empty($region) && $region != $mark->geolocation_country_code){
							$region = 'world';
							$diffCountries = true;
						}else{
							$region = $mark->geolocation_country_code;
						}
					}
				}
				$this->assignRef('geoloc_city', $markCities);
				$this->assignRef('geoloc_details', $dataDetails);
				$this->assignRef('geoloc_region', $region);
				$this->assignRef('geoloc_addresses', $addresses);
			}
		}

		$doc->addScript("https://www.google.com/jsapi");
		$today = acymailing_getTime(date('Y-m-d'));
		$joomConfig = JFactory::getConfig();
		$offset = ACYMAILING_J30 ? $joomConfig->get('offset') : $joomConfig->getValue('config.offset');
		$diff = date('Z') + intval($offset * 60 * 60);
		$db->setQuery("SELECT count(`subid`) as total, DATE_FORMAT(FROM_UNIXTIME(`created` - $diff),'%Y-%m-%d') as subday FROM ".acymailing_table('subscriber')." WHERE `created` > 100000 GROUP BY subday ORDER BY subday DESC LIMIT 15");
		$statsusers = $db->loadObjectList();
		$this->assignRef('statsusers', $statsusers);

		$db = JFactory::getDBO();
		$db->setQuery('SELECT name,email,html,confirmed,subid,created FROM '.acymailing_table('subscriber').' ORDER BY subid DESC LIMIT 10');
		$users10 = $db->loadObjectList();
		$this->assignRef('users', $users10);

		$toggleClass = acymailing_get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);


		$listStatusQuery = 'SELECT count(subid) AS total, list.name AS listname, list.listid, listsub.status FROM #__acymailing_list AS list JOIN #__acymailing_listsub AS listsub ON list.listid = listsub.listid GROUP BY listsub.listid, listsub.status';
		$db->setQuery($listStatusQuery);
		$listStatusResult = $db->loadObjectList();

		$listStatusData = array();
		foreach($listStatusResult as $oneResult){
			$listStatusData[$oneResult->listname][$oneResult->status] = $oneResult->total;
		}
		$this->assignRef('listStatusData', $listStatusData);


		$db->setQuery("SELECT count(userstats.`mailid`) as total,  userstats.sent, DATE_FORMAT(FROM_UNIXTIME(`senddate` - $diff), '%Y-%m-%d') AS senddate
							FROM ".acymailing_table('userstats')." AS userstats
							WHERE userstats.senddate > ".intval(time() - 2628000)."
							GROUP BY userstats.sent, senddate
							ORDER BY senddate DESC");

		$newsletters = $db->loadObjectList();
		$this->assignRef('newsletters', $newsletters);


		$progressBarSteps = new stdClass();
		$progressBarSteps->listCreated = (!empty($listStats->total) ? 1 : 0);
		$progressBarSteps->contactCreated = (!empty($userStats->total) ? 1 : 0);
		$progressBarSteps->newsletterCreated = (!empty($nlStats->total) ? 1 : 0);

		$db->setQuery('SELECT subid FROM #__acymailing_userstats LIMIT 1');
		$result = $db->loadResult();

		$progressBarSteps->newsletterSent = (!empty($result) ? 1 : 0);
		$this->assignRef('progressBarSteps', $progressBarSteps);

		parent::display($tpl);
	}
}
