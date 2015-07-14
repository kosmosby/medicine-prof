<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageTranslatorDefault extends ComLogmanTranslator
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'alias_catalogue' => 'koowa:translator.catalogue',
            'prefix'          => 'KLS_ACTIVITY_',
            'catalogue'       => 'com://admin/logman.activity.message.translator.catalogue.default'));
        parent::_initialize($config);
    }

    /**
     * Checks if a translation for the provided string exists.
     *
     * @param string The string to search a translation for.
     *
     * @return bool True if exists, false otherwise.
     */
    public function hasTranslation($string)
    {
        return $this->_translation_helper->hasKey($this->getKey($string));
    }

    /**
     * Translates activity messages.
     *
     * Peforms an intelligent fallback mechanism for looking for more precise keys for languages
     * that require dealing with verb conjugations and genders depending on context.
     *
     * @param string $string     The text to translate.
     * @param array  $parameters An optional array containing translator parameter objects.
     *
     * @return string The translated text.
     */
    public function message($string, array $parameters = array())
    {
        if ($parameters) {
            foreach ($this->_getVariations($string, $parameters) as $variation) {
                // Check if a key for the variation exists.
                if ($this->hasTranslation($variation)) {
                    $string = $variation;
                    break;
                }
            }
        }

        $replacements = array();

        foreach ($parameters as $parameter) {
            $replacements[$parameter->getName(true)] = $parameter;
        }

        $translation = parent::translate($string, $replacements);

        // Process context translations.
        if (preg_match_all('/%(.+?):(.+?)%/', $translation, $matches) !== false) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $replacement = '%' . $matches[1][$i] . '%';
                if (isset($replacements[$replacement])) {
                    $parameter = clone $replacements[$replacement];
                    // Changing text for the one provided by the translation and making the parameter
                    // non-translatable (as it's already).
                    $parameter->setText($matches[2][$i])->setTranslate(false);
                    $translation = str_replace($matches[0][$i], (string) $parameter, $translation);
                }
            }
        }

        return $translation;
    }

    /**
     * Returns a list of possible string variations.
     *
     * These variations can be considered as key overrides for the provided string which helps on dealing with
     * languages particularities.
     *
     * @param     string The text string to be translated.
     * @param     array  An array containing parameter objects.
     *
     * @return array A list of string variations.
     */
    protected function _getVariations($string, array $parameters = array())
    {
        $variations = array();
        $set        = array();

        // Construct a set containing non-empty (with replacement texts) parameters.
        foreach ($parameters as $parameter) {
            if ($parameter->getText()) {
                $set[] = $parameter;
            }
        }

        if (count($set)) {
            // Get the powerset of the set of parameters and construct a list of string variations from it.
            foreach ($this->_getPowerSet($set) as $subset) {
                $variation = $string;
                foreach ($subset as $parameter) {
                    $variation = str_replace($parameter->getName(true), $parameter->getText(), $variation);
                }
                $variations[] = $variation;
            }
        }

        return $variations;
    }

    /**
     * Returns the powerset of a set represented by the elements contained in an array.
     *
     * The elements are ordered from size (subsets with more elements first) for convenience.
     *
     * @param     array The powerset represented by an array of arrays containing elements from a set.
     * @param     int   The minimum amount of elements that a subset from the powerset may contain.
     *
     * @return array
     */
    protected function _getPowerSet(array $set = array(), $min_length = 1)
    {
        $elements = count($set);
        $size     = pow(2, $elements);
        $members  = array();

        for ($i = 0; $i < $size; $i++) {
            $b      = sprintf("%0" . $elements . "b", $i);
            $member = array();
            for ($j = 0; $j < $elements; $j++) {
                if ($b{$j} == '1') $member[] = $set[$j];
            }
            if (count($member) >= $min_length) {
                if (!isset($members[count($member)])) {
                    $members[count($member)] = array();
                }
                // Group members by number of elements they contain.
                $members[count($member)][] = $member;
            }
        }

        // Sort members by number of elements (key value).
        ksort($members, SORT_NUMERIC);

        $power = array();

        // We want members with greater amount of elements first.
        foreach (array_reverse($members) as $subsets) {
            $power = array_merge($power, $subsets);
        }

        return $power;
    }
}