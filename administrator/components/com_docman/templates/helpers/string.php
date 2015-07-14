<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperString extends KTemplateHelperAbstract
{
    public function humanize($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'string' => '',
            'strip_extension' => false
        ));

        $string = $config->string;

        if ($config->strip_extension) {
            $string = pathinfo($string, PATHINFO_FILENAME);
        }

        $string = str_replace(array('_', '-', '.'), ' ', $string);
        $string = ucfirst($string);

        return $string;
    }

    public function truncate($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'text' => '',
            'offset' => 0,
            'length' => 100,
            'pad' => ' ...')
        );

        // Don't show endstring if actual string length is less than cutting length
        $config->endstr = (KHelperString::strlen($config->text) < $config->length) ? '' : $config->pad;

        return KHelperString::substr(strip_tags($config->text), $config->offset, $config->length) . $config->pad;
    }

    /**
     * Converts a byte size to human readable format
     * e.g. 1 Megabyte for 1048576
     *
     * @param array $config
     */
    public function bytes2text($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'sizes'      => array('Bytes' => 'B', 'Kilobytes' => 'KB', 'Megabytes' => 'MB', 'Gigabytes' => 'GB', 'Terabytes' => 'TB', 'Petabytes' => 'PB'),
            'abbreviate' => false
        ));
        $bytes = $config->bytes;
        $which = $config->abbreviate ? 's' : 'l';
        $result = '';
        $format = (($bytes > 1024*1024 && $bytes % 1024 !== 0) ? '%.2f' : '%d').' %s';

        foreach ($config->sizes as $l => $s) {
            $size = $$which;
            if ($bytes < 1024) {
                $result = $bytes;
                break;
            }
            $bytes /= 1024;
        }

        if ($result == 1) {
            $size = KInflector::singularize($size);
        }

        return sprintf($format, $result, $this->translate($size));
    }
}
