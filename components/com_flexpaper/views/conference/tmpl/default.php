<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//echo "<pre>";
//print_r($this->items); die;

$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
echo $this->loadTemplate('topmenu');
?>

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
require_once(JPATH_ADMINISTRATOR.DS.'components/com_openmeetings/om_gateway/openmeetingsRoomManagament.php');
require_once(JPATH_ADMINISTRATOR.DS.'components/com_openmeetings/om_gateway/openmeetingsRecordingManagament.php');
require_once(JPATH_ADMINISTRATOR.DS.'components/com_openmeetings/om_gateway/openmeetings_gateway.php');

//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$db = &JFactory::getDBO();

$user =& JFactory::getUser();
$userid=$user->id;

$query = 'SELECT * FROM #__om_rooms WHERE `owner` = '. $userid;
$db->setQuery($query);
$my_rooms = $db->loadAssocList();

$query = 'SELECT * FROM #__om_rooms WHERE `room_id` IN ( SELECT room_id FROM #__om_rooms_users WHERE `user_id` = '. $userid . ') ';
$db->setQuery($query);
$rooms = $db->loadAssocList();

$query = 'SELECT * FROM #__om_rooms WHERE `is_public` = 1 ';
$db->setQuery($query);
$public_rooms = $db->loadAssocList();
?>
<ul>
    <?php
    if ( count($my_rooms) || count($rooms) || count($public_rooms) )  {
        foreach ($my_rooms as $item) {
            ?>
            <li><img src='media/com_openmeetings/images/user_people.png'
                     align='absmiddle' border='0'> <a
                    href="<?php echo JRoute::_('index.php?option=com_openmeetings&view=om&format=raw&room=' . $item['room_id'] ); ?>"
                    target="_blank"> <b><?php echo $item['name']; ?> </b>
                </a>
            </li>
        <?php
        }
        foreach ($rooms as $item) {
            ?>
            <li><img src='media/com_openmeetings/images/user_people.png'
                     align='absmiddle' border='0'> <a
                    href="<?php echo JRoute::_('index.php?option=com_openmeetings&view=om&format=raw&room=' . $item['room_id'] ); ?>"
                    target="_blank"> <b><?php echo $item['name']; ?> </b>
                </a>
            </li>
        <?php
        }
        foreach ($public_rooms as $item) {
            ?>
            <li><img src='media/com_openmeetings/images/user_people.png'
                     align='absmiddle' border='0'> <a
                    href="<?php echo JRoute::_('index.php?option=com_openmeetings&view=om&format=raw&room=' . $item['room_id'] ); ?>"
                    target="_blank"> <b><?php echo $item['name']; ?> </b>
                </a>
            </li>
        <?php
        }
    } else {
        ?>
        <li>No public or owned video conference rooms.</li>
    <?php
    }
    ?>

</ul>
<span class='header-3'>Openmeetings Recordings</span>

<?php



//Recordings
$om_recordings=new openmeetingsRecordingManagament();
$om_recordings_return = $om_recordings->getFlvRecordingByExternalUserId($user->id);

if($om_recordings_return != null) {
    if (!is_array($om_recordings_return[0])) {
        $om_recordings_return = array($om_recordings_return);
    }
    ?>
    <ul>
        <?php
        for ($i = 0; $i < count($om_recordings_return); $i++) {
            ?>
            <li><img src='media/com_openmeetings/images/Webcam_16.png'
                     align='absmiddle' border='0'> <a
                    href="<?php echo JRoute::_('index.php?option=com_openmeetings&view=rec_link&format=rec_link&rec=' . $om_recordings_return[$i]['flvRecordingId']); ?>"
                    target="_blank"> <b><?php echo $om_recordings_return[$i]['fileName']; ?>
                    </b>
                </a> <a
                    href="<?php echo JRoute::_('index.php?option=com_openmeetings&view=delrec&format=delrec&rec=' . $om_recordings_return[$i]['flvRecordingId']); ?>"
                    target='_self'> <img
                        src='media/com_openmeetings/images/process-stop.png'
                        align='absmiddle' border='0'>
                </a>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
?>
