<?php
/**
* Community Builder (TM) cbautoactions Default (English) language file Frontend
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
// 19 language strings from file plug_cbautoactions/cbautoactions.php
'NOT_EQUAL_TO_9ac8c0'	=>	'Not Equal To',
'GREATER_THAN_728845'	=>	'Greater Than',
'LESS_THAN_45ed1c'	=>	'Less Than',
'GREATER_THAN_OR_EQUAL_TO_93301c'	=>	'Greater Than or Equal To',
'LESS_THAN_OR_EQUAL_TO_3181f2'	=>	'Less Than or Equal To',
'EMPTY_ce2c8a'	=>	'Empty',
'NOT_EMPTY_f49248'	=>	'Not Empty',
'DOES_CONTAIN_396955'	=>	'Does Contain',
'DOES_NOT_CONTAIN_ef739c'	=>	'Does Not Contain',
'IS_REGEX_44846d'	=>	'Is REGEX',
'IS_NOT_REGEX_4bddfd'	=>	'Is Not REGEX',
'EQUAL_TO_c1d440'	=>	'Equal To',
'UNKNOWN_88183b'	=>	'Unknown',
'SYSTEM_ACTIONS_CAN_NOT_BE_DELETED_d886c0'	=>	'System actions can not be deleted',
'CLICK_TO_EXECUTE_ddbd31'	=>	'Click to Execute',
'AUTO_ACTION_ACCESS_FAILED'	=>	':: Action [action] :: Access check for [user_id] failed: looking for [access] in [groups]',
'AUTO_ACTION_CONDITIONAL_FAILED'	=>	':: Action [action] :: Conditional [cond] failed for [user_id]: [field] [operator] [value]',
'AUTO_ACTION_USER_EXCLUDED'	=>	':: Action [action] :: User [user_id] excluded',
'CLICK_TO_PROCESS_0753cd'	=>	'Click to Process',
// 6 language strings from file plug_cbautoactions/cbautoactions.xml
'YOUR_SITE_UNIQUE_URL_THAT_CAN_BE_USED_IN_HTTP_REQU_fbe5ff'	=>	'Your site unique URL that can be used in HTTP requests, accessed directly, or in CRON to execute Internal General actions.',
'INTERNAL_GENERAL_URL_bdd935'	=>	'Internal General URL',
'YOUR_SITE_UNIQUE_URL_THAT_CAN_BE_USED_IN_HTTP_REQU_ad80b1'	=>	'Your site unique URL that can be used in HTTP requests, accessed directly, or in CRON to execute Internal Users actions on every CB user. To execute on a specific user or set of users include the users parameter as a comma separated list of user ids in the URL (e.g. users=62,38,43,29).',
'INTERNAL_USERS_URL_f0e72f'	=>	'Internal Users URL',
'INPUT_COMMA_SEPERATED_LIST_OF_USER_IDS_TO_BE_EXCLU_5b20a6'	=>	'Input comma seperated list of user ids to be excluded from all auto actions (e.g. 62,39,21,8).',
'EXCLUDE_843f28'	=>	'Exclude',
// 4 language strings from file plug_cbautoactions/models/activity.php
'AUTO_ACTION_ACTIVITY_NOT_INSTALLED'	=>	':: Action [action] :: CB Activity is not installed',
'AUTO_ACTION_ACTIVITY_NO_OWNER'	=>	':: Action [action] :: CB Activity skipped due to missing owner',
'AUTO_ACTION_ACTIVITY_NO_TYPE'	=>	':: Action [action] :: CB Activity skipped due to missing type',
'AUTO_ACTION_ACTIVITY_CREATE_FAILED'	=>	':: Action [action] :: CB Activity failed to save. Error: [error]',
// 2 language strings from file plug_cbautoactions/models/acymailing.php
'AUTO_ACTION_ACYMAILING_NOT_INSTALLED'	=>	':: Action [action] :: AcyMailing is not installed',
'AUTO_ACTION_ACYMAILING_NO_SUB'	=>	':: Action [action] :: AcyMailing skipped due to missing subscriber id',
// 3 language strings from file plug_cbautoactions/models/antispam.php
'AUTO_ACTION_ANTISPAM_NOT_INSTALLED'	=>	':: Action [action] :: CB AntiSpam is not installed',
'AUTO_ACTION_ANTISPAM_NO_VALUE'	=>	':: Action [action] :: CB AntiSpam skipped due to missing value',
'AUTO_ACTION_ANTISPAM_FAILED'	=>	':: Action [action] :: CB AntiSpam failed to save. Error: [error]',
// 4 language strings from file plug_cbautoactions/models/blog.php
'AUTO_ACTION_BLOGS_NOT_INSTALLED'	=>	':: Action [action] :: CB Blogs is not installed',
'AUTO_ACTION_BLOGS_NO_OWNER'	=>	':: Action [action] :: CB Blogs skipped due to missing owner',
'AUTO_ACTION_BLOGS_BIND_FAILED'	=>	':: Action [action] :: CB Blogs failed to bind. Error: [error]',
'AUTO_ACTION_BLOGS_FAILED'	=>	':: Action [action] :: CB Blogs failed to save. Error: [error]',
// 1 language strings from file plug_cbautoactions/models/cbsubs.php
'AUTO_ACTION_CBSUBS_NOT_INSTALLED'	=>	':: Action [action] :: CB Paid Subscriptions is not installed',
// 1 language strings from file plug_cbautoactions/models/connection.php
'AUTO_ACTION_CONNECTION_NO_USER'	=>	':: Action [action] :: Connection skipped due to no user',
// 2 language strings from file plug_cbautoactions/models/content.php
'AUTO_ACTION_CONTENT_NO_OWNER'	=>	':: Action [action] :: Content skipped due to missing owner',
'AUTO_ACTION_CONTENT_FAILED'	=>	':: Action [action] :: Content failed to save',
// 4 language strings from file plug_cbautoactions/models/email.php
'AUTO_ACTION_EMAIL_NO_TO'	=>	':: Action [action] :: Email skipped due to missing to',
'AUTO_ACTION_EMAIL_NO_SBJ'	=>	':: Action [action] :: Email skipped due to missing subject',
'AUTO_ACTION_EMAIL_NO_BODY'	=>	':: Action [action] :: Email skipped due to missing body',
'AUTO_ACTION_EMAIL_FAILED'	=>	':: Action [action] :: Email failed to send. Error: [error]',
// 3 language strings from file plug_cbautoactions/models/field.php
'AUTO_ACTION_FIELD_NO_USER'	=>	':: Action [action] :: Field skipped due to no user',
'AUTO_ACTION_FIELD_NO_FIELD'	=>	':: Action [action] :: Field skipped due to missing field',
'AUTO_ACTION_FIELD_DOES_NOT_EXIST'	=>	':: Action [action] :: Field skipped due to field [field_id] does not exist',
// 4 language strings from file plug_cbautoactions/models/gallery.php
'AUTO_ACTION_GALLERY_NOT_INSTALLED'	=>	':: Action [action] :: CB Gallery is not installed',
'AUTO_ACTION_GALLERY_NO_OWNER'	=>	':: Action [action] :: CB Gallery skipped due to missing owner',
'AUTO_ACTION_GALLERY_NO_VALUE'	=>	':: Action [action] :: CB Gallery skipped due to missing value',
'AUTO_ACTION_GALLERY_FAILED'	=>	':: Action [action] :: CB Gallery failed to save. Error: [error]',
// 6 language strings from file plug_cbautoactions/models/groupjive.php
'AUTO_ACTION_GROUPJIVE_NOT_INSTALLED'	=>	':: Action [action] :: CB GroupJive is not installed',
'AUTO_ACTION_GROUPJIVE_NO_OWNER'	=>	':: Action [action] :: CB GroupJive skipped due to missing owner',
'AUTO_ACTION_GROUPJIVE_NO_NAME'	=>	':: Action [action] :: CB GroupJive skipped due to missing name',
'AUTO_ACTION_GROUPJIVE_NO_CATEGORY'	=>	':: Action [action] :: CB GroupJive skipped due to missing category',
'AUTO_ACTION_GROUPJIVE_FAILED'	=>	':: Action [action] :: CB GroupJive failed to save. Error: [error]',
'AUTO_ACTION_GROUPJIVE_NO_CAT_NAME'	=>	':: Action [action] :: CB GroupJive skipped due to missing category name',
// 4 language strings from file plug_cbautoactions/models/invite.php
'AUTO_ACTION_INVITE_NOT_INSTALLED'	=>	':: Action [action] :: CB Invites is not installed',
'AUTO_ACTION_INVITE_NO_OWNER'	=>	':: Action [action] :: CB Invites skipped due to missing owner',
'AUTO_ACTION_INVITE_FAILED'	=>	':: Action [action] :: CB Invites failed to save. Error: [error]',
'AUTO_ACTION_INVITE_SEND_FAILED'	=>	':: Action [action] :: CB Invites failed to send. Error: [error]',
// 2 language strings from file plug_cbautoactions/models/k2.php
'AUTO_ACTION_K2_NO_USER'	=>	':: Action [action] :: K2 skipped due to no user',
'AUTO_ACTION_K2_NOT_INSTALLED'	=>	':: Action [action] :: K2 is not installed',
// 8 language strings from file plug_cbautoactions/models/kunena.php
'AUTO_ACTION_KUNENA_NOT_INSTALLED'	=>	':: Action [action] :: Kunena is not installed',
'AUTO_ACTION_KUNENA_NO_USER'	=>	':: Action [action] :: Kunena skipped due to no user',
'AUTO_ACTION_KUNENA_NO_OWNER'	=>	':: Action [action] :: Kunena skipped due to missing owner',
'AUTO_ACTION_KUNENA_NO_MSG'	=>	':: Action [action] :: Kunena skipped due to missing message',
'AUTO_ACTION_KUNENA_NO_TOPIC'	=>	':: Action [action] :: Kunena skipped due to missing topic',
'AUTO_ACTION_KUNENA_NO_SUBJ'	=>	':: Action [action] :: Kunena skipped due to missing subject',
'AUTO_ACTION_KUNENA_NO_CAT'	=>	':: Action [action] :: Kunena skipped due to missing category',
'AUTO_ACTION_KUNENA_NO_NAME'	=>	':: Action [action] :: Kunena skipped due to missing name',
// 1 language strings from file plug_cbautoactions/models/menu.php
'AUTO_ACTION_MENU_NO_TITLE'	=>	':: Action [action] :: CB Menu skipped due to missing title',
// 3 language strings from file plug_cbautoactions/models/pms.php
'AUTO_ACTION_PMS_NO_FROM'	=>	':: Action [action] :: Private Message skipped due to missing from',
'AUTO_ACTION_PMS_NO_TO'	=>	':: Action [action] :: Private Message skipped due to missing to',
'AUTO_ACTION_PMS_NO_MSG'	=>	':: Action [action] :: Private Message skipped due to missing message',
// 5 language strings from file plug_cbautoactions/models/privacy.php
'AUTO_ACTION_PRIVACY_NOT_INSTALLED'	=>	':: Action [action] :: CB Privacy is not installed',
'AUTO_ACTION_PRIVACY_NO_OWNER'	=>	':: Action [action] :: CB Privacy skipped due to missing owner',
'AUTO_ACTION_PRIVACY_NO_TYPE'	=>	':: Action [action] :: CB Privacy skipped due to missing type',
'AUTO_ACTION_PRIVACY_NO_RULE'	=>	':: Action [action] :: CB Privacy skipped due to missing rule',
'AUTO_ACTION_PRIVACY_FAILED'	=>	':: Action [action] :: CB Privacy failed to save. Error: [error]',
// 5 language strings from file plug_cbautoactions/models/query.php
'AUTO_ACTION_QUERY_NO_HOST'	=>	':: Action [action] :: Query skipped due to missing host',
'AUTO_ACTION_QUERY_NO_USERNAME'	=>	':: Action [action] :: Query skipped due to missing username',
'AUTO_ACTION_QUERY_NO_PSWD'	=>	':: Action [action] :: Query skipped due to missing password',
'AUTO_ACTION_QUERY_NO_DB'	=>	':: Action [action] :: Query skipped due to missing database',
'AUTO_ACTION_QUERY_EXT_DB_FAILED'	=>	':: Action [action] :: Query external database failed. Error: [error]',
// 1 language strings from file plug_cbautoactions/models/redirect.php
'AUTO_ACTION_REDIRECT_NO_URL'	=>	':: Action [action] :: Redirect skipped due to missing url',
// 1 language strings from file plug_cbautoactions/models/registration.php
'AUTO_ACTION_REGISTRATION_FAILED'	=>	':: Action [action] :: Registration failed to save. Error: [error]',
// 2 language strings from file plug_cbautoactions/models/request.php
'AUTO_ACTION_REQUEST_NO_URL'	=>	':: Action [action] :: Request skipped due to missing url',
'AUTO_ACTION_REQUEST_FAILED'	=>	':: Action [action] :: Request failed. Error: [error]',
// 5 language strings from file plug_cbautoactions/models/usergroup.php
'AUTO_ACTION_USERGROUP_NO_USER'	=>	':: Action [action] :: Usergroup skipped due to no user',
'AUTO_ACTION_USERGROUP_NO_TITLE'	=>	':: Action [action] :: Usergroup skipped due to missing title',
'AUTO_ACTION_USERGROUP_CREATE_FAILED'	=>	':: Action [action] :: Usergroup failed to create',
'AUTO_ACTION_USERGROUP_FAILED'	=>	':: Action [action] :: Usergroup failed to save. Error: [error]',
'AUTO_ACTION_USERGROUP_NO_GROUPS'	=>	':: Action [action] :: Usergroup skipped due to missing groups',
// 3 language strings from file plug_cbautoactions/models/activity.xml
'CREATE_76ea0b'	=>	'create',
'ACTIVITY_69a256'	=>	'activity',
'ECHO_cbb11e'	=>	'echo',
// 1 language strings from file plug_cbautoactions/models/gallery.xml
'PHOTO_5ae0c1'	=>	'photo',
// 2 language strings from file plug_cbautoactions/xml/controllers/frontcontroller.xml
'AUTO_ACTIONS_9705cd'	=>	'Auto Actions',
'SYSTEM_ACTIONS_043942'	=>	'System Actions',
);
