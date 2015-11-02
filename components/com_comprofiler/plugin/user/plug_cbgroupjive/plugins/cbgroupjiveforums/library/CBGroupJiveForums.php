<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\GroupJiveForums;

use CB\Plugin\GroupJiveForums\Model\ModelInterface;

defined('CBLIB') or die();

class CBGroupJiveForums
{

	/**
	 * Returns the forum model instance
	 *
	 * @return null|ModelInterface
	 */
	static public function getModel()
	{
		global $_CB_framework, $_PLUGINS;

		static $model			=	null;

		if ( ! $model ) {
			static $params		=	null;

			if ( ! $params ) {
				$plugin			=	$_PLUGINS->getLoadedPlugin( 'user/plug_cbgroupjive/plugins', 'cbgroupjiveforums' );
				$params			=	$_PLUGINS->getPluginParams( $plugin );
			}

			switch( $params->get( 'groups_forums_model', 'kunena' ) ) {
				case 'kunena':
					$api		=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_kunena/api.php';

					if ( file_exists( $api ) ) {
						require_once( $api );

						if ( class_exists( 'KunenaForum' ) ) {
							\KunenaForum::setup();
						}

						$model	=	new Model\Kunena\Model();
					}
					break;
			}
		}

		return $model;
	}

	/**
	 * Returns select options list of forum categories
	 *
	 * @return array
	 */
	static public function getCategoryOptions()
	{
		$model	=	self::getModel();

		if ( ! $model ) {
			return array();
		}

		return $model->getCategories();
	}
}