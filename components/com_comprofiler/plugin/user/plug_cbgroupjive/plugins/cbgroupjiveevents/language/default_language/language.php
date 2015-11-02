<?php
/**
* Community Builder (TM) cbgroupjiveevents Default (English) language file Frontend
* @version $Id:$
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/**
* WARNING:
* Do not make changes to this file as it will be over-written when you upgrade CB.
* To localize you need to create your own CB language plugin and make changes there.
*/

defined('CBLIB') or die();

return	array(
// 8 language strings from file cbgroupjiveevents/cbgroupjiveevents.xml
'ENABLE_OR_DISABLE_USAGE_OF_CONTENT_PLUGINS_CONTENT_9c9e72'	=>	'Enable or disable usage of content plugins content.prepare on group events.',
'CONTENT_PLUGINS_897da7'	=>	'Content Plugins',
'INPUT_NUMBER_OF_EVENTS_EACH_INDIVIDUAL_USER_IS_LIM_dde6e0'	=>	'Input number of events each individual user is limited to creating per group. If blank allow unlimited events. Moderators and group owners are exempt from this configuration.',
'ENABLE_OR_DISABLE_USAGE_OF_CAPTCHA_ON_GROUP_EVENTS_4ca8d1'	=>	'Enable or disable usage of captcha on group events. Requires latest CB AntiSpam to be installed and published. Moderators are exempt from this configuration.',
'SCHEDULE_OF_NEW_EVENT_6729bc'	=>	'Schedule of new event',
'NEW_EVENT_REQUIRES_APPROVAL_bed887'	=>	'New event requires approval',
'USER_ATTENDS_MY_EXISTING_EVENTS_6325f2'	=>	'User attends my existing events',
'USER_UNATTENDS_MY_EXISTING_EVENTS_c91a5c'	=>	'User unattends my existing events',
// 4 language strings from file cbgroupjiveevents/cbgroupjiveevents.php
'ENABLE_WITH_APPROVAL_575b45'	=>	'Enable, with Approval',
'OPTIONALLY_ENABLE_OR_DISABLE_USAGE_OF_EVENTS_GROUP_538b5f'	=>	'Optionally enable or disable usage of events. Group owner and group administrators are exempt from this configuration and can always schedule events. Note existing events will still be accessible.',
'SEARCH_EVENTS_16de57'	=>	'Search Events...',
'EVENTS_87f9f7'	=>	'Events',
// 45 language strings from file cbgroupjiveevents/component.cbgroupjiveevents.php
'YOU_DO_NOT_HAVE_ACCESS_TO_THIS_EVENT_b450eb'	=>	'You do not have access to this event.',
'EVENT_DOES_NOT_EXIST_2499af'	=>	'Event does not exist.',
'SEARCH_ATTENDING_a45168'	=>	'Search Attending...',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_SCHEDULE_377dda'	=>	'You do not have sufficient permissions to schedule an event in this group.',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_EDIT_THI_7a77ac'	=>	'You do not have sufficient permissions to edit this event.',
'SELECT_PUBLISH_STATE_OF_THIS_EVENT_UNPUBLISHED_EVE_eee522'	=>	'Select publish state of this event. Unpublished events will not be visible to the public.',
'INPUT_THE_EVENT_TITLE_THIS_IS_THE_TITLE_THAT_WILL__58d733'	=>	'Input the event title. This is the title that will distinguish this event from others. Suggested to input something to uniquely identify your event.',
'INPUT_A_DETAILED_DESCRIPTION_ABOUT_THIS_EVENT_352b85'	=>	'Input a detailed description about this event.',
'INPUT_THE_LOCATION_FOR_THIS_EVENT_EG_MY_HOUSE_THE__dd5da8'	=>	'Input the location for this event (e.g. My House, The Park, Restaurant Name, etc..).',
'OPTIONALLY_INPUT_THE_ADDRESS_FOR_THIS_EVENT_OR_CLI_753f71'	=>	'Optionally input the address for this event or click the map button to attempt to find your current location.',
'SELECT_THE_DATE_AND_TIME_THIS_EVENT_STARTS_ea1ef7'	=>	'Select the date and time this event starts.',
'OPTIONALLY_SELECT_THE_END_DATE_AND_TIME_FOR_THIS_E_b327a7'	=>	'Optionally select the end date and time for this event.',
'OPTIONALLY_INPUT_A_GUEST_LIMIT_FOR_THIS_EVENT_cd7cde'	=>	'Optionally input a guest limit for this event.',
'INPUT_THE_EVENT_OWNER_ID_EVENT_OWNER_DETERMINES_TH_094492'	=>	'Input the event owner id. Event owner determines the creator of the event specified as User ID.',
'GROUP_EVENT_FAILED_TO_SAVE'	=>	'Event failed to save! Error: [error]',
'NEW_GROUP_EVENT_7a6b0d'	=>	'New group event',
'USER_HAS_SCHEDULED_THE_EVENT_EVENT_IN_THE_GROUP_GR_517c40'	=>	'[user] has scheduled the event [event] in the group [group]!',
'NEW_GROUP_EVENT_AWAITING_APPROVAL_39e90d'	=>	'New group event awaiting approval',
'USER_HAS_SCHEDULED_THE_EVENT_EVENT_IN_THE_GROUP_GR_217963'	=>	'[user] has scheduled the event [event] in the group [group] and is awaiting approval!',
'EVENT_SCHEDULED_SUCCESSFULLY_0f6b9c'	=>	'Event scheduled successfully!',
'EVENT_SAVED_SUCCESSFULLY_e074e6'	=>	'Event saved successfully!',
'YOUR_EVENT_IS_AWAITING_APPROVAL_0bbb1c'	=>	'Your event is awaiting approval.',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_PUBLISH__d26385'	=>	'You do not have sufficient permissions to publish or unpublish this event.',
'GROUP_EVENT_STATE_FAILED_TO_SAVE'	=>	'Event state failed to saved. Error: [error]',
'EVENT_SCHEDULE_REQUEST_ACCEPTED_ac27df'	=>	'Event schedule request accepted',
'YOUR_EVENT_EVENT_SCHEDULE_REQUEST_IN_THE_GROUP_GRO_fc4c05'	=>	'Your event [event] schedule request in the group [group] has been accepted!',
'EVENT_STATE_SAVED_SUCCESSFULLY_123c23'	=>	'Event state saved successfully!',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_DELETE_T_4b06a1'	=>	'You do not have sufficient permissions to delete this event.',
'GROUP_EVENT_FAILED_TO_DELETE'	=>	'Event failed to delete. Error: [error]',
'EVENT_DELETED_SUCCESSFULLY_be1e9a'	=>	'Event deleted successfully!',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_ATTEND_T_dc27d7'	=>	'You do not have sufficient permissions to attend this event.',
'YOU_CAN_NOT_ATTEND_AN_EXPIRED_EVENT_d41f63'	=>	'You can not attend an expired event.',
'THIS_EVENT_IS_FULL_c0d66e'	=>	'This event is full.',
'YOU_ARE_ALREADY_ATTENDING_THIS_EVENT_1bdf2f'	=>	'You are already attending this event.',
'GROUP_EVENT_ATTEND_FAILED'	=>	'Event attend failed. Error: [error]',
'USER_ATTENDING_YOUR_GROUP_EVENT_76f83f'	=>	'User attending your group event',
'USER_WILL_BE_ATTENDING_YOUR_EVENT_EVENT_IN_THE_GRO_f05a66'	=>	'[user] will be attending your event [event] in the group [group]!',
'EVENT_ATTENDED_SUCCESSFULLY_86dd6d'	=>	'Event attended successfully!',
'YOU_DO_NOT_HAVE_SUFFICIENT_PERMISSIONS_TO_UNATTEND_653cb2'	=>	'You do not have sufficient permissions to unattend this event.',
'YOU_CAN_NOT_UNATTEND_AN_EXPIRED_EVENT_0020a4'	=>	'You can not unattend an expired event.',
'YOU_CAN_NOT_UNATTEND_AN_EVENT_YOU_ARE_NOT_ATTENDIN_0d0bd9'	=>	'You can not unattend an event you are not attending.',
'GROUP_EVENT_FAILED_TO_UNATTEND'	=>	'Event failed to unattend. Error: [error]',
'USER_UNATTENDED_YOUR_GROUP_EVENT_6aa20f'	=>	'User unattended your group event',
'USER_WILL_NO_LONGER_BE_ATTENDING_YOUR_EVENT_EVENT__818798'	=>	'[user] will no longer be attending your event [event] in the group [group]!',
'EVENT_UNATTENDED_SUCCESSFULLY_d29b36'	=>	'Event unattended successfully!',
// 3 language strings from file cbgroupjiveevents/library/Table/AttendanceTable.php
'OWNER_NOT_SPECIFIED_4e1454'	=>	'Owner not specified!',
'EVENT_NOT_SPECIFIED_3f2fec'	=>	'Event not specified!',
'EVENT_DOES_NOT_EXIST_bb3b8e'	=>	'Event does not exist!',
// 6 language strings from file cbgroupjiveevents/library/Table/EventTable.php
'GROUP_NOT_SPECIFIED_70267b'	=>	'Group not specified!',
'START_DATE_NOT_SPECIFIED_1127f6'	=>	'Start date not specified!',
'GROUP_DOES_NOT_EXIST_adf2fd'	=>	'Group does not exist!',
'END_DATE_CAN_NOT_BE_BEFORE_THE_START_DATE_fab0bf'	=>	'End date can not be before the start date!',
'GROUP_EVENT_DATE_FORMAT'	=>	'l, F j Y',
'GROUP_EVENT_TIME_FORMAT'	=>	' g:i A',
// 3 language strings from file cbgroupjiveevents/templates/default/attending.php
'NO_EVENT_GUEST_SEARCH_RESULTS_FOUND_ca6af0'	=>	'No event guest search results found.',
'THIS_EVENT_CURRENTLY_HAS_NO_GUESTS_207e61'	=>	'This event currently has no guests.',
'BACK_0557fa'	=>	'Back',
// 9 language strings from file cbgroupjiveevents/templates/default/event_edit.php
'EDIT_EVENT_6a11c1'	=>	'Edit Event',
'NEW_EVENT_842b2b'	=>	'New Event',
'EVENT_a4ecfc'	=>	'Event',
'ADDRESS_dd7bf2'	=>	'Address',
'START_DATE_db3794'	=>	'Start Date',
'END_DATE_3c1429'	=>	'End Date',
'GUEST_LIMIT_b0a150'	=>	'Guest Limit',
'UPDATE_EVENT_f126f4'	=>	'Update Event',
'SCHEDULE_EVENT_98fbce'	=>	'Schedule Event',
// 15 language strings from file cbgroupjiveevents/templates/default/events.php
'GROUP_EVENTS_COUNT'	=>	'%%COUNT%% Event|%%COUNT%% Events',
'ATTEND_9961c9'	=>	'Attend',
'ARE_YOU_SURE_YOU_DO_NOT_WANT_TO_ATTEND_THIS_EVENT_c67ed0'	=>	'Are you sure you do not want to attend this Event?',
'UNATTEND_3534eb'	=>	'Unattend',
'ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THIS_EVENT_77b8b6'	=>	'Are you sure you want to unpublish this Event?',
'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_EVENT_86f16e'	=>	'Are you sure you want to delete this Event?',
'THIS_EVENT_HAS_ENDED_48f27e'	=>	'This event has ended.',
'GROUP_EVENT_ENDS_IN'	=>	'This event is currently in progress and ends in [timeago].',
'THIS_EVENT_IS_CURRENTLY_IN_PROGRESS_558969'	=>	'This event is currently in progress.',
'GROUP_EVENT_STARTS_IN'	=>	'This event starts in [timeago].',
'GROUP_GUESTS_COUNT_LIMITED'	=>	'%%COUNT%% of [limit] Guest|%%COUNT%% of [limit] Guests',
'GROUP_GUESTS_COUNT'	=>	'%%COUNT%% Guest|%%COUNT%% Guests',
'SEE_MORE_1f7e18'	=>	'See More',
'NO_GROUP_EVENT_SEARCH_RESULTS_FOUND_aa3f23'	=>	'No group event search results found.',
'THIS_GROUP_CURRENTLY_HAS_NO_EVENTS_4bf235'	=>	'This group currently has no events.',
);
