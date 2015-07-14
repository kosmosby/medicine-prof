<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: custom_fields
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class JTheFactoryListModel extends JModelLegacy {

    protected $context;

    function __construct($config = array()) {

        $this->context = APP_EXTENSION . '.model_' . $this->getName() . ( isset($config['task']) ? ('.'.$config['task']) : '' );

        parent::__construct($config);
    }

    function setContext($task) {

        if(!$task) {
            return;
        }

        $this->context .= '.'.$task;
    }

    function setCustomFilters($profile=null) {

        $app = JFactory::getApplication();

        $searchableFields = CustomFieldsFactory::getSearchableFieldsList();

        if($profile) {
            //the listing includes some profile filters
            $this->pushProfileIntegrationFields($searchableFields,$profile);
        }

        foreach($searchableFields as $field) {
            //TO DO: add 3rd and 4th params: default, type
            $requestKey = $field->page.'%'.$field->db_name;
            $value = $app->getUserStateFromRequest($this->context . '.filters.' . $requestKey, $requestKey);

            if(!empty($value)) {
                $this->setState('filters.'.$requestKey, $value );
            }
        }
    }

    function resetFilters($resetKey) {

        $app = JFactory::getApplication();
        $resetFilter = $this->getState('filters.'.$resetKey);

        if(empty($resetFilter)) {
            return;
        } else {
            //reset all filters
            $app->setUserState($this->context.'.filters', null);
        }
    }

    /*
     * @param object $query
     * @param string profile mode - helps decide if we use profile tables in this query; if so, we know the second table, that extends the basic joomla profile
     * @param string - userid foreign key
     * @param array - additional integration fields to be selected
     */
    function buildCustomQuery(&$query,$profileObject=null,$useridField=null,$selectIntegrationFields=array()) {

        $queriedTables = $query->getQueriedTables();

        $searchableFields = CustomFieldsFactory::getSearchableFieldsList();

        if($profileObject) {

            //first look for Joomla's users table; join it only if not queried
            $usersAlias = array_search('#__users',$queriedTables);
            if(false===$usersAlias) {
                if(!$useridField) {
                    JError::raiseWarning(500,'When using profile mode, the USERID foreign key is required!');
                    return;
                }
                $query->join('left','#__users','u',$useridField.'=`u`.`id`');
                $usersAlias = 'u';
            }

            //join the extended profile table
            $tableName = $profileObject->getIntegrationTable();
            $tableKey = $profileObject->getIntegrationKey();
            $query->join('left',$tableName,'ui','`'.$usersAlias.'`.`id`=`ui`.`'.$tableKey.'`');

            //the listing includes some profile filters
            $this->pushProfileIntegrationFields($searchableFields,$profileObject);

            //we need to "refresh" this because of the previous joins
            $queriedTables = $query->getQueriedTables();
        }

        foreach($searchableFields as $field) {

            $requestKey = $field->page.'%'.$field->db_name;
            $searchValue = $this->getState('filters.'.$requestKey);

            $fieldName = $field->db_name;
            $tableName = $field->own_table;
            $tableAlias = array_search($tableName,$queriedTables);

            if(false===$tableAlias) {
                //this IS a searchable field, but its parent table is not loaded in this context
                //i.e.: we use CB and this is a searchable CF for "component profile"
                continue;
            }

            //SELECT the field no matter what
            $query->select('`'.$tableAlias.'`.`'.$fieldName.'`');

            //no value from request => no SQL filter
            if(empty($searchValue)) {
                continue;
            }

            $ftype = CustomFieldsFactory::getFieldType( $field->ftype ? $field->ftype : 'inputbox');
            $query->where( $ftype->getSQLFilter($field,$searchValue,$tableAlias) );
        }

        foreach($selectIntegrationFields as $f) {
            $fieldName = $profileObject->getFilterField($f);
            $fieldTable = $profileObject->getFilterTable($f);

            $tableAlias = array_search($fieldTable, $queriedTables);
            if(!$fieldTable || !$tableAlias) {
                $query->select('NULL AS '.$f);
            } else {
                $query->select('`'.$tableAlias.'`.`'.$fieldName.'` AS '.$f);
            }
        }
    }

    function getFilters() {

        $filters = new JObject;
        //unset "_errors" so it does not interfere with filters
        unset($filters->_errors);

        foreach($this->getState() as $k=>$v) {
            if(preg_match('/filters\.(.*)/i',$k,$m)) {
                $filters->$m[1] = $v;
            }
        }

        return $filters;
    }

    function pushProfileIntegrationFields(& $searchableFields, $profileObject) {

        $integrationArray = $profileObject->getIntegrationArray();

        foreach($integrationArray as $alias=>$fieldName) {
            if(''!=$fieldName) {
                $newSearchableProfileField = new stdClass();
                $newSearchableProfileField->name = $alias;
                $newSearchableProfileField->db_name = $profileObject->getFilterField($fieldName);
                $newSearchableProfileField->page = 'user_profile';
                $newSearchableProfileField->own_table = $profileObject->getFilterTable($fieldName);
                $newSearchableProfileField->ftype = null;

                $searchableFields[] = $newSearchableProfileField;
            }
        }
    }
}

