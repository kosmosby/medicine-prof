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
 * @subpackage: library
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryXLSCreator extends JObject
{
	function createXLS(&$rows,$row_headers=null,$title=null){


		ob_clean ();
		ob_start ();

		require_once (JPATH_ROOT.DS.'libraries'.DS.'pear'.DS.'PEAR.php');
		require_once (JPATH_COMPONENT_SITE.DS.'libraries'.DS.'Excel'.DS.'Writer.php');

		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		$worksheet =  $workbook->addWorksheet($title);

		$BIFF = new Spreadsheet_Excel_Writer_BIFFwriter();

		$format = new Spreadsheet_Excel_Writer_Format($BIFF);
		$format->setBold(1);
		$format->setAlign('center');

        for ($k=0;$k<count($row_headers);$k++){
		  $worksheet->write( 0, $k, $row_headers[$k], $format );
        }
        
		for($i = count($row_headers)?1:0; $i< count($rows); $i++){
            for ($k=0;$k<count($rows[$i]);$k++){
    		  $worksheet->write( $i, $k, $rows[$i][$k] );
            } 
		}
		$workbook->close();
		$attachment = ob_get_contents();

		@ob_end_clean();
		echo  $attachment;

	}
	
	function createXLSAdv(&$rows,$row_labels = null,$row_headers=null,$title=null ){


		ob_clean ();
		ob_start ();

		require_once (JPATH_COMPONENT_SITE.DS.'libraries'.DS.'pear'.DS.'PEAR.php');
		require_once (JPATH_COMPONENT_SITE.DS.'libraries'.DS.'Excel'.DS.'Writer.php');

		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		$worksheet =  $workbook->addWorksheet($title);

		$BIFF = new Spreadsheet_Excel_Writer_BIFFwriter();

		$format = new Spreadsheet_Excel_Writer_Format($BIFF);
		$format->setBold(1);
		$format->setAlign('center');

		for ($k=0;$k<count($row_labels);$k++){
			$worksheet->write( 0, $k, $row_headers[$row_labels[$k]], $format );
        }
        
		for ($i=0;$i<count($rows);$i++){
			for ($k=0;$k<count($row_labels);$k++){
				$worksheet->write( $i+1, $k, $rows[$i][$row_labels[$k]] );
            } 
		}
		$workbook->close();
		$attachment = ob_get_contents();

		@ob_end_clean();
		echo  $attachment;

	}

}

?>
