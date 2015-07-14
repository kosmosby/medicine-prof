<?php
/**
 * CBSubs paidsubs plugin installer script
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @Copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 * @version $Id: script.cbpaidsubsbot.php 1601 2012-12-28 23:01:30Z beat $
 **/
if ( ! ( defined( '_VALID_MOS' ) or defined( '_JEXEC' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Installer class for Joomla 1.6+
 */
class plgsystemcbpaidsubsbotInstallerScript
{
	/**
	 * method to preflight the update of this plugin
	 *
	 * @param	string                   $type    'update' or 'install'
	 * @param	JInstallerAdapterPlugin  $parent  The class calling this method
	 * @return void
	 */
	public function preflight( /** @noinspection PhpUnusedParameterInspection */ $type, $parent )
	{
		$element	=	'cbpaidsubsbot';
		$installer	=	$parent->getParent();
		$adminPath	=	$installer->getPath( 'source' );

		if ( JFile::exists( $adminPath . '/' . $element . '.j16.xml' ) ) {
			if ( JFile::exists( $adminPath . '/' . $element . '.xml' ) ) {
				JFile::delete( $adminPath . '/' . $element . '.xml' );
			}

			JFile::move( $adminPath . '/' . $element . '.j16.xml', $adminPath . '/' . $element . '.xml' );
			$installer->setPath( 'manifest', $adminPath . '/' . $element . '.xml' );
		}
	}
}
