<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateFilterForm extends ComDefaultTemplateFilterForm
{
    /**
     * Custom write method that splits up actions into methods
     *
     * @see KTemplateFilterForm::write()
     */
    public function write(&$text)
    {
        $this->_addAction($text);
        $this->_addToken($text);
        $this->_addQueryParameters($text);

        return $this;
    }

    /**
     * Add the action if left empty
     *
     * @param string $text Template text
     */
    protected function _addAction(&$text)
    {
        if (preg_match_all('#<\s*form.*?action=""#im', $text, $matches, PREG_SET_ORDER)) {
            $view   = $this->getTemplate()->getView();
            $state  = $view->getModel()->getState();
            $action = $view->createRoute(http_build_query($state->getData($state->isUnique())));

            foreach ($matches as $match) {
                $str  = str_replace('action=""', 'action="'.$action.'"', $match[0]);
                $text = str_replace($match[0], $str, $text);
            }
        }

        return $this;
    }

    /**
     * Add the token to the form
     *
     * @param string $text Template text
     */
    protected function _addToken(&$text)
    {
        // POST : Add token
        if (!empty($this->_token_value)) {
            $text    = preg_replace('/(<form.*method="post".*>)/i',
                '\1'.PHP_EOL.'<input type="hidden" name="'.$this->_token_name.'" value="'.$this->_token_value.'" />',
                $text
            );
        }

        // GET : Add token to .-koowa-grid forms
        if (!empty($this->_token_value)) {
            $text    = preg_replace('#(<\s*?form\s+?.*?class=(?:\'|")[^\'"]*?-koowa-grid.*?(?:\'|").*?)>#im',
                '\1 data-token-name="'.$this->_token_name.'" data-token-value="'.$this->_token_value.'">',
                $text
            );
        }

        return $this;
    }

    /**
     * Add query parameters as hidden fields to the GET forms
     *
     * @param string $text Template text
     */
    protected function _addQueryParameters(&$text)
    {
        $matches = array();
        if (preg_match_all('#<form.*action=".*\?(.*)".*method="get".*>(.*)</form>#isU', $text, $matches)) {
            foreach ($matches[1] as $key => $query) {
                parse_str(str_replace('&amp;', '&', $query), $query);

                $input = '';
                foreach ($query as $name => $value) {
                    if (strpos($matches[2][$key], 'name="'.$name.'"') !== false) {
                        continue;
                    }

                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            $input .= PHP_EOL.'<input type="hidden" name="'.$name.'['.$k.']" value="'.$v.'" />';
                        }
                    } else $input .= PHP_EOL.'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
                }

                $text = str_replace($matches[2][$key], $input.$matches[2][$key], $text);
            }
        }

        return $this;
    }
}
