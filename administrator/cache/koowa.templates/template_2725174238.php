<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Import necessary JavaScript and toolbars ?>
<?php echo $this->import('com://site/docman.document.form_scripts.html') ?>

<?php // Toolbar ?>
<div class="koowa_toolbar">
    <ktml:toolbar type="actionbar">
</div>

<?php // Form ?>
<div class="koowa_form">

    <div class="docman_form_layout">
        <form action="<?php echo $this->route('slug='. $document->slug.'&category_slug=' . $document->category_slug) ?>" method="post" class="-koowa-form">

            <div class="docman_container">
                <div class="docman_grid">
                    <div class="docman_grid__item two-thirds">

                        <?php // Details fieldset ?>
                        <fieldset>
                            <legend><?php echo $this->translate('Details') ?></legend>

                            <?php echo $this->import('com://site/docman.document.form_details.html') ?>

                        </fieldset>

                        <?php // Description fieldset ?>
                            <legend><?php echo $this->translate('Description') ?></legend>
                            <?php echo $this->import('com://site/docman.document.form_description.html') ?>
                    </div>

                    <div class="docman_grid__item one-third">
                        <?php // Publishing fieldset ?>
                        <fieldset>
                            <legend><?php echo $this->translate('Publishing') ?></legend>

                            <?php echo $this->import('com://site/docman.document.form_publishing.html') ?>

                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="docman_secondary_container">
                <div class="docman_container">
                    <div class="docman_grid">
                        <div class="docman_grid__item two-thirds">
                            <?php // Images fieldset ?>
                            <fieldset>
                                <legend><?php echo $this->translate('Images') ?></legend>

                                <?php echo $this->import('com://site/docman.document.form_images.html') ?>

                            </fieldset>
                        </div>
                        <div class="docman_grid__item one-third">
                            <?php // Audit fieldset ?>
                            <fieldset>
                                <legend><?php echo $this->translate('Audit') ?></legend>

                                <?php echo $this->import('com://site/docman.document.form_audit.html') ?>

                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>