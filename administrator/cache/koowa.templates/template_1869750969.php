<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php if (substr($icon, 0, 5) === 'icon:'): $icon = substr($icon, 5); ?>
    <span class="koowa_header__image_container<?php echo isset($class) ? $class : '' ?>"><img src="icon://<?php echo $icon ?>" class="koowa_header__image" /></span>
<?php else: ?>
    <span class="koowa_icon--<?php echo $icon ?><?php echo isset($class) ? $class : '' ?>"><i><?php echo $this->translate($icon); ?></i></span>
<?php endif; ?>