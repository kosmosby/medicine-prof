<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperBehavior extends ComKoowaTemplateHelperBehavior
{
    /**
     * Shorthand to use in template files in frontend
     *
     * @param array $config
     * @return string
     */
    public function thumbnail_modal($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'selector' => '.koowa .thumbnail',
            'options'  => array(
                'type' => 'image'
            )
        ));

        return $this->modal($config);
    }

    /**
     * Uses Google Analytics to track download events in frontend
     * @param array $config
     * @return string
     */
    public function download_tracker($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'selector' => 'docman_track_download',
            'category' => 'DOCman',
            'action'   => 'Download'
        ));

        $html = $this->jquery();

        $signature = md5(serialize(array($config->selector, $config->category, $config->action)));
        if (!isset(self::$_loaded[$signature])) {
            $html .= "
            <script>
            kQuery(function($) {
                $('.{$config->selector}').on('click', function() {
                    var el = $(this);
                    if (typeof _gaq !== 'undefined') {
                        if (_gat._getTrackers().length) {
                            _gaq.push(function() {
                                var tracker = _gat._getTrackers()[0];
                                tracker._trackEvent('{$config->category}', '{$config->action}', el.data('title'), parseInt(el.data('id'), 10));
                            });
                        }
                    } else if ('ga' in window && window.ga !== undefined && typeof window.ga === 'function' ) {
                        window.ga('send', 'event', '{$config->category}', '{$config->action}', el.data('title'), parseInt(el.data('id'), 10));
                    }
                });

                if (typeof _paq !== 'undefined') {
                    _paq.push(['setDownloadClasses', '{$config->selector}']);
                    _paq.push(['trackPageView']);
                }
            });
            </script>
            ";
            self::$_loaded[$signature] = true;
        }

        return $html;
    }

    /**
     * Makes links delete actions
     *
     * Used in frontend delete buttons
     *
     * @param array $config
     * @return string
     */
    public function deletable($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'selector' => '.docman-deletable',
            'confirm_message' => $this->getObject('translator')->translate('You will not be able to bring this item back if you delete it. Would you like to continue?'),
        ));

        $html = $this->koowa();

        $signature = md5(serialize(array($config->selector,$config->confirm_message)));
        if (!isset(self::$_loaded[$signature])) {
            $html .= "
            <script>
            kQuery(function($) {
                $('{$config->selector}').on('click', function(event){
                    event.preventDefault();

                    var target = $(event.target);

                    if (!target.hasClass('disabled') && confirm('{$config->confirm_message}')) {
                        new Koowa.Form($.parseJSON(target.prop('rel'))).submit();
                    }
                });
            });
            </script>
            ";

            self::$_loaded[$signature] = true;
        }

        return $html;
    }

    /**
     * Widget for picking an icon
     *
     * Renders as a button that toggles a dropdown menu, with a list over selectable icon thumbs at the top
     * and a Choose Custom button that triggers a modal popup with a file browser for choosing a custom image.
     *
     * Used in document and category forms next to the title input element
     *
     * @param array $config
     * @return string
     */
    public function icon($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name' => '',
            'attribs' => array(),
            'visible' => true,
            'link' => '',
            'link_text' => $this->getObject('translator')->translate('Select custom icon&hellip;'),

        ))->append(array(
            'options' => array(
                'custom_icon_path'  => 'icon://',
                'blank_icon_path'   => 'media://system/images/blank.png'
            ),
            'icons' => array(
                'archive', 'audio', 'default', 'document', 'folder',
                'image', 'pdf', 'spreadsheet', 'video'
            ),
            'id' => $config->name,
            'value' => $config->name
        ))->append(array(
            'callback' => 'select_'.$config->id,
            'options' => array(
                'id' => $config->id
            )
        ));

        if ($config->callback)
        {
            $config->options->callback = $config->callback;
            //This value is passed to the modal.icon helper
            $config->callback = 'Docman.Modal.request_map.'.$config->callback;
        }

        $image = $config->value;
        $font_icon = true;

        if (!$image) {
            $image = 'default';
        }

        if (substr($image, 0, 5) === 'icon:') {
            $image = 'icon://'.substr($image, 5);
            $font_icon = false;
        }

        $html  = $this->getTemplate()->helper('bootstrap.load', array('javascript' => true));
        $html .= '<ktml:script src="media://com_docman/js/modal.js" />';

        $html .= '<div class="docman_dropdown_grid">
                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                            <span id="'.$config->id.'-font-preview"
                                  class="koowa_icon--'.($font_icon ? $image : '').'"
                                  style="display:'.($font_icon ? 'inline-block' : 'none').'"
                            ></span>
                            <img
                                id="'.$config->id.'-preview"
                                data-src="'.$image.'"
                                '.($font_icon ? '' : 'src="'.$image.'"').'
                                onerror="this.src=\''.$config->options->blank_image_path.'\'"
                                style="display:'.($font_icon ? 'none' : 'inline-block').'"
                            />
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">';

        foreach($config->icons as $icon)
        {
            $html .= '<li class="icon"><a href="#" title="'.$this->getObject('translator')->translate($icon).'" data-value="'.$icon.'">';
            $html .= '<span class="koowa_icon--'.$icon.'"><i>'.$this->getObject('translator')->translate($icon).'</i></span>';
            $html .= '</a></li>';
        }

        $html .= '
                    <li class="spacer"></li>
                    <li class="divider"></li>
                    <li>';

        $html .= $this->getTemplate()->helper('modal.icon', $config->toArray());
        $html .= '</li>
                        </ul>
                    </div>';

        /**
         * str_replace helps convert the paths before the template filter transform media:// to full path
         */
        $options = str_replace('\/', '/', $config->options->toString());

        $html .= $this->icon_map();

        /**
         * str_replace helps convert the paths before the template filter transform media:// to full path
         */
        $html .= "<script>kQuery(function($){new Docman.Modal.Icon(".$options.");});</script>";


        return $html;
    }

    public function icon_map($config = array())
    {
        $icon_map = json_encode(ComFilesTemplateHelperIcon::getIconExtensionMap());

        $html = "
            <script>
            if (typeof Docman === 'undefined') Docman = {};

            Docman.icon_map = $icon_map;
            </script>";

        return $html;
    }

    /**
     * Widget for selecting an thumbnail image
     *
     * Renders as a button that toggle a dropdown menu, with an optional menu item for toggling the automatically generate
     * state, Choose Custom and Clear selection.
     *
     * Used in document forms with allow_automatic enabled, on category forms without
     *
     * @param array $config
     * @return string
     */
    public function thumbnail($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'automatic_switch' => false,
            'allow_automatic'  => $this->getObject('com://admin/docman.model.configs')->fetch()->thumbnails,
            'id'               => $config->name
        ))->append(array(
            'callback' => 'select_'.$config->id
        ))->append(array(
            'options'  => array(
                'id'               => $config->id,
                'callback'         => $config->callback,
                'image_folder'     => 'root://joomlatools-files/docman-images/',
                'has_automatic'    => $config->has_automatic,
                'automatic_path'   => $config->automatic_path,
                'automatic_switch' => $config->automatic_switch
            )
        ));

        if ($config->callback) {
            $config->callback = 'Docman.Modal.request_map.'.$config->callback;
        }

        $html = $this->getTemplate()
            ->loadFile('com://admin/docman.document.thumbnail.html')
            ->render(array('config' => $config));

        return $html;
    }

    public function calendar($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'format' => '%Y-%m-%d'
        ));

        return parent::calendar($config);
    }

    /**
     * Loading js necessary to render a jqTree sidebar navigation of document categories
     *
     * @param array|KObjectConfig $config
     * @return string	The html output
     */
    public function category_tree($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug'   => JFactory::getApplication()->getCfg('debug'),
            'element' => '#categories-tree',
            'selected'  => '',
            'list'    => array(),
            'options' => array(
                'lang' => array(
                    'root' => $this->getObject('translator')->translate('All Categories')
                )
            )
        ))->append(array(
            'options' => array(
                'selected' => $config->selected
            )
        ));

        $data = array();
        foreach($config->list as $item)
        {
            $parts = explode('/', $item->path);
            array_pop($parts); // remove current id
            $data[] = array(
                'label'  => $item->title.(isset($item->document_count) ? ' ('.$item->document_count.')' : ''),
                'id'     => (int)$item->id,
                'path'   => $item->path,
                'parent' => (int)array_pop($parts)
            );
        }
        $config->options->append(array('data' => $data));

        // Load assets by calling parent tree behavior
        $html = parent::tree(array('debug' => $config->debug));

        if (!isset(self::$_loaded['category_tree']))
        {
            $html .= '<ktml:script src="media://com_docman/js/category.tree.js" />';

            $html .= '<script>
            kQuery(function($){
                new DOCman.Tree.Categories('.json_encode($config->element).', '.$config->options.');
            });</script>';

            self::$_loaded['category_tree'] = true;
        }

        return $html;
    }

    /**
     * Loading js necessary for doclink to render a jqTree sidebar and the other UI features
     *
     * @param array|KObjectConfig $config
     * @return string	The html output
     */
    public function doclink($config = array())
    {
        $translator = $this->getObject('translator');

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug'   => JFactory::getApplication()->getCfg('debug'),
            'list'    => array(), // com://admin/docman.model.pages list, preprocessed by com://admin/docman.view.doclink.html
            'options' => array(
                'lang' => array(
                    'empty_folder_text' => $translator->translate('No documents found.'),
                    'insert_category' => $translator->translate('Insert category link'),
                    'insert_document' => $translator->translate('Insert document link'),
                    'insert_menu' => $translator->translate('Insert menu link')
                )
            )
        ))->append(array(
                'options' => array(
                    'editor' => $config->editor
                )
            ));

        $data = array();
        foreach($config->list as $page)
        {
            $target = ($page->params->get('document_title_link') === 'download' && $page->params->get('download_in_blank_page')) ? 'blank' : '';
            $tag    = '';

            if ($page->language && JLanguageMultilang::isEnabled())
            {
                $length = strlen($page->language);
                if ($length == 5 || $length == 6) {
                    $tag = substr($page->language, 0, $length-3);
                }
            }

            $entity = array(
                'label'    => $page->title.(!empty($page->language) ? ' ('.$page->language.')' : ''),
                'tag'      => $tag,
                'id'       => 'page'.$page->id,
                'itemid'   => (int)$page->id,
                'view'     => $page->query['view'],
                'target'   => $target,
                'children' => array()
            );

            foreach($page->categories as $category)
            {
                $parts = explode('/', $category->path);
                array_pop($parts); // remove current id
                $parent = (int)array_pop($parts);
                $entity['children'][] = array(
                    'label'       => $category->title,
                    'tag'         => $tag,
                    'slug'        => $category->slug,
                    'itemid'      => $category->itemid,
                    'id'          => 'page'.$page->id.'category'.$category->id,
                    'category_id' => (int)$category->id,
                    'path'        => $category->path,
                    'parent'      => 'page'.$page->id.'category'.$parent,
                    'target'      => $target
                );
            }

            $data[] = $entity;
        }
        $config->options->append(array('data' => $data));

        // Load assets by calling parent tree behavior
        $html = parent::tree(array('debug' => $config->debug));

        if (!isset(self::$_loaded['doclink']))
        {
            $html .= '<ktml:script src="media://com_docman/js/category.tree.js" />';
            $html .= '<ktml:script src="media://com_docman/js/doclink.js" />';
            $html .= '<ktml:script src="media://com_docman/js/footable.js" />';
            $html .= '<ktml:script src="media://com_docman/js/footable.sort.js" />';
            $html .= '<ktml:script src="media://koowa/com_files/js/spin.min.js" />';

            $html .= '<script>
            kQuery(function($){

                new DOCman.Doclink('.$config->options.');

                //Workaround for templates that incorrectly wrap &tmpl=component views breaking the layout
                $(".docman-container.doclink").parents().css({width: "auto", padding: 0, margin: 0});
            });</script>';

            self::$_loaded['doclink'] = true;
        }

        return $html;
    }

    /**
     * Attaches Bootstrap Affix to the sidebar along with custom code making it responsive
     *
     * @NOTE Also contains j!3.0 specific fixes
     *
     * @TODO requires bootstrap-affix!
     *
     * @param array|KObjectConfig $config
     * @return string	The html output
     */
    public function sidebar($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'sidebar'   => '',
            'target'    => ''
        ));

        $html = '';
        // Load the necessary files if they haven't yet been loaded
        if (!isset(self::$_loaded['sidebar']))
        {
            $html .= $this->jquery();
            //@TODO requires bootstrap-affix!
            //helper('bootstrap.load', array('javascript' => true))
            $html .= '<ktml:script src="media://com_docman/js/sidebar.js" />';

            self::$_loaded['sidebar'] = true;
        }

        $html .= '<script>kQuery(function($){new DOCman.Sidebar('.$config.');});</script>';

        return $html;
    }

    /**
     * JS Behavior to button-groups
     *
     * @param array|KObjectConfig $config
     * @return string	The html output
     */
    public function buttongroup($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'element'   => ''
        ));

        $html = '';
        // Load the necessary files if they haven't yet been loaded
        if (!isset(self::$_loaded['buttongroup']))
        {
            $html .= $this->jquery();
            $html .= $this->koowa();
            $html .= '<ktml:script src="media://com_docman/js/buttongroup.js" />';

            self::$_loaded['buttongroup'] = true;
        }

        $html .= '<script>kQuery(function($){new DOCman.Buttongroup('.$config.');});</script>';

        return $html;
    }
}
