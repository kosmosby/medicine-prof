<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanAliases extends KObject
{
    protected $_loaded = false;

    public function setLoaded($loaded)
    {
        $this->_loaded = $loaded;

        return $this;
    }

    public function setAliases()
    {
        if (!$this->_loaded) {
            $loader = KService::get('koowa:loader');

            if (KRequest::get('get.view', 'cmd') === 'doclink') {
                $loader->loadIdentifier('com://site/docman.model.default');
            }

            // This is here because if we map the controller, backend view will be used instead of the frontend
            $loader->loadIdentifier('com://admin/docman.controller.file');

            $loader->loadIdentifier('com://admin/docman.controller.behaviors.permissions');

            $loader->loadIdentifier('com://admin/default.controller.toolbars.default');
            $loader->loadIdentifier('com://admin/docman.controller.toolbars.default');

            $loader->loadIdentifier('com://admin/docman.model.documents');

            $maps = array(
                'com://site/docman.controller.doclink'              => 'com://admin/docman.controller.doclink',
                'com://site/docman.controller.file'                 => 'com://admin/docman.controller.file',
                'com://site/docman.controller.behavior.aclable'     => 'com://admin/docman.controller.behavior.aclable',
                'com://site/docman.controller.behavior.image'       => 'com://admin/docman.controller.behavior.image',
                'com://site/docman.model.files'                  	=> 'com://admin/docman.model.files',
                'com://site/docman.model.nodes'                  	=> 'com://admin/docman.model.nodes',
                'com://site/docman.database.table.nodes'	     	=> 'com://admin/docman.database.table.nodes',
                'com://site/docman.database.table.categories'    	=> 'com://admin/docman.database.table.categories',
                'com://site/docman.database.table.documents'     	=> 'com://admin/docman.database.table.documents',
                'com://site/docman.database.row.node'		    	=> 'com://admin/docman.database.row.node',
                'com://site/docman.database.row.category'	    	=> 'com://admin/docman.database.row.category',
                'com://site/docman.database.row.document'        	=> 'com://admin/docman.database.row.document',
                'com://site/docman.database.row.file'            	=> 'com://admin/docman.database.row.file',
                'com://site/docman.database.rowset.nodes'        	=> 'com://admin/docman.database.rowset.nodes',
                'com://site/docman.database.rowset.files'        	=> 'com://admin/docman.database.rowset.files',
                'com://site/docman.template.helper.grid' 	     	=> 'com://admin/docman.template.helper.grid',
                'com://site/docman.template.helper.listbox'      	=> 'com://admin/docman.template.helper.listbox',
                'com://site/docman.template.helper.toolbar'      	=> 'com://admin/default.template.helper.toolbar',
                'com://site/docman.template.helper.modal'         	=> 'com://admin/docman.template.helper.modal',
                'com://site/docman.template.filter.bootstrap'    	=> 'com://admin/docman.template.filter.bootstrap',
                'com://site/docman.template.filter.form'	    	=> 'com://admin/docman.template.filter.form',
                'com://site/docman.template.filter.style'	    	=> 'com://admin/docman.template.filter.style'
            );

            foreach ($maps as $alias => $identifier) {
                KService::setAlias($alias, $identifier);
            }

            $this->setLoaded(true);
        }

        return $this;
    }
}
