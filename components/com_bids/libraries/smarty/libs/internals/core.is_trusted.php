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
 * determines if a resource is trusted or not
 *
 * @param string $resource_type
 * @param string $resource_name
 * @return boolean
 */

 // $resource_type, $resource_name

function smarty_core_is_trusted($params, &$smarty)
{
    $_smarty_trusted = false;
    if ($params['resource_type'] == 'file') {
        if (!empty($smarty->trusted_dir)) {
            $_rp = realpath($params['resource_name']);
            foreach ((array)$smarty->trusted_dir as $curr_dir) {
                if (!empty($curr_dir) && is_readable ($curr_dir)) {
                    $_cd = realpath($curr_dir);
                    if (strncmp($_rp, $_cd, strlen($_cd)) == 0
                        && substr($_rp, strlen($_cd), 1) == DIRECTORY_SEPARATOR ) {
                        $_smarty_trusted = true;
                        break;
                    }
                }
            }
        }

    } else {
        // resource is not on local file system
        $_smarty_trusted = call_user_func_array($smarty->_plugins['resource'][$params['resource_type']][0][3],
                                                array($params['resource_name'], $smarty));
    }

    return $_smarty_trusted;
}

/* vim: set expandtab: */

?>
