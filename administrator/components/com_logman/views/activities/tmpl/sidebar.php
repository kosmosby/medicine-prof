<?
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 *
 */
defined('_JEXEC') or die; ?>

<?= @helper('behavior.validator') ?>
<?= @helper('behavior.jquery') ?>

<script>
jQuery(function($) {
    var end_date = $('#end_date');

    if (!end_date.val()) {
    	end_date.val(<?= json_encode(@date(array('format' => '%Y-%m-%d'))); ?>);
    }

    $('#activities-filter').on('reset', function(e) {
        e.preventDefault();
        
        $(this).find('input').each(function(i, el) {
           if ($.inArray($(el).attr('name'), ['day_range','end_date', 'user', 'ip']) !== -1) {
               $(el).val('');
           }
        });

        $(this).submit();
    });
});
</script>

<div id="sidebar">
	<h4><?=@text( 'Components' )?></h4>
	<ul>
		<li class="<?= empty($state->package) ? 'active' : ''; ?>">
			<a href="<?= @route('package=') ?>">
		    <?= @text('All components')?>
			</a>
		</li>
	    <?php foreach ($packages as $package): ?>
		    <?php if ($package->id == $state->package): ?>
				<li class="active">
		    <?php else: ?> <li> <?php endif ?>
				<a href="<?=@route('package='.$package->id)?>"><?= @text(ucfirst($package->package))?></a>
			</li>
	    <?php endforeach ?>
	</ul>

	<div class="activities-filter">
		<h4><?=@text( 'Filters' )?></h4>

		<form action="" method="get" id="activities-filter" class="form-vertical">
			<fieldset>
				<div class="activities-calendar">
					<label for="end_date"><?=@text( 'Show events until' )?></label>
					<?= @helper('behavior.calendar',
							array(
								'date' => $state->end_date,
								'name' => 'end_date',
								'format' => '%Y-%m-%d'
							)); ?>
				</div>

				<div class="activities-days-back input-prepend input-append">
					<span class="add-on"><?= @text( 'Going back' )?></span><input type="text" size="3" id="day_range" name="day_range" class="span1" value="<?=$state->day_range?>" placeholder="&nbsp;&nbsp;&infin;" /><span class="add-on"><?= @text('days') ?></span>
				</div>

				<div class="activities-filter-by-user">
					<label for="user"><?=@text( 'Filter by User' )?></label>
					<div class="input-prepend">
						<? /* no line breaks or white space can be between the icon and the input or you'll have an ugly gap */ ?>
						<span class="add-on"><i class="icon-user"></i></span><?=
                            @helper('com://admin/users.template.helper.listbox.users', array(
                                'attribs' => array('placeholder' => @text('Enter a name&hellip;'))
                            ))
                        ?>
						</div>
				</div>

                <input type="hidden" name="ip" value="<?=$state->ip?>"/>

				<div class="activities-buttons">
					<input type="reset" name="cancelfilter" class="btn" value="<?=@text('Reset')?>" />
					<input type="submit" name="submitfilter" class="btn btn-primary" value="<?=@text('Filter')?>" />
				</div>
			</fieldset>
		</form>
	</div>
</div>
