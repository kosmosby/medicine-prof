<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

KService::get('koowa:loader')->loadIdentifier('com://admin/extman.template.helpers.behavior');

class ComDocmanTemplateHelperBehavior extends ComExtmanTemplateHelperBehavior
{
    public function download_tracker($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'selector' => '.docman-download',
            'category' => 'DOCman',
            'action'   => 'Download'
        ));

        $html = '';

        $signature = md5(serialize(array($config->selector, $config->category, $config->action)));
        if (!isset(self::$_loaded[$signature])) {
            $html .= "
            <script>
            window.addEvent('domready', function(){
                $$('{$config->selector}').addEvent('click', function(e) {
                    var el = this;
                    if (typeof _gaq !== 'undefined') {
                        if (_gat._getTrackers().length) {
                            _gaq.push(function() {
                                var tracker = _gat._getTrackers()[0];
                                tracker._trackEvent('{$config->category}', '{$config->action}', el.get('data-title'), parseInt(el.get('data-id'), 10));
                            });
                        }
                    }
                });
            });
            </script>
            ";
            self::$_loaded[$signature] = true;
        }

        return $html;
    }

    public function deletable($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'selector' => '.docman-deletable',
            'confirm_message' => $this->translate('You will not be able to bring this item back if you delete it. Would you like to continue?'),
        ));

        $html = '';

        $signature = md5(serialize(array($config->selector,$config->confirm_message)));
        if (!isset(self::$_loaded[$signature])) {
            $html .= "
            <script>
            window.addEvent('domready', function() {
            $$('{$config->selector}').addEvent('click', function(e){
            e.stop();
            if (!e.target.hasClass('disabled') && confirm('{$config->confirm_message}')) {
            new Koowa.Form(JSON.decode(e.target.getProperty('rel'))).submit();
            }
            });
            });
            </script>
            ";

            self::$_loaded[$signature] = true;
        }

        return $html;
    }

    public function select2($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
        'element' => '.select2-listbox',
        'options' => array(
        'width' => 'resolve',
            'dropdownCssClass' => 'com_docman'
        )
        ));

        $html ='';

        if (!isset(self::$_loaded['jquery'])) {
            $html .= $this->jquery();
        }

        if (!isset(self::$_loaded['select2'])) {

            $html .= '<script src="media://com_docman/js/select2.js" />';

            if(isset(self::$_loaded['validator']))
            {
                $html .= '<script src="media://com_docman/js/select2.validator.js" />';
            }

            $html .= '<script>jQuery(function($){
            $("'.$config->element.'").select2('.$config->options.');
        });</script>';

            self::$_loaded['select2'] = true;
        }

        return $html;
    }

    /*
     * Overriden to make the validator support Select2
    */
    public function validator($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
        'options'  => array(
        'fieldSelectors' => 'input, select, textarea'
        )
        ));

        return parent::validator($config);
    }

    // @TODO copy pasted code from admin version of this helper since we can't reuse classes with identical names
    public function thumbnail($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'images_root' => KRequest::root().'/joomlatools-files/docman-images/',
            'enable_automatic_thumbnail' => true,
            'automatic_thumbnail' => false,
            'automatic_thumbnail_text' => $this->translate('Automatically generate')
        ));

        //Don't cache automatic thumbnails
        if(!$config->automatic_thumbnail) $config->automatic_thumbnail_image = false;

        $value = $config->value;

        if (!$value) {
            $value = 'default.png';
        }

        if (substr($value, 0, 6) === 'image:') {
            $value = substr($value, 6);
        }

        if ($config->enable_automatic_thumbnail) {
            $html ='<div class="image-picker thumbnail-picker" style="margin-top: 5px">
                        <img
                                id="image-preview"
                                data-src="'.$value.'"
                                src="'.$config->images_root.$value.'"
                                onerror="this.src=\'media://com_docman/images/nothumbnail.png\';"
                                style="max-height:128px;max-width:128px;background:#EEE;margin:0;float:left"
                                    />
                        <div id="choose-thumbnail" class="dropdown btn-group" style="float:left;margin-left:5px">
                            <button class="btn choose-photo-button dropdown-toggle" id="profile_header_upload" type="button" data-toggle="dropdown"
                                    style="float:none">
                                    '. $this->translate('Change thumbnail') .'
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li id="automatic_thumbnail" class="dropdown-link">
                                    <a href="#" class="dropdown-link" id="automatic_thumbnail">
                                        <input style="display:none" type="checkbox" name="automatic_thumbnail" value="1"
                                        '. ($config->automatic_thumbnail ? 'checked="checked"' : '') .'
                                            />
                                        '. $this->translate('Automatically generate') .'
                                    </a>
                                </li>
                                <li id="thumbnail-choose-existing" class="dropdown-link">
                                    <div id="image-selector" class="image-selector">
                                        '. $this->getTemplate()->renderHelper('modal.image', $config->toArray()).'
                                    </div>
                                </li>
                                <li class="divider"></li>
                                <li id="thumbnail-delete-image" class="pretty-link dropdown-link ">
                                    <a href="#" class="dropdown-link btn" id="image-selector-clear">'. $this->translate('Clear') .'</a>
                                </li>
                            </ul>
                            <p style="font-size:13px;display:block;margin-top:5px;top:100%;left:0;width:100%;position:absolute;" class="help-inline">'.$this->translate('Thumbnail is automatically generated.').'</p>
                        </div>

                    </div>';
        } else {
            $html ='<div class="image-picker thumbnail-picker" style="margin-top: 5px">
                        <img
                                id="image-preview"
                                data-src="'.$value.'"
                                src="'.$config->images_root.$value.'"
                                onerror="this.src=\'media://com_docman/images/nothumbnail.png\';"
                                style="max-height:128px;max-width:128px;background:#EEE;margin:0;float:left"
                                    />
                        <div id="choose-thumbnail" class="dropdown btn-group" style="float:left;margin-left:5px">
                            <button class="btn choose-photo-button dropdown-toggle" id="profile_header_upload" type="button" data-toggle="dropdown"
                                    style="float:none">
                                    '. $this->translate('Change thumbnail') .'
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li id="thumbnail-choose-existing" class="dropdown-link">
                                    <div id="image-selector" class="image-selector">
                                        '. $this->getTemplate()->renderHelper('modal.image', $config->toArray()).'
                                    </div>
                                </li>
                                <li class="divider"></li>
                                <li id="thumbnail-delete-image" class="pretty-link dropdown-link ">
                                    <a href="#" class="dropdown-link btn" id="image-selector-clear">'. $this->translate('Clear') .'</a>
                                </li>
                            </ul>
                        </div>

                    </div>';
        }

        return $html;
    }

    public function calendar($config = array())
    {
        static $loaded;

        if ($loaded === null)
        {
            $loaded = array();
        }

        $config = new KConfig($config);
        $config->append(array(
            'value' => null,
            'name' => null,
            'format' => '%Y-%m-%d',
            'attribs' => array('placeholder' => ''), //This is to avoid Chrome displaying UNKNOWN in the input
            'filter' => null
        ))->append(array(
                'id' => $config->name
            ));

        // Load the necessary files if they haven't yet been loaded
        if (!isset(self::$_loaded['calendar']))
        {
            // Load the calendar behavior
            JHtml::_('behavior.calendar');
            JHtml::_('behavior.tooltip');

            self::$_loaded['calendar'] = true;
        }

        // Handle the special case for "now".
        if (strtoupper($config->value) == 'NOW')
        {
            $config->value = strftime($config->format);
        }

        switch (strtoupper($config->filter))
        {
            case 'SERVER_UTC':
                // Convert a date to UTC based on the server timezone.
                if (intval($config->value))
                {
                    // Get a date object based on the correct timezone.
                    $date = JFactory::getDate($config->value, 'UTC');
                    $date->setTimezone(new DateTimeZone(JFactory::getConfig()->get('offset')));

                    // Transform the date string.
                    $config->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;

            case 'USER_UTC':
                // Convert a date to UTC based on the user timezone.
                if (intval($config->value))
                {
                    // Get a date object based on the correct timezone.
                    $date = JFactory::getDate($config->value, 'UTC');
                    $date->setTimezone(new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'))));

                    // Transform the date string.
                    $config->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;
        }

        $attribs = JArrayHelper::toString(KConfig::unbox($config->attribs));

        if ($config->attribs->readonly !== 'readonly' && $config->attribs->disabled !== 'disabled') {
            // Only display the triggers once for each control.
            if (!in_array($config->id, $loaded)) {
                $document = JFactory::getDocument();
                $document
                    ->addScriptDeclaration(
                        'window.addEvent(\'domready\', function() {Calendar.setup({
                        // Id of the input field
                        inputField: "' . $config->id . '",
                    // Format of the input field
                    ifFormat: "' . $config->format . '",
                    // Trigger for the calendar (button ID)
                    button: "' . $config->id . '_img",
                    // Alignment (defaults to "Bl")
                    align: "Tl",
                    singleClick: true,
                    firstDay: ' . JFactory::getLanguage()->getFirstDay() . '
            });});'
                    );
                $loaded[] = $config->id;
            }

            $html = '';
            $html .= '<div class="input-append">';
            $html .= '<input type="text" name="'.$config->name.'" id="'.$config->id.'" value="'.$config->value.'" '.$attribs.' />';
            $html .= '<button type="button" id="'.$config->id.'_img" class="btn" >';
            $html .= '<i class="icon-calendar"></i>&zwnj;'; //&zwnj; is a zero width non-joiner, helps the button get the right height without adding to the width (like with &nbsp;)
            $html .= '</button>';
            $html .= '</div>';
        }
        else
        {
            $html = '';
            $html .= '<div class="input-append">';
            $html .= '<input type="text" name="'.$config->name.'" id="'.$config->id.'" value="'.$config->value.'" '.$attribs.' />';
            $html .= '</div>';
        }

        return $html;
    }
}
