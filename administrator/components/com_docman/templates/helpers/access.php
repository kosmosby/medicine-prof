<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperAccess extends KTemplateHelperDefault
{
    public function rules($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'component' => 'com_docman',
            'section' => 'component',
            'name' => 'rules',
            'asset' => null,
            'asset_id' => 0
        ))->append(array(
            'id' => $config->name
        ));

        $xml = <<<EOF
<form>
    <fieldset>
        <field name="asset_id" type="hidden" value="{$config->asset_id}" />
        <field name="{$config->name}" type="rules" label="JFIELD_RULES_LABEL"
            translate_label="false" class="inputbox" filter="rules"
            component="{$config->component}" section="{$config->section}" validate="rules"
            id="{$config->id}"
        />
    </fieldset>
</form>
EOF;

        $form = JForm::getInstance('com_docman.document.acl', $xml);
        $form->setValue('asset_id', null, $config->asset_id);

        return $form->getInput('rules');
    }
}
