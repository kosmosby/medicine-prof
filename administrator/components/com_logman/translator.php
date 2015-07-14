<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanTranslator extends ComDefaultTranslator
{
    /**
     * TODO Remove fix after Joomlatools Framework v1.0.4 is released.
     *
     * @see ComDefaultTranslator::_loadLanguageFile
     */
    protected function _loadLanguageFile($extension, $locale, array $base)
    {
        $result = false;

        foreach ($base as $path) {
            $signature = md5($extension . $path . $locale);

            $result = in_array($signature, self::$_loaded_files)
                || $this->_translation_helper->load($extension, $path, $locale, true, false);

            if ($result) {
                if (!in_array($signature, self::$_loaded_files)) {
                    self::$_loaded_files[] = $signature;
                }

                break;
            }
        }

        return $result;
    }
}