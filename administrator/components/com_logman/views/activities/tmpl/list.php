<?
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die; ?>

<? if(count($activities)) : ?>
    <script src="media://com_logman/js/logman.js"/>
    <?=@helper('behavior.bootstrap', array('type' => false))?>
    <?=@helper('behavior.modal')?>

    <script type="text/javascript">
        window.addEvent("domready", function () {
            Logman.Message.Parameters.init();
        });
    </script>

    <div id="activities" class="com_logman">
		<table class="table table-striped simple">
			<tfoot>
                <? if ($view_all): ?>
				<tr>
					<td colspan="2" class="browse-view-all"><a href="index.php?option=com_logman&view=activities" class="btn"><?=@text('VIEW_ALL')?></a></td>
				</tr>
                <? endif; ?>
			</tfoot>
			<tbody>
			<? foreach ($activities as $activity) : ?>
				<tr class="activities-activity <?=$user->id == $activity->created_by ? 'greyed':''?>">
					<td class="message">
						<?= @helper('activity.message', array('row' => $activity))?>
					</td>
                    <td align="right" class="time">
                        <?= @helper('activity.when', array('row' => $activity, 'humanize' => true))?>
                    </td>
				</tr>
	        <? endforeach; ?>
			</tbody>
		</table>
	</div>
<? endif ?>