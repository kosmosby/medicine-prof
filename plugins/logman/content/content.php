<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */


/**
 * LOGman content plugin.
 *
 * Provides handlers for dealing with content level events from core J! extensions.
 */
class PlgLogmanContent extends ComLogmanPluginContent
{
    /**
     * @see ComLogmanPluginContext::$_contexts
     */
    protected $_contexts = array(
        'com_banners.client',
        'com_banners.banner',
        'com_categories.category',
        'com_contact.contact',
        'com_content.article',
        'com_menus.menu',
        'com_menus.item',
        'com_newsfeeds.newsfeed',
        'com_redirect.link',
        'com_users.level',
        'com_users.note',
        'com_languages.language',
        'com_weblinks.weblink');

    protected $_aliases = array('com_content.form'  => 'com_content.article',
                                'com_weblinks.form' => 'com_weblinks.weblink');

    /**
     * J!1.5 after content (article) save event handler.
     *
     * @param $article
     * @param $isNew
     */
    public function onAfterContentSave($article, $isNew)
    {
        // Same as J!2.5
        $this->onContentAfterSave('com_content.article', $article, $isNew);
    }

    /**
     * @see ComLogmanPluginContent::onContentAfterSave
     */
    public function onContentAfterSave($context, $content, $isNew)
    {
        // Map inconsistent contexts.
        if (isset($this->_aliases[$context])) {
            $context = $this->_aliases[$context];
        }

        parent::onContentAfterSave($context, $content, $isNew);
    }

    protected function _getRedirectLinkSubject($data)
    {
        return array('id' => $data->id, 'title' => 'redirect');
    }

    // TODO Added since the delete language event is inconsistently triggered on content plugins.
    protected function _getLanguagesLanguageSubject($data)
    {
        return array('id' => $data->lang_id, 'title' => $data->title);
    }

    protected function _getUsersNoteSubject($data)
    {
        return array('id' => $data->id, 'title' => $data->subject);
    }

    protected function _getNewsfeedsNewsfeedSubject($data)
    {
        return $this->_getBannersBannerSubject($data);
    }

    protected function _getContactContactSubject($data)
    {
        return $this->_getBannersBannerSubject($data);
    }

    protected function _getBannersClientSubject($data)
    {
        return $this->_getBannersBannerSubject($data);
    }

    protected function _getBannersBannerSubject($data)
    {
        return array('id' => $data->id, 'title' => $data->name);
    }

    protected function _getCategoriesCategorySubject($data)
    {
        $subject = parent::_getDefaultSubject($data);
        // Push meta data.
        $subject['metadata'] = array('extension' => $data->extension);
        return $subject;
    }
}