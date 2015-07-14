<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Overrides com_files CSS files if they are present in DOCman
 */
class ComDocmanTemplateFilterOverrider extends KTemplateFilterAbstract implements KTemplateFilterRead
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority'   => KCommand::PRIORITY_HIGH
        ));

        parent::_initialize($config);
    }

    public function read(&$text)
    {
        if (preg_match_all('#<style\s*src="([^"]+)"(.*)\/>#iU', $text, $matches)) {
            foreach (array_unique($matches[1]) as $key => $match) {
                if (strpos($match, 'media://com_files/') !== false) {
                    $package = $this->getIdentifier()->package;
                    $path = str_replace('media://com_files/', 'media://com_'.$package.'/css/files/', $match);
                    $override = str_replace('media://', JPATH_ROOT.'/media/', $path);
                    if (file_exists($override)) {
                        $text = str_replace($match, $path, $text);
                    }
                }
            }
        }

        return $this;
    }
}
