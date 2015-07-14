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





/**
 * called for included php files within templates
 *
 * @param string $smarty_file
 * @param string $smarty_assign variable to assign the included template's
 *               output into
 * @param boolean $smarty_once uses include_once if this is true
 * @param array $smarty_include_vars associative array of vars from
 *              {include file="blah" var=$var}
 */

//  $file, $assign, $once, $_smarty_include_vars

function smarty_core_smarty_include_php($params, &$smarty)
{
    $_params = array('resource_name' => $params['smarty_file']);
    require_once(SMARTY_CORE_DIR . 'core.get_php_resource.php');
    smarty_core_get_php_resource($_params, $smarty);
    $_smarty_resource_type = $_params['resource_type'];
    $_smarty_php_resource = $_params['php_resource'];

    if (!empty($params['smarty_assign'])) {
        ob_start();
        if ($_smarty_resource_type == 'file') {
            $smarty->_include($_smarty_php_resource, $params['smarty_once'], $params['smarty_include_vars']);
        } else {
            $smarty->_eval($_smarty_php_resource, $params['smarty_include_vars']);
        }
        $smarty->assign($params['smarty_assign'], ob_get_contents());
        ob_end_clean();
    } else {
        if ($_smarty_resource_type == 'file') {
            $smarty->_include($_smarty_php_resource, $params['smarty_once'], $params['smarty_include_vars']);
        } else {
            $smarty->_eval($_smarty_php_resource, $params['smarty_include_vars']);
        }
    }
}


/* vim: set expandtab: */

?>
