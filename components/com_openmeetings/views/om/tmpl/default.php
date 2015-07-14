<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
		* "License") +  you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body bgcolor="#5a5152" text="#333333" link="#FF3366" LEFTMARGIN="0"
	TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0">
	<?php
	require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'om_gateway'.DS.'openmeetings_gateway.php');

	$user = JFactory::getUser();

	$openmeetings_gateway = new openmeetings_gateway();
	if ($openmeetings_gateway->openmeetings_loginuser()) {
		if ($user->authorise('core.admin') || $user->authorise('core.manage')) {
			$becomemoderator=1;
		} else {
			$becomemoderator=0;
		}

		$showAudioVideoTest=1;

		if ($user->guest) {
			$roomhash = $openmeetings_gateway->openmeetings_setUserObjectAndGenerateRoomHash('public', 'public','', '', 'public', $user->id, $this->room_id, $becomemoderator, $showAudioVideoTest);
		}else{
			$roomhash = $openmeetings_gateway->openmeetings_setUserObjectAndGenerateRoomHash($user->username,$user->name,'', '', $user->email,$user->id, $this->room_id, $becomemoderator, $showAudioVideoTest);
		}


		$lang =& JFactory::getLanguage();
		$languages = array (
		'en-GB' => 1, 'de-DE' => 2, 'fr-FR' => 4, 'it-IT' => 5, 'pt-PT' => 6, 'pt-BR' => 7, 'es-ES' => 8, 'ru-RU' => 9, 'swedish' => 10,
		'ko-KR' => 13, 'ar-AA' => 14, 'ua-UA' => 18, 'nl-NL' => 27, 'ca-ES' => 29);

		//$om_laguage_id = $languages[$lang->getTag()];
			
		if (!empty($roomhash)) {
			$swfurl = $openmeetings_gateway->getOMUrl() .
			"/?" .
			"scopeRoomId=" . $this->room_id .
			"&secureHash=" .$roomhash.
			"&lzproxied=solo" .
			//"&language=".$om_laguage_id;
            "&language=18";
		}
	}
	?>
	<iframe src="<?php echo $swfurl; ?>" width="100%" height="100%">
		<p align="center">
			<strong>This content requires the Adobe Flash Player: <a
				href="http://www.macromedia.com/go/getflash/">Get Flash</a>
			</strong>!
		</p>
	</iframe>