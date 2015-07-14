<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperPaginator extends KTemplateHelperPaginator
{
    public function sort_documents($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'options'  => array(),
            'name'     => 'sort-documents',
            'attribs'  => array(
                'onchange' => 'window.location = this.value;'
            )
        ));

        $options = array_merge(array(
            $this->translate('Title Alphabetical') => array('document_sort' => 'title', 'document_direction' => 'asc'),
            $this->translate('Title Reverse Alphabetical') => array('document_sort' => 'title', 'document_direction' => 'desc'),
            $this->translate('Most Recent First') => array('document_sort' => 'created_on', 'document_direction' => 'desc'),
            $this->translate('Oldest First') => array('document_sort' => 'created_on', 'document_direction' => 'asc')
        ), KConfig::unbox($config->options));

        $html     = '';
        $selected = null;
        $view     = $this->getTemplate()->getView();
        $state    = $view->getModel()->getState();
        $default  = $state->getData($state->isUnique());
        $current  = array(
            'document_sort' => $state->document_sort,
            'document_direction' => $state->document_direction,
        );

        $select = array();
        foreach($options as $text => $value)
        {
            $data = array_merge($value, $default);
            $route = $view->createRoute(http_build_query($data));

            if ($selected === null && $value === $current) {
                $selected = $route;
            }

            $select[] = $this->option(array('text' => $text, 'value' => $route));
        }

        $html .= $this->optionlist(array(
            'options' => $select,
            'name' => $config->name,
            'attribs' => $config->attribs,
            'selected' => $selected
        ));

        return $html;
    }

    /**
     * Render item pagination
     *
     * @param   array   An optional array with configuration options
     * @return string Html
     * @see     http://developer.yahoo.com/ypatterns/navigation/pagination/
     */
    public function pagination($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'total'      => 0,
            'display'    => 0,
            'offset'     => 0,
            'limit'      => 0,
            'show_limit' => true,
            'show_count' => false,
            'show_pages' => true,
            'attribs'    => array('onchange' => 'this.form.submit();')
        ));

        $this->_initialize($config);

        if ($config->count === 1) {
            $config->show_pages = false;
        }

        $html = '<div class="pagination">';
        if ($config->show_limit) {
            $html .= '<div class="limit">'.$this->limit($config).'</div>';
        }

        if ($config->show_pages) {
            $html .= '<ul class="pagination-list">';
            $html .=  $this->_pages($this->_items($config));
            $html .= '</ul>';
        }
        if ($config->show_count) {
            $html .= '<div class="limit">';
            $html .= sprintf($this->translate('JLIB_HTML_PAGE_CURRENT_OF_TOTAL'), $config->current, $config->count);
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a list of pages links
     *
     * This function is overriddes the default behavior to render the links in the khepri template
     * backend style.
     *
     * @param   araay   An array of page data
     * @return string Html
     */
    protected function _pages($pages)
    {
        $html  = $pages['previous']->active ? '<li>'.$this->_link($pages['previous'], '&larr;').'</li>' : '';

        /* @TODO should be a better way to do this than iterating the array to find the current page */
        $current = 0;
        foreach ($pages['pages'] as $i => $page) {
            if($page->current) $current = $i;
        }

        /* @TODO move this into the $config initialize */
        $padding = 2;

        $total = count($pages['pages']);
        $hellip = false;
        foreach ($pages['pages'] as $i => $page) {
            $in_range = $i > ($current - $padding) && $i < ($current + $padding);

            if ($i < $padding || $in_range || $i >= ($total - $padding)) {
                $html .= '<li class="'.($page->active && !$page->current ? '' : 'active').'">';
                $html .= $this->_link($page, $page->page);

                $hellip = false;
            } else {
                if($hellip == true) continue;

                $html .= '<li class="disabled">';
                $html .= '<a href="#">&hellip;</a>';

                $hellip = true;
            }

            $html .= '</li>';
        }

        $html  .= $pages['next']->active ? '<li>'.$this->_link($pages['next'], '&rarr;').'</li>' : '';

        return $html;
    }

    protected function _link($page, $title)
    {
        $url   = clone KRequest::url();
        $query = $url->getQuery(true);

        //For compatibility with Joomla use limitstart instead of offset
        $query['limit']      = $page->limit;
        $query['limitstart'] = $page->offset;

        $url->setQuery($query);

        if ($page->active && !$page->current) {
            $html = '<a href="'.$url.'">'.$this->translate($title).'</a>';
        } else {
            $html = '<a href="#">'.$this->translate($title).'</a>';
        }

        return $html;
    }
}
