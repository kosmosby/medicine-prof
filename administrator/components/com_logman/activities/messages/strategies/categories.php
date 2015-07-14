<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyCategories extends ComLogmanActivityMessageStrategyDefault
{
    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);
        $config->append(array('table' => 'categories', 'identity_column' => 'id'));
        return parent::_resourceExists($config);
    }

    public function getText(KConfig $config)
    {
        $subject = array();

        if ($config->activity->metadata) {
            $subject[] = '%type%';
        }

        $subject[] = '%resource% %title%';

        $config->append(array('subject' => implode(' ', $subject)));

        return parent::getText($config);
    }

    protected function _getType(KConfig $config)
    {
        $extension = $config->activity->metadata->extension;

        if (strpos($extension, '.') === false) {
            // Guess context based on provided extension.
            // J!2.5 only provides the extension name while 3.0 passes (ON SOME CASES) a context.
            switch ($extension) {
                case 'com_users':
                    $context = 'com_users.notes';
                    break;
                case 'com_content':
                    $context = 'com_content.articles';
                    break;
                case 'com_banners':
                    $context = 'com_banners.banners';
                    break;
                case 'com_contact':
                    $context = 'com_contact.contacts';
                    break;
                case 'com_newsfeeds':
                    $context = 'com_newsfeeds.newsfeeds';
                    break;
                case 'com_weblinks':
                    $context = 'com_weblinks.weblinks';
                    break;
                default:
                    $context = null;
                    break;
            }
        } else {
            $context = $extension;
        }

        // Translate context into readable type.
        switch ($context) {
            case 'com_users.notes':
                $text = 'user notes';
                break;
            case 'com_content.articles':
                $text = 'articles';
                break;
            case 'com_banners.banners':
                $text = 'banners';
                break;
            case 'com_contact.contacts':
                $text = 'contacts';
                break;
            case 'com_newsfeeds.newsfeeds':
                $text = 'newsfeeds';
                break;
            case 'com_weblinks.weblinks':
                $text = 'weblinks';
                break;
            default:
                $text = '';
                break;
        }

        $config->append(array('text' => $text, 'translate' => true));

        return $this->_getParameter($config);
    }

    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        $url = parent::_getResourceUrl($activity);

        if ($metadata = $activity->metadata) {
            // Append extension info.
            $url .= '&extension=' . $metadata->extension;
        }

        return $url;
    }
}