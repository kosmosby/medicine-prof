<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
interface ComLogmanActivityMessageTranslatorParameterInterface
{
    /**
     * Replacement text setter.
     *
     * @param $text The target text to be injected.
     *
     * @return ComLogmanActivityMessageTranslatorInterface this.
     */
    public function setText($text);

    /**
     * Replacement text getter.
     *
     * @return string The target text to be injected.
     */
    public function getText();

    /**
     * Switches the translatable state of the parameter.
     *
     * @param bool The parameter is made trasnlatable if true, non-translatable on false.
     *
     * @return ComLogmanActivityMessageTranslatorInterface this.
     */
    public function setTranslate($state);

    /**
     * Tells if the parameter is translatable, i.e. it's target text will be translated prior injection.
     *
     * @return bool True if translatable, false otherwise.
     */
    public function isTranslatable();

    /**
     * Name getter.
     *
     * The parameter name corresponds to the text that will be replaced (using the parameter text) in the
     * translation string.
     *
     * @param bool If true, the name will be enclosed using the parameter delimiters. Otherwise the name
     *             is provided as is.
     *
     * @return string The parameter name.
     */
    public function getName($delimiter = false);

    /**
     * Casts the parameter object into a string.
     *
     * @return string The text representation of the parameter instance.
     */
    public function __toString();

}