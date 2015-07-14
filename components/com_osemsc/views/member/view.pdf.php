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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class osemscViewMember extends osemscView {
	function display($tpl = null) {
		$tpl = null;
		$this->set('_layout', 'default');
		$user = JFactory::getUser();
		$app = JFactory::getApplication('SITE');
		if ($user->guest) {
			$session = JFactory::getSession();
			$session->set('oseReturnUrl', base64_encode('index.php?option=com_osemsc&view=member'));
			$app->redirect('index.php?option=com_osemsc&view=login');
		} else {
			$member = oseRegistry::call('member');
			$view = $member->getInstance('PanelView');
			$member->instance($user->id);
			$hasMember = $member->hasOwnMsc();
			if ($hasMember > 0) {
				$result = $member->getMemberPanelView('Member');
				if ($result['tpl'] != 'master') {
					$tpl = $result['tpl'];
				}
			} else {
				$app->redirect('index.php?option=com_osemsc&view=member', JText::_('You do not have access'));
			}
		}
		$task = JRequest::getCmd('memberTask', null);
		if (empty($task)) {
			oseExit();
		}
		if (!method_exists($this, $task)) {
			oseExit();
		}
		call_user_method($task, $this);
	}
	function generateOrderView() {
		$order_id = JRequest::getInt('order_id', 0);
		$my = JFactory::getUser();
		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$where[] = " `user_id` = {$my->id}";
		$orderInfo = oseRegistry::call('payment')->getOrder($where, 'obj');
		if (empty($orderInfo)) {
			$result = array();
			$result['title'] = 'Error';
			$result['content'] = 'Error';
			oseExit('Error');
		}
		$receipt = oseRegistry::call('member')->getReceipt($orderInfo);
		$document = &JFactory::getDocument();
		$document->setTitle($receipt->subject);
		$document->setName('Invoice-#' . $order_id);
		$document->setDescription('Invoice-#' . $order_id);
		$document->setMetaData('keywords', 'Invoice');
		// prepare header lines
		$document->setHeader('Invoice Create Date:' . $orderInfo->create_date);
		echo $receipt->body;
	}
}
?>