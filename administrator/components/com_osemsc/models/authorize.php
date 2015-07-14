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
defined('_JEXEC') or die("Direct Access Not Allowed");

jimport('joomla.application.component.modellist');

class oseMscModelAuthorize extends JModelList
{
	protected $items;
	protected $pagination;
	protected $state;
	
    public function __construct($config = array())
    {
    	if (empty($config['filter_fields']))
    	{
    		$config['filter_fields'] = array( 'c.id',
    					'a.status','c.expired_date','d.name'
    		);
    	}
    	
        parent::__construct($config);
    } //function

	function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId('getItems');

		// Try to load the data from internal storage.
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}
		
		$search = $this->getState('filter.search');
		$searchid = $this->getState('filter.searchid');
		
		// Load the list items.
		$items = parent::getItems();

		// If emtpy or an error, just return.
		if (empty($items))
		{
			return array();
		}
		
		
		
		$db = oseDB::instance();
		// Inject the values back into the array.
		foreach ($items as $k => $item)
		{
			$order_id = null;
			
			if($item->msc_id > 0)
			{
				$params = oseJson::decode($item->params);
				$order_id = oseGetValue($params,'order_id',0);
				$query = " SELECT `title` FROM `#__osemsc_acl`"
				." WHERE `id` = '{$item->msc_id}'"
				;
				$db->setQuery($query);
				$item->membership = $db->loadResult();
					
				/*$query = " SELECT `expired_date` FROM `#__osemsc_member`"
				 ." WHERE `id` = '{$item->id}'"
				;
				$db->setQuery($query);
				$item->expired_date = $db->loadResult();
					
				$query = " SELECT *"
				." FROM `#__osemsc_order_fix`"
				." WHERE `member_id` = '{$item->id}'"
				;
				$db->setQuery($query);
				$ofItem = oseDB::loadItem('obj');
				$item->status = oseObject::getValue($ofItem,'status');*/
			}
			else
			{
				$item->expired_date = null;
				$item->status = '';
			}
			
			if(empty($search) && empty($searchid))
			{
				//return array();
			}
			else
			{
				//$item->order_number = null;
				if(!empty($order_id))
				{
					$query = " SELECT `order_id`,`order_number`,`payment_serial_number`"
					." FROM `#__osemsc_order`"
					." WHERE `order_id` = '{$order_id}'"
					;
					$db->setQuery($query);
					$oItem = oseDB::loadItem('obj');
					$item->order_number = $oItem->order_number;
					$item->order_id = $oItem->order_id;
					$item->payment_serial_number = $oItem->payment_serial_number;
				}
				else
				{
				
				}
			}
			
			
			$items[$k] = $item;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		
		$search = $this->getState('filter.search');
		
		$searchid = (int)$this->getState('filter.searchid');
		
		$query = $db->getQuery(true);
		
		if(!empty($search) || !empty($searchid))
		{
			// Select all fields from the table.
			$query->select($this->getState('list.select', 'c.*,d.username,d.name,d.email'));
			$query->from($db->quoteName('#__osemsc_member').' AS c');
			$query->innerJoin('`#__users` AS d ON d.id = c.member_id');
			// Filter on the published state.
			
			$payment_method = $this->getState('filter.payment_method');
			
			if($search)
			{
				// Escape the search token.
				$token	= $db->Quote('%'.$search.'%');
				// Compile the different search clauses.
				$searches	= array();
				$searches[]	= 'd.name LIKE '.$token;
				$searches[]	= 'd.username LIKE '.$token;
				$searches[]	= 'd.email LIKE '.$token;
				
				// Add the clauses to the query.
				$query->where('('.implode(' OR ', $searches).')');
			}
			elseif($searchid)
			{
				// Escape the search token.
				$token	= $db->Quote('%'.$search.'%');

				// Add the clauses to the query.
				$query->where('c.id='.$searchid);
			}
			
			//$query->where("c.`msc_id` NOT IN (9)");
			//$query->group('a.id');
			
			// Add the list ordering clause.
			$query->order($db->escape($this->getState('list.ordering', 'c.expired_date')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
		else
		{
			$query->select($this->getState('list.select', 'c.*,d.username,d.name,d.email,b.order_id,b.order_number,b.payment_serial_number'));
			$query->from('#__osemsc_order_item AS a');
			$query->innerjoin("`#__osemsc_order` AS b ON b.order_id = a.order_id");
			$query->innerjoin("`#__osemsc_member`AS c ON c.member_id = b.user_id AND c.msc_id=a.entry_id");
			$query->innerjoin("`#__users` AS d ON d.id = b.user_id");
			$query->where("b.`payment_method` = 'authorize'");
			//$query->where("c.`msc_id` NOT IN (9)");
			//$query->where("c.`params` LIKE (CONCAT('%\"order_id\":\"',b.`order_id`,'%'))");
			$query->where("b.`order_status` = 'confirmed'");
			// set Order
			
			$query->order($this->getState('list.ordering', 'c.expired_date').' '.$this->getState('list.direction', 'ASC'));
		}
		
		
		return $query;
	}
	
	public function getTotal()
	{
		$search = $this->getState('filter.search');
		$searchid = $this->getState('filter.searchid');
		
		if(empty($search) && empty($searchid))
		{
			//return 0;
		}
	
		return parent::getTotal();
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
	
		// Load the filter state.
		$status = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status');
		$this->setState('filter.status', $status);
		
		$payment_mode = $this->getUserStateFromRequest($this->context.'.filter.payment_mode', 'filter_payment_mode');
		///$payment_mode = ($status == 'failed')?'':$payment_mode;
		$this->setState('filter.payment_mode', $payment_mode);
		
		$payment_method = $this->getUserStateFromRequest($this->context.'.filter.payment_method', 'filter_payment_method');
		//$payment_method = ($status == 'failed')?'':$payment_method;
		$this->setState('filter.payment_method', $payment_method);
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		//$search = ($status == 'failed')?'':$search;
		$this->setState('filter.search', $search);
		// List state information.
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.searchid', 'filter_searchid');
		//$search = ($status == 'failed')?'':$search;
		$this->setState('filter.searchid', $search);
		
		$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
		//oseExit($_POST);
		//$db->escape($this->getState('list.ordering', 'c.expired_date')).' '.$db->escape($this->getState('list.direction', 'ASC'))
		//parent::populateState($this->getState('list.ordering', 'c.expired_date'), $this->getState('list.direction', 'ASC'));
		parent::populateState( 'c.expired_date', 'ASC');
	}
}