<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman contact plugin.
 *
 * Provides handlers for dealing with contact events.
 */
class PlgLogmanContact extends ComLogmanPluginAbstract
{
    public function onSubmitContact($contact, $data)
    {
        if (version_compare(JVERSION, '1.6', '<')) {
            $form_name = @$data['name'];
            $form_email = @$data['email'];
        } else {
            $form_name = @$data['contact_name'];
            $form_email = @$data['contact_email'];
        }

        $activity = $this->getActivity(array(
            'subject' => array(
                'component' => 'contact',
                'resource'  => 'contact',
                'title'     => $contact->name,
                'id'        => $contact->id,
                'metadata'  => array(
                    'sender' => array(
                        'name'  => $form_name,
                        'email' => $form_email))),
            'result'  => 'contacted',
            'action'  => 'contact'));

        $this->save($activity);
    }
}