<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/


class TheFactoryBIDSInstaller extends TheFactoryInstaller
{

    function askTemplateOverwrite()
    {
        ob_start();
        ?>
        <table width="100%">
        <tr>
            <td>
                <h1>
                The installation detected that you already had a previous installed version of Auctions Factory.
                </h1>
            </td>
        </tr>
        <tr>
            <td>
                <h2>
                The previously existing Auctions Template folder WAS NOT overwritten in order to preserve any changes you might have done. If you like to overwrite the contents of the template folder please click the button below
                </h2>            
            </td>        
        </tr>
        <tr>
            <td>
                <button style="background-color:red;color:black;" onclick="if(confirm('Are you sure that you want to overwrite your existing Auctions Factory templates?')) window.location='index.php?option=com_bids&task=installtemplates'">
                Overwrite Templates now!
                </button> 
            </td>        
        </tr>
        </table>
        <?php

        return ob_get_clean();
    }

    function insertDefaultCategory() {

        jimport('joomla.application.component.model');
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_categories'.DS.'tables');
        JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_categories'.DS.'models');

        $modelCategory = JModel::getInstance('category','CategoriesModel');
        $data = array (
          'id' => 0,
          'parent_id' => '1',
          'extension' => 'com_bids',
          'title' => 'Uncategorised',
          'alias' => '',
          'note' => '',
          'description' => '',
          'published' => '1',
          'access' => '1',
          'metadesc' => '',
          'metakey' => '',
          'created_user_id' => '0',
          'language' => 'en-GB',
          'rules' =>
          array (
            'core.create' =>
            array (
            ),
            'core.delete' =>
            array (
            ),
            'core.edit' =>
            array (
            ),
            'core.edit.state' =>
            array (
            ),
            'core.edit.own' =>
            array (
            ),
          ),
          'params' =>
          array (
            'category_layout' => '',
            'image' => '',
          ),
          'metadata' =>
          array (
            'author' => '',
            'robots' => '',
          ),
        );

        $modelCategory->save($data);
    }
}
