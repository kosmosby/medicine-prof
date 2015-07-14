<?php

jimport('joomla.application.component.modelform');

class JBidsAdminModelBackupRestore extends JModel {

    function backup() {

        $sqlFileName = 'bids.sql';
        $imagesDir = AUCTION_PICTURES_PATH;

        jimport('joomla.filesystem.archive');
        jimport('joomla.filesystem.file');

        if (!is_writable(AUCTION_BACKUPS_PATH)) {
            JError::raiseWarning(500,'<strong style="color:red;">' . AUCTION_BACKUPS_PATH . ' not writable!</strong>');
            return false;
        }

        $sqlData = $this->backupSQL();
        $imagesData = $this->backupImages();

        $backupFile = AUCTION_BACKUPS_PATH . DS . 'Auction_Factory_BKP_' . JHtml::date('now','d-M-Y_H-i-s') . '.zip';

        $aFilesData = array_merge($sqlData, $imagesData);

        $zip =  JArchive::getAdapter('zip');
        //the second parameter for this function is very odd
        //as you can see above, each "file" entity needs to be an array with 2 keys: 'name' and 'data'
        $zip->create($backupFile, $aFilesData);

        return $backupFile;
    }

    function backupSQL() {

        $tables = $this->getApplicationTables();

        $aSqlBackups = array();
        foreach ($tables as $bakup_table) {
            $fileName = str_replace('#__', '', $bakup_table);
            $aSqlBackups[] = array('name' => 'sql' . DS . $fileName . '.sql', 'data' => $this->_createBackupString($bakup_table));
        }

        //special table - #__categories
        $aSqlBackups[] = array('name' => 'sql' . DS . 'categories.sql', 'data' => $this->_createBackupString('#__categories','extension=\'com_bids\'') );

        return $aSqlBackups;
    }

    function backupImages() {

        jimport('joomla.filesystem.file');

        //list of files to backup
        $aFiles = array();
        $aFilesData = array();

        //images
        $imagesFiles = JFolder::files(AUCTION_PICTURES_PATH, '', false, true);

        //read files data
        foreach ($imagesFiles as $fileName) {
            $aFilesData[] = array('name' => 'images' . DS . basename($fileName), 'data' => file_get_contents($fileName));
        }

        return $aFilesData;
    }

    protected function getApplicationTables() {

        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $db->setQuery('SHOW TABLES LIKE \''.$app->getCfg('dbprefix').'bid%\'');

        $tables = $db->loadResultArray();
        foreach($tables as &$table) {
            $table = preg_replace('#'.$app->getCfg('dbprefix').'#','#__',$table,1);
        }

        return $tables;
    }

    /**
     *
     * SQL Dump Table Utility
     *
     */
    protected function _createBackupString($table,$where=null) {
        $database = JFactory::getDBO();
        $database->setQuery("select * from $table".($where ? (' WHERE '.$where) : ''));
        $rows = $database->loadObjectList();

        $file_tmp = '';
        foreach ($rows as $row) {
            $arr = JArrayHelper::fromObject($row);
            $fieldlist = array_keys($arr);
            $InsertDump = "INSERT INTO $table (`" . implode('`,`', $fieldlist) . "`) VALUES (";
            foreach ($arr as $key => $value) {
                $value = addslashes($value);
                $value = str_replace("\n", '\r\n', $value);
                $value = str_replace("\r", '', $value);
                $InsertDump .= "'$value',";
            }
            $file_tmp .= rtrim($InsertDump, ',') . ");\n";
        }
        return $file_tmp;
    }

    function extractBackup($file) {

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.archive');

        $zip =  JArchive::getAdapter('zip');
        if (!$zip->extract($file, JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore')) {
            return JError::raiseError(1, 'Error extracting backup!');
        }
    }

    function restoreImages() {

        if (JFolder::exists(JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore' . DS . 'images')) {
            JFolder::copy(JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore' . DS . 'images' , AUCTION_PICTURES_PATH, '', true);
        }
    }

    function restoreDatabase() {

        set_time_limit(0);
        jimport('joomla.filesystem.file');
        $db =  JFactory::getDBO();

        $db->setQuery('SET FOREIGN_KEY_CHECKS=0;');
        $db->query();

        //empty database
        foreach ( $this->getApplicationTables() as $bakup_table) {
            $db->setQuery('TRUNCATE TABLE '.$bakup_table);
            $db->query();
        }
        //database is empty

        // #__categories
        $db->setQuery('DELETE FROM #__categories WHERE extension=\'com_bids\'');
        $db->query();

        $extractedSQLFolder = JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore' . DS . 'sql' . DS;

        $fieldsSQLFile = $extractedSQLFolder . 'bid_fields.sql';
        if (!JFile::exists($fieldsSQLFile)) {
            JError::raiseWarning(1, $fieldsSQLFile . ' does not exist or not found!');
        }
        $fieldsSQL = JFile::read($fieldsSQLFile);
        $db->setQuery($fieldsSQL);
        $db->queryBatch();

        $db->setQuery('SELECT * FROM `#__bid_fields`');
        $rows = $db->loadObjectList();

        $existingFields = array();
        foreach($rows as $row) {
            if(!isset($existingCFs[$row->own_table])) {
                $db->setQuery('SHOW COLUMNS FROM '.$row->own_table);
                $fields = $db->loadObjectList();
                foreach($fields as $f) {
                    $existingFields[$row->own_table][] = $f->Field;
                }
            }
        }

        $field = JTable::getInstance('FieldsTable','JTheFactory');//new FactoryFieldsTable($db);
        foreach ($rows as $row) {
            $field->bind($row);
            $forceCreate = !in_array($row->db_name,$existingFields[$row->own_table]);
            $field->store($forceCreate);
        }

        $files = JFolder::files($extractedSQLFolder);
        foreach ($files as $file) {
            if ('bid_fields.sql' == $file) {
                continue;
            }
            $fileSQL = JFile::read($extractedSQLFolder . $file);
            $db->setQuery($fileSQL);
            if (!$db->queryBatch()) {
                JError::raiseWarning(1,'Error on restoring: "' . $fileSQL . '"!');
            }
        }

        $db->setQuery('SET FOREIGN_KEY_CHECKS=1;');
        $db->query();

        return true;
    }

    function cleanRestore() {

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        if (JFile::exists(JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore.zip')) {
            JFile::delete(JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore.zip');
        }
        JFolder::delete(JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore');
    }
}