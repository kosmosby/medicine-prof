<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Overridden to not lock resources if the user does not have edit permission on them
 */
class ComDocmanControllerBehaviorEditable extends KControllerBehaviorEditable
{
    public function lockResource(KCommandContext $context)
    {
        if (!$this->canEdit()) {
            return;
        }

        return parent::lockResource($context);
    }

    public function unlockResource(KCommandContext $context)
    {
        if (!$this->canEdit()) {
            return;
        }

        return parent::unlockResource($context);
    }

    /**
     * Saves the current row and redirects to a new edit form
     *
     * @param KCommandContext $context
     *
     * @return KDatabaseRowInterface A row object containing the saved data
     */
    protected function _actionSave2new(KCommandContext $context)
    {
        // Cache and lock the referrer since _ActionSave would unset it
        $referrer = $this->getReferrer();
        $this->lockReferrer();

        $result = $this->save($context);

        // Re-set the referrer
        KRequest::set('cookie.referrer', (string) $referrer);

        $identifier = $this->getMixer()->getIdentifier();
        $view       = KInflector::singularize($identifier->name);
        $url        = sprintf('index.php?option=com_%s&view=%s', $identifier->package, $view);

        $this->setRedirect($this->getService('koowa:http.url',array('url' => $url)));

        return $result;
    }
}
