<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\GroupJiveForums\Model;

use CBLib\Registry\Registry;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJiveForums\Table\CategoryTableInterface;

defined('CBLIB') or die();

/**
 * Interface ModelInterface
 *
 * @property-read string      $type
 * @property-read PluginTable $gjPlugin
 * @property-read Registry    $gjParams
 * @property-read PluginTable $plugin
 * @property-read Registry    $params
 *
 * @package CB\Plugin\GroupJiveForums\Model
 */
interface ModelInterface
{

	public function __construct();

	/**
	 * @return array
	 */
	public function getCategories();

	/**
	 * @param int $id
	 * @return CategoryTableInterface
	 */
	public function getCategory( $id );

	/**
	 * @param UserTable  $user
	 * @param GroupTable $group
	 * @param array      $counters
	 * @return array|null
	 */
	public function getTopics( $user, &$group, &$counters );
}