<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


class MedicineProductsController extends JControllerLegacy
{

	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->getCmd('view', 'list');
		$vFormat = $document->getType();
		$lName   = $this->input->getCmd('layout', 'default');

		if ($view = $this->getView($vName, $vFormat))
		{
			// Do any specific processing by view.
			switch ($vName)
			{
                                case 'item':
                                        $model = $this->getModel('Item');
                                        break;

				default:
					$model = $this->getModel('List');
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			$view->display();
		}
	}
        
        public function getItemsJson(){
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $callback = isset($_GET['callback'])?$_GET['callback']:'';
            $sidx = isset($_GET['sidx'])?$_GET['sidx']:'';
            $sord = isset($_GET['sord'])?$_GET['sord']:'';
            $cats = isset($_GET['cats'])?$_GET['cats']:'0';
            if(!$sidx){
                $sidx = 'a.code';
            }
            if(!$sord){
                $sord = 'DESC';
            }
            if($cats){
                $cats = substr($cats, 0, -1);
            }
		// Select the required fields from the table.
		$query = 'SELECT a.*, c.name as catname'
			. ' FROM #__comprofiler_plugin_cbmedizd AS a'
                        . ' LEFT JOIN #__comprofiler_plugin_cbmedizd_categories AS c ON c.id = a.category'
                        .($cats ? " WHERE a.category IN ({$cats})":"")
                        . ' ORDER BY '.$sidx.' '.$sord;
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                
                
                
                if($rows){
                    $intR = 0;
                    foreach ($rows as $row){
                        if(strlen($row->description) > 500){
                            $tmp = strip_tags($row->description);
                            $rows[$intR]->description = mb_substr($tmp,0,300).'...';
                        }
                        $intR ++;
                    }
                    //var_dump($rows);
                    $rows = json_encode($rows);
                    
                }else{
                    $rows = null;
                }
                $pages = ceil(count($rows)/20);
                
                echo $callback.'({"records":"'.count($rows).'","page":1,"total":'.$pages.',"rows":';
                echo $rows;
                
                echo '})';
                exit();
        }
}
