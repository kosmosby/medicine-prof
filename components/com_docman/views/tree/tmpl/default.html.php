<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('javascript' => true)); ?>

<div class="docman_list_layout docman_list_layout--tree">
    <div class="docman_list__sidebar">
        <div id="categories-tree"></div>
    </div>
    <div class="docman_list__content">
        <ktml:content>
    </div>
</div>

<?= helper('behavior.category_tree_site', array(
    'element' => '#categories-tree',
    'selected' => $selected,
    'state' => $state
)) ?>

<?= helper('behavior.sidebar', array(
    'sidebar'   => '#documents-sidebar',
    'target'    => '#categories-tree',
    'affix'     => false,
    'minHeight' => 100
)) ?>

