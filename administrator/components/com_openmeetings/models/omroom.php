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
/**
 * OpenMeetings Administrator OMRoom model
 *
 * @package com_openmeetings
 * @subpackage components
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
// Import the JModel class
jimport('joomla.application.component.modeladmin');
jimport( 'joomla.application.component.modelform' );
jimport('joomla.event.dispatcher');


require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'om_gateway'.DS.'openmeetingsRoomManagament.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'om_gateway'.DS.'openmeetings_gateway.php');

class OpenMeetingsModelOMRoom extends JModelForm {
	public $xml_form_name;

	public function getForm($data = array(), $loadData = true) {

		$form_name = 'omroom';

		$this->xml_form_name = $form_name;


		// Get the form.
		$form = $this->loadForm('com_openmeetings.'.$form_name, $form_name, array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		$form_name = $this->xml_form_name;

		$data = JFactory::getApplication()->getUserState('com_openmeetings.edit.'.$form_name.'.data', array());
		if (empty($data))
			$data = $this->getData();

		return $data;

	}

	public function getData() {
		$form_name = $this->xml_form_name;

		$id = JRequest::getVar('cid', 0);

		$db = $this->getDBO();

		$table = $db->nameQuote( '#__om_rooms' );
		$key = $db->nameQuote( 'id' );
		$query = " SELECT * FROM " . $table
		. " WHERE " . $key . " = " . $db->quote( $id[0]);
		$db->setQuery($query);
		$params = $db->loadAssoc();
		$params['is_public'] = (( $params['is_public'] ) ? 'True' : 'False') ;

		if ( $id[0] ) {
			$query = " SELECT user_id FROM #__om_rooms_users WHERE om_room_id = " . $db->quote( $params['room_id']);
			$db->setQuery($query);
			$params['owners'] = $db->loadResultArray();
		}

		foreach ($params as $key => $param) {
			$tmp = json_decode($param);
			if (!is_array($tmp)) {
				$this->data[$key] = $param;
			} else {
				$this->data[$key] = $tmp;
			}
			$tmp = array();
		}

		$dispatcher	= JDispatcher::getInstance();
		$results = $dispatcher->trigger('onContentPrepareData', array('com_openmeetings.'.$form_name, $this->data));


		// Return the row data
		return $this->data;
	}
	 
	function store() {
		$db = $this->getDBO();
		$table = $db->nameQuote('#__om_rooms');

		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar('id', 0, 'post');

		$requestData = JRequest::getVar('jform', array(), 'post');
		if (!is_numeric($requestData["number_of_participants"])) {
			$requestData["number_of_participants"] = 0;
		}

		$data = $requestData;
		if ( $requestData["is_public"] == 'True' ) {
			$requestData["is_public"] = 1;
		} else {
			$requestData["is_public"] = 0;
		}


		if ($cid) {
			/* Обновляем данные */
			$sql = 'UPDATE '. $table .' SET '.
					$db->nameQuote('name') .' = '. $db->quote($requestData['name']) .', '.
					$db->nameQuote('roomtype_id') .' = '. $db->quote($requestData['roomtype_id']) .', '.
					$db->nameQuote('comment') .' = '. $db->quote($requestData['comment']) .', '.
					$db->nameQuote('number_of_participants') .' = '. $db->quote($requestData['number_of_participants']) .', '.
					$db->nameQuote('is_public') .' = '. $db->quote($requestData['is_public']) .', '.
					$db->nameQuote('appointment') .' = '. $db->quote($requestData['appointment']) .', '.
					$db->nameQuote('is_moderated_room') .' = '. $db->quote($requestData['is_moderated_room']) .', '.
					$db->nameQuote('room_validity') .' = '. $db->quote($requestData['room_validity']) .', '.
					$db->nameQuote('date_type') .' = '. $db->quote($requestData['date_type']) .', '.
					$db->nameQuote('time_type') .' = '. $db->quote($requestData['time_type']) .', '.
					$db->nameQuote('duration') .' = '. $db->quote($requestData['duration']) .', '.
					$db->nameQuote('repeat_type') .' = '. $db->quote($requestData['repeat_type']) .', '.
					$db->nameQuote('weekday_type') .' = '. $db->quote($requestData['weekday_type']) .' '.
					'WHERE '. $db->nameQuote('id') .' = '. $db->quote($cid);


			$omRoomManagament = 	new openmeetingsRoomManagament();
			$room_id = $omRoomManagament-> updateRoomWithModeration($data);

			//Make sure the Openmeetings Room was succsefully created
			if($room_id < 1){
				throw new Exception('Could not login User to OpenMeetings, check your OpenMeetings Module Configuration');
				return false;
			}
				
		} else {
			/* Создаем новую запись */
			$omRoomManagament = 	new openmeetingsRoomManagament();
			$room_id = $omRoomManagament-> createRoomWithModeration($data);
			//$data['room_id'] = $room_id;
			$data->room_id = $room_id;

			//Make sure the Openmeetings Room was succsefully created
			if($room_id < 1){
				throw new Exception('Could not login User to OpenMeetings, check your OpenMeetings Module Configuration');
				return false;
			}

			$sql = 'INSERT INTO '. $table .'
					(`id`, `name`, `room_id`, `owner`, `roomtype_id`, `comment`, `number_of_participants`, `is_public`, `appointment`,
					`is_moderated_room`, `room_validity`, `date_type`, `time_type`, `duration`, `repeat_type`, `weekday_type`) VALUES ('.
					'0, '. $db->quote($requestData['name']) .', '.  $db->quote($room_id) .', '. $db->quote($requestData['owner']) .', '.
					$db->quote($requestData['roomtype_id']) .', '.  $db->quote($requestData['comment']) .', '. $db->quote($requestData['number_of_participants']) .', '.
					$db->quote($requestData['is_public']) .', '.  $db->quote($requestData['appointment']) .', '. $db->quote($requestData['is_moderated_room']) .', '.
					$db->quote($requestData['room_validity']) .', '.  $db->quote($requestData['date_type']) .', '. $db->quote($requestData['time_type']) .', '.
					$db->quote($requestData['duration']) .', '.  $db->quote($requestData['repeat_type']) .', '. $db->quote($requestData['weekday_type']) .')';
		}

		$db->setQuery($sql);
		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		$db->setQuery('DELETE FROM #__om_rooms_users WHERE `om_room_id` = '. $db->quote( $room_id ));
		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		foreach ($requestData['owners'] as $value ) {
			$tuples[] = '('. $db->quote( $room_id ).", ".$db->quote( $value ) .')';
		}

		if ( count($tuples) ) {
			$db->setQuery('INSERT INTO #__om_rooms_users VALUES '.implode(', ', $tuples));
			if (!$db->query()) {
				throw new Exception($db->getErrorMsg());
			}
		}

	}
}
?>