<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman injector plugin interface.
 */
interface ComLogmanPluginInjector
{
    /**
     * Dependency injection setter.
     *
     * @param array $dependencies Associative array containing dependencies to be injected.
     */
    public function inject($dependencies = array());
}