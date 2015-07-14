<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
class JFormFieldMembership extends JFormField {
	public $type = 'membership';
	protected function getInput() {
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ose_cpu' . DS . 'define.php');
		require_once(OSECPU_B_PATH . DS . 'oseregistry' . DS . 'oseregistry.php');
		oseRegistry::register('registry', 'oseregistry');
		oseRegistry::call('registry');
		oseRegistry::register('msc', 'membership');
		oseRegistry::call('msc', 'membership');
		$objs = oseMscTree::getSubTreeDepth(0, 0, 'obj');
		$option = array();
		foreach ($objs as $obj) {
			if ($obj->published) {
				@$option[] = JHTML::_('select.option', $obj->id, $obj->title);
			}
		}
		$control_name = 'jform[params]';
		$name = 'allowCreateMSC';
		return JHTML::_('select.genericlist', $option, '' . $control_name . '[' . $name . '][]', ' class="inputbox" style="width:90%;" multiple="multiple" size="15"', 'value', 'text', '');
	}
}
