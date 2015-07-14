<?php
/*
 * ARI YUI menu Joomla! module
 *
 * @package		ARI YUI Menu Joomla! module.
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

$menu = AriUtils::getParam($params, 'menu');
$menuId = AriUtils::getParam($params, 'menuId');
$menuStartLevel = AriUtils::getParam($params, 'menuStartLevel');
$menuEndLevel = AriUtils::getParam($params, 'menuEndLevel');
$menuLevel = AriUtils::getParam($params, 'menuLevel');
$menuDirection = AriUtils::getParam($params, 'menuDirection');
$isVertical = ($menuDirection != 'horizontal');
$parent = AriUtils::getParam($params, 'parent');
$advSeparator = AriUtils::getParam($params, 'advSeparator', false);
$hlCurrentItem = AriUtils::getParam($params, 'hlCurrentItem');
$hlOnlyActiveItems = AriUtils::getParam($params, 'hlOnlyActiveItems');
$showHiddenItems = AriUtils::getParam($params, 'showHiddenItems');
$remainActive = AriUtils::getParam($params, 'remainActive');
$activeTopId = AriUtils::getParam($params, 'activeTopId');

$isMainLevel = ($menuLevel == $menuStartLevel);

$cssClass = (!$isMainLevel || $isVertical) ? 'yuimenu' : 'yuimenubar yuimenubarnav';
$hrefCssClass = (!$isMainLevel || $isVertical) ? 'yuimenuitemlabel' : 'yuimenubaritemlabel';
$menuItemClass = (!$isMainLevel || $isVertical) ? 'yuimenuitem' : 'yuimenubaritem';
$menuItemDisabledClass = (!$isMainLevel || $isVertical) ? 'yuimenuitem-disabled' : 'yuimenubaritem-disabled';
$hrefDisabledCssClass = (!$isMainLevel || $isVertical) ? 'yuimenuitemlabel-disabled' : 'yuimenubaritemlabel-disabled';
$selectedCssClass = (!$isMainLevel || $isVertical) ? 'yuimenuitem-selected' : 'yuimenubaritem-selected';
$hrefSelectedCssClass = (!$isMainLevel || $isVertical) ? 'yuimenuitemlabel-selected' : 'yuimenubaritemlabel-selected';

if ($params['remainActive'])
{
	$selectedCssClass .= (!$isMainLevel || $isVertical) ? ' yuimenuitem-active' : ' yuimenubaritem-active';
	$hrefSelectedCssClass .= (!$isMainLevel || $isVertical) ? ' yuimenuitemlabel-active' : ' yuimenubaritemlabel-active'; 
}

$currentLevelMenu = $menu->getItems(ARI_MENU_LEVEL_PARAM, $menuLevel);
?>

<?php
if ($currentLevelMenu):
	$parentParam = ARI_MENU_PARENT_PARAM;
?>
	<div class="<?php echo $cssClass; ?>"<?php if ($isMainLevel): ?> id="<?php echo $menuId; ?>"<?php endif;?>>
		<div class="bd">
			<ul<?php if ($isMainLevel): ?> class="first-of-type"<?php endif; ?>>
			<?php
				$i = 0;
				$menuCnt = count($currentLevelMenu);
				foreach ($currentLevelMenu as $menuItem):
					if ((!J1_6 && !$menuItem->published) || ($parent && $menuItem->$parentParam != $parent))
						continue ;
	
					$hasChilds = (($menuEndLevel < 0 || $menuLevel + 1 <= $menuEndLevel) && $menu->hasChilds($menuItem->id));
					if (($menuItem->type == 'separator' && !$hasChilds)): 
						if ($i < $menuCnt - 1 && (!$isMainLevel || $isVertical)):
							if (!$advSeparator):
			?>
			</ul>
			<ul>
			<?php
							else:
			?>
				<li class="<?php echo $menuItemClass . ' ' . $menuItemDisabledClass; ?> yuimenuitem-separator"><?php echo stripslashes(htmlspecialchars(J1_6 ? $menuItem->title : $menuItem->name)); ?></li>
				<?php
							endif;
						endif;

						continue;
					endif;

					if ($isMainLevel && $hlOnlyActiveItems && !$menu->isChildOrSelf($menuItem->id, $activeTopId))
						continue ;

					$isDisabled = !$menu->authorize($menuItem->id);
					if ($isDisabled && !$showHiddenItems)
						continue ;
					
					$isSelected = ($hlCurrentItem && ($menu->isSelfOrParentActive($menuItem->id) || $menu->isChildActive($menuItem->id)));
					$link = $menu->getLink($menuItem->id);
					$aAttr = array(
						'class' => $hrefCssClass . ($isDisabled ? ' ' . $hrefDisabledCssClass : '') . ($isSelected ? ' ' . $hrefSelectedCssClass : '')
					);
					if (!$isDisabled && $link)
					{
						switch ($menuItem->browserNav)
						{
							case 1:
								$aAttr['target'] = '_blank';
								break;
									
							case 2:
								$aAttr['onclick'] = "window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,');return false;";
								break;
						}
					}
					else
					{
						$link = 'javascript:void(0);';
					}
					
					$aAttr['href'] = $link;
	
					$menuAbsLevel = $menuLevel - $menuStartLevel;
					$liClass = $menuItemClass;
					$liClass .= ' yuimenuitem-level-' . $menuAbsLevel;
					if ($hasChilds)
						$liClass .= ' yuimenuitem-parent';
					$liClass .= ' yuimenuitem-item' . $menuItem->id;
					if ($isSelected)
						$liClass .= ' ' . $selectedCssClass;
					if ($isDisabled)
						$liClass .= ' ' . $menuItemDisabledClass;
					if ($isMainLevel && !$i)
						$liClass .= ' first-of-type';
			?>
			<li class="<?php echo $liClass; ?>">
				<a<?php echo AriHtmlHelper::getAttrStr($aAttr); ?>><?php echo stripslashes(htmlspecialchars(J1_6 ? $menuItem->title : $menuItem->name)); ?></a>
				<?php
					if ($hasChilds && ($menuEndLevel < 0 || $menuLevel + 1 <= $menuEndLevel)):
						AriTemplate::display(
							__FILE__, 
							array(
								'menu' => $menu,
								'menuStartLevel' => $menuStartLevel,
								'menuEndLevel' => $menuEndLevel,
								'menuLevel' => $menuLevel + 1,
								'menuDirection' => $menuDirection,
								'parent' => $menuItem->id,
								'hlCurrentItem' => $hlCurrentItem,
								'hlOnlyActiveItems' => $hlOnlyActiveItems,
								'showHiddenItems' => $showHiddenItems,
								'remainActive' => $remainActive,
								'advSeparator' => $advSeparator,
								'activeTopId' => $activeTopId 
							)
						);
					endif;
				?>
			</li>
		<?php
				++$i;
			endforeach;
		?>
			</ul>
		</div>
	</div>
<?php
endif; 
?>