<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperModal extends ComFilesTemplateHelperModal
{
    public function icon($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name' => '',
            'attribs' => array(),
            'visible' => true,
            'link' => '',
            'link_text' => $this->translate('Choose Custom&hellip;'),
            'link_selector' => 'modal',
            'default_icon_path' => 'media://com_docman/images/icons',
            'callback' => 'Docman.selectIcon'
        ))->append(array(
            'id' => $config->name,
            'value' => $config->name
        ));

        if ($config->callback) {
            $config->link .= '&callback='.$config->callback;
        }

        $default_path = $config->default_icon_path;
        $image = $config->value;

        if (!$image) {
            $image = 'default.png';
        }

        if (substr($image, 0, 5) === 'icon:') {
            $image = 'icon://'.substr($image, 5);
        } else {
            $image = $default_path.'/'.$image;
        }

        $html  = $this->getTemplate()->renderHelper('behavior.bootstrap', array('javascript' => array('dropdown')));

        $html .= '<div class="btn-group dropdown-grid">
                        <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
                            <img
                                id="'.$config->id.'-preview"
                                data-src="'.$image.'"
                                src="'.$image.'"
                                onerror="this.src=\''.$default_path.'/default.png\'"
                                style="height:32px;width:32px"
                            />
                            <span class="caret" style="margin-top: 18px;"></span>
                        </a>
                        <ul class="dropdown-menu">';

        $folder = str_replace('media://', JPATH_ROOT.'/media/', $default_path);

        jimport('joomla.filesystem.folder');
        $icons = JFolder::files($folder, '(png|jpg|jpeg|gif|ico)$');
        foreach ($icons as $icon) {
            $html .= '<li class="icon">';
                $html .= '<a href="#" title="'.$icon.'" data-value="'.$icon.'">';
                    $html .= '<img src="'.$default_path.'/'.$icon.'" /><span>'.$icon.'</span>';
                $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '
                    <li class="spacer"></li>
                    <li class="divider"></li>
                    <li>';
        $link = '<a class="%s"
                    rel="{\'handler\': \'iframe\', \'size\': {\'x\': 690}}"
                    href="%s">%s</a>';
        $html .= sprintf($link, $config->link_selector, $config->link, $config->link_text);
        $html .= '</li>
                        </ul>
                    </div>';

        $input = '<input name="%1$s" id="%2$s" value="%3$s" %4$s size="40" %5$s style="display:none" />';
        $html .= sprintf($input, $config->name, $config->id, $config->value, $config->visible ? 'type="text" readonly' : 'type="hidden"', $config->attribs);

        $html .= "
        <script>
        jQuery(function($){
            if (typeof Docman === 'undefined') Docman = {};

            Docman.selectIcon = function(selected) {
                $('#".$config->id."').val('icon:'+selected);
                $('#".$config->id."').trigger('change');

                SqueezeBox.close();
            };

            Docman.icon_map = {
                'archive': ['7z','gz','rar','tar','zip'],
                'audio': ['aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'],
                'document': ['doc','docx','rtf','txt','ppt','pptx','pps','xml'],
                'image': ['bmp','gif','jpg','jpeg','png','psd','tif','tiff'],
                'pdf'  : ['pdf'],
                'spreadsheet': ['xls', 'xlsx', 'ods'],
                'video': ['3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv']
            };

            var preview = $('#".$config->id."-preview'),
                value = '',
                icon_path = 'icon://',
                dropdown = preview.parent(),
                event = function(){
                    var el = $(this),
                        value = el.val();

                    if (value.substr(0, 5) === 'icon:') {
                        value = icon_path + '/' + value.substr(5);
                    } else if (value) {
                        value = '".$default_path."/' + value;
                    } else {
                        value = 'media://system/images/blank.png';
                    }

                    preview.attr('src', value);
                    //Breaks on Joomla 3.0 due to no event argument being passed to Dropdown.toggle
                    //dropdown.dropdown('toggle');
                    //Workaround
                    if(dropdown.parent().hasClass('open')) dropdown.trigger('click');
                };

            preview.closest('.btn-group').find('li.icon a').click(function(e){
                e.preventDefault();
                var value = $(this).attr('data-value') ? ('".$default_path."/' + $(this).attr('data-value')) : 'media://system/images/blank.png';
                preview.attr('src', value);
                $('#".$config->id."').val($(this).attr('data-value'));
            });

            $('#".$config->id."').on('change', event);
        });
        </script>
        ";

        return $html;
    }

    public function image($config = array()) {
        $config = new KConfig($config);
        $config->append(array(
            'name' => 'image',
            'attribs' => array(),
            'visible' => true,
            'link_text' => $this->translate('Choose existing image'),
            'callback' => 'Docman.selectImage',
            'link_selector' => 'modal-button'
        ))->append(array(
            'link'  => JRoute::_('index.php?option=com_docman&view=files&layout=select_icon&tmpl=component&container=docman-images&types[]=image&callback='.$config->callback),
            'id' => $config->name,
            'value' => $config->name
        ));

        $html = "<script>
        jQuery(function($){
            if (typeof Docman === 'undefined') Docman = {};

            ".$config->callback." = function(selected) {
                $('#".$config->id."').val(selected);
                $('#".$config->id."').trigger('change')
                .closest('.dropdown').removeClass('open');
                $('#automatic_thumbnail input').prop('checked', false).trigger('change');
                $('#thumbnail-delete-image').removeClass('disabled');
                SqueezeBox.close();
            };

            //Thumbnail picker
            $('#".$config->id."').on('change', function(){
                var preview = $('#image-preview'),
                    icon_path = '".$config->images_root."',
                    el = $(this),
                    value = el.val();
                    if (value) {
                        value = icon_path + '/' + value;
                    } else {
                        value = 'media://com_docman/images/nothumbnail.png';
                    }
                preview.attr('src', value).removeClass('missing');
            });


            var automatic_thumbnail = function(){
                    var self = $('#automatic_thumbnail input'), container = $('#image').closest('.thumbnail-picker');
                if (self.prop('checked')) {
                    $('#automatic_thumbnail').addClass('disabled');
                    $('.help-inline.automatic-enabled', container).show();
                } else {
                    $('#automatic_thumbnail').removeClass('disabled');
                    $('.help-inline.automatic-enabled', container).hide();
                }

            };
            $('#automatic_thumbnail input').on('change', automatic_thumbnail);
            automatic_thumbnail();
            $('#automatic_thumbnail').data('automatic_thumbnail_image', '".$config->automatic_thumbnail_image."').on('click', function(e){
                e.preventDefault();

                if ($(this).hasClass('disabled')) {
                    $(this).closest('.dropdown').removeClass('open');

                    return;
                }

                $('#automatic_thumbnail input').prop('checked', true).trigger('change')
                    .closest('.dropdown').removeClass('open');
                $('#".$config->id."').val($(this).data('automatic_thumbnail_image')).trigger('change');
                $('#thumbnail-delete-image').removeClass('disabled');
            });

            $('#thumbnail-delete-image').on('click', function(e){
                e.preventDefault();
                $('#".$config->id."').val(null).trigger('change')
                    .closest('.dropdown').removeClass('open');
                $('#automatic_thumbnail input').prop('checked', false).trigger('change');
                $('#thumbnail-delete-image').addClass('disabled');
            });
            if(".json_encode(!$config->value).") $('#thumbnail-delete-image').addClass('disabled');
        });
        </script>
        ";

        $attribs = KHelperArray::toString($config->attribs);

        $input = '<input name="%1$s" id="%2$s" value="%3$s" %4$s size="40" %5$s />';

        $link = '<a class="%s btn"
                    rel="{\'handler\': \'iframe\', \'size\': {\'x\': 690}}"
                    href="%s">%s</a>';

        $html .= sprintf($input, $config->name, $config->id, $config->value, $config->visible ? 'type="text" readonly' : 'type="hidden"', $attribs);
        $html .= sprintf($link, $config->link_selector, $config->link, $config->link_text);

        return $html;
    }

    public function select($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name' => '',
            'attribs' => array(),
            'visible' => true,
            'link' => '',
            'link_text' => $this->translate('Select'),
            'link_selector' => 'modal'
        ))->append(array(
            'id' => $config->name,
            'value' => $config->name
        ));

        $attribs = KHelperArray::toString($config->attribs);

        $input = '<input name="%1$s" id="%2$s" value="%3$s" %4$s size="40" %5$s />';

        $link = '<a class="%s btn"
                    rel="{\'handler\': \'iframe\', \'size\': {\'x\': 690}}"
                    href="%s">%s</a>';

        $html = sprintf($input, $config->name, $config->id, $config->value, $config->visible ? 'type="text" readonly' : 'type="hidden"', $attribs);
        $html .= sprintf($link, $config->link_selector, $config->link, $config->link_text);

        return $html;
    }
}
