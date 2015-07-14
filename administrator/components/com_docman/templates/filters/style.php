<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateFilterStyle extends ComDefaultTemplateFilterStyle
{
    /**
     * An array of MD5 hashes for loaded style strings
     */
    protected $_loaded = array();

    /**
     * Check if a style was added before calling JDocument.
     *
     * Note that for links the check is already done in JDocument so no need to repeat here.
     *
     * @see ComDefaultTemplateFilterStyle::_renderStyle()
     */
    protected function _renderStyle($style, $link, $attribs = array())
    {
        if (!$link) {
            $hash = md5($style.serialize($attribs));
            if (isset($this->_loaded[$hash])) {
                return;
            }
        }

        parent::_renderStyle($style, $link, $attribs);

        if (!$link) {
            $this->_loaded[$hash] = true;
        }
    }
}
