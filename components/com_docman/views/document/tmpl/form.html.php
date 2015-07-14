<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Import necessary JavaScript and toolbars ?>
<?= import('com://site/docman.document.form_scripts.html') ?>

<? // Toolbar ?>
<div class="koowa_toolbar">
    <ktml:toolbar type="actionbar">
</div>

<? // Form ?>
<div class="koowa_form">

    <div class="docman_form_layout">
        <form action="<?= route('slug='. $document->slug.'&category_slug=' . $document->category_slug) ?>" method="post" class="-koowa-form">

            <div class="koowa_container">
                <div class="koowa_grid__row">
                    <div class="koowa_grid__item two-thirds">

                        <? // Details fieldset ?>
                        <fieldset>
                            <legend><?= translate('Details') ?></legend>

                            <?= import('com://site/docman.document.form_details.html') ?>

                        </fieldset>

                        <? // Description fieldset ?>
                            <legend><?= translate('Description') ?></legend>
                            <?= import('com://site/docman.document.form_description.html') ?>
                    </div>

                    <div class="koowa_grid__item one-third">
                        <? // Publishing fieldset ?>
                        <fieldset>
                            <legend><?= translate('Publishing') ?></legend>

                            <?= import('com://site/docman.document.form_publishing.html') ?>

                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="koowa_secondary_container">
                <div class="koowa_container">
                    <div class="koowa_grid__row">
                        <div class="koowa_grid__item two-thirds">
                            <? // Images fieldset ?>
                            <fieldset>
                                <legend><?= translate('Images') ?></legend>

                                <?= import('com://site/docman.document.form_images.html') ?>

                            </fieldset>
                        </div>
                        <div class="koowa_grid__item one-third">
                            <? // Audit fieldset ?>
                            <fieldset>
                                <legend><?= translate('Audit') ?></legend>

                                <?= import('com://site/docman.document.form_audit.html') ?>

                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>