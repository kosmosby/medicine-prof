<?php defined('_JEXEC') or die('Restricted access');

    JHTML::_('behavior.mootools');
    $doc= JFactory::getDocument();
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/jxlib.standalone.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/locale.english.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/base.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/browser.record.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/browser.adaptor.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/dialog.category.js');
    $doc->addScript(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/browser.js');
    $doc->addStyleSheet(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/themes/crispin/jxtheme.uncompressed.css');
    $doc->addStyleSheet(JURI::base().'components/'.APP_EXTENSION.'/thefactory/category/js/browser.css');
    $doc->addScriptDeclaration("
            window.addEvent('load', function() {
                Jx.setLanguage('en-US');
                var catBrowser = new CB.CategoryBrowser({
                    container: 'tree-container',
                    urls: {
                        insert: 'index.php?option=".APP_EXTENSION."&task=category.newcatjson',
                        'delete': 'index.php?option=".APP_EXTENSION."&task=category.deletecatjson',
                        update: 'index.php?option=".APP_EXTENSION."&task=category.updatecatjson',
                        read: 'index.php?option=".APP_EXTENSION."&task=category.categoryjson'
                   }          
                });
            });
    ");
    $doc->addStyleDeclaration("
        .test-container {
            width: 100%;
            height: 500px;
            border: 1px black solid;
        }
    ");

?>
<div class="test-container" id="tree-container">

</div>
