<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class cbautoactionsActionEmail extends cbPluginHandler
{

	/**
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable $user
	 */
	public function execute( $trigger, $user )
	{
		foreach ( $trigger->getParams()->subTree( 'email' ) as $row ) {
			/** @var ParamsInterface $row */
			$mailTo										=	$row->get( 'to', null, GetterInterface::STRING );

			if ( ! $mailTo ) {
				$mailTo									=	$user->get( 'email' );
			} else {
				$mailTo									=	$trigger->getSubstituteString( $mailTo );
			}

			if ( ! $mailTo ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_EMAIL_NO_TO', ':: Action [action] :: Email skipped due to missing to', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
				}

				continue;
			}

			$mailSubject								=	$trigger->getSubstituteString( $row->get( 'subject', null, GetterInterface::STRING ) );

			if ( ! $mailSubject ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_EMAIL_NO_SBJ', ':: Action [action] :: Email skipped due to missing subject', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
				}

				continue;
			}

			$mailBody									=	$trigger->getSubstituteString( $row->get( 'body', null, GetterInterface::RAW ), false );

			if ( ! $mailBody ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_EMAIL_NO_BODY', ':: Action [action] :: Email skipped due to missing body', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
				}

				continue;
			}

			$mailHtml									=	(int) $row->get( 'mode', null, GetterInterface::INT );
			$mailCC										=	$trigger->getSubstituteString( $row->get( 'cc', null, GetterInterface::STRING ) );
			$mailBCC									=	$trigger->getSubstituteString( $row->get( 'bcc', null, GetterInterface::STRING ) );
			$mailAttachments							=	$trigger->getSubstituteString( $row->get( 'attachment', null, GetterInterface::STRING ) );
			$mailReplyToEmail							=	$trigger->getSubstituteString( $row->get( 'replyto_address', null, GetterInterface::STRING ) );
			$mailReplyToName							=	$trigger->getSubstituteString( $row->get( 'replyto_name', null, GetterInterface::STRING ) );
			$mailFromEmail								=	$trigger->getSubstituteString( $row->get( 'from_address', null, GetterInterface::STRING ) );
			$mailFromName								=	$trigger->getSubstituteString( $row->get( 'from_name', null, GetterInterface::STRING ) );
			$mailMailer									=	$row->get( 'mailer', null, GetterInterface::STRING );
			$mailProperties								=	array();

			if ( $mailTo ) {
				$mailTo									=	preg_split( ' *, *', $mailTo );
			} else {
				$mailTo									=	null;
			}

			if ( $mailCC ) {
				$mailCC									=	preg_split( ' *, *', $mailCC );
			} else {
				$mailCC									=	null;
			}

			if ( $mailBCC ) {
				$mailBCC								=	preg_split( ' *, *', $mailBCC );
			} else {
				$mailBCC								=	null;
			}

			if ( $mailAttachments ) {
				$mailAttachments						=	preg_split( ' *, *', $mailAttachments );
			} else {
				$mailAttachments						=	null;
			}

			if ( $mailReplyToEmail ) {
				$mailReplyToEmail						=	preg_split( ' *, *', $mailReplyToEmail );
			} else {
				$mailReplyToEmail						=	null;
			}

			if ( $mailReplyToName ) {
				$mailReplyToName						=	preg_split( ' *, *', $mailReplyToName );
			} else {
				$mailReplyToName						=	null;
			}

			if ( $mailMailer ) {
				$mailProperties['Mailer']				=	$mailMailer;

				if ( $mailMailer == 'smtp' ) {
					$mailProperties['SMTPAuth']			=	(int) $row->get( 'mailer_smtpauth', null, GetterInterface::INT );
					$mailProperties['Username']			=	$row->get( 'mailer_smtpuser', null, GetterInterface::STRING );
					$mailProperties['Password']			=	$row->get( 'mailer_smtppass', null, GetterInterface::STRING );
					$mailProperties['Host']				=	$row->get( 'mailer_smtphost', null, GetterInterface::STRING );

					$smtpPort							=	(int) $row->get( 'mailer_smtpport', null, GetterInterface::INT );

					if ( $smtpPort ) {
						$mailProperties['Port']			=	$smtpPort;
					}

					$smtpSecure							=	$row->get( 'mailer_smtpsecure', null, GetterInterface::STRING );

					if ( ( $smtpSecure === 'ssl' ) || ( $smtpSecure === 'tls' ) ) {
						$mailProperties['SMTPSecure']	=	$smtpSecure;
					}
				} elseif ( $mailMailer == 'sendmail' ) {
					$sendMail							=	$row->get( 'mailer_sendmail', null, GetterInterface::STRING );

					if ( $sendMail ) {
						$mailProperties['Sendmail']		=	$sendMail;
					}
				}
			}

			$error										=	null;

			if ( ! comprofilerMail( $mailFromEmail, $mailFromName, $mailTo, $mailSubject, $mailBody, $mailHtml, $mailCC, $mailBCC, $mailAttachments, $mailReplyToEmail, $mailReplyToName, $mailProperties, $error ) ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_EMAIL_FAILED', ':: Action [action] :: Email failed to send. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $error ) ) );
				}
			}
		}
	}
}