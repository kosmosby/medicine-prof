<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperGrid extends ComDefaultTemplateHelperGrid
{
    /**
     * Render an search header
     *
     * @param 	array 	An optional array with configuration options
     * @return string Html
     */
    public function search($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'search'      => null,
            'placeholder' => $this->translate('Find by title or description&hellip;')
        ));

        $html  = '<label for="search"><i class="icon-search"></i></label>';
        $html .= '<input type="search" name="search" id="search" placeholder="'.$config->placeholder.'" value="'.$this->getTemplate()->getView()->escape($config->search).'" />';
        $html .= '<button>'.$this->translate('Go').'</button>';
        $html .= '<button onclick="document.getElementById(\'search\').value=\'\';this.form.submit();">'.$this->translate('Reset').'</button>';

        return $html;
    }

    public function checkbox($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'row' => null,
            'permissions' => true,
            'attribs' => array()
        ));

        if ($config->row->isLockable() && $config->row->locked()) {
            $html = '<span class="editlinktip hasTip" title="'.$config->row->lockMessage() .'">
            <img src="media://lib_koowa/images/locked.png"/>
            </span>';
        } else {
            $column = $config->row->getIdentityColumn();
            $value  = $config->row->{$column};

            $permissions = '';
            if ($config->permissions === true && $config->row->isAclable()) {
                $data = $config->row->getPermissions()->toArray();
                $permissions = sprintf('data-permissions="%s"', htmlentities(json_encode($data)));
            }

            $attribs = KHelperArray::toString($config->attribs);

            $html = '<input type="checkbox" class="-koowa-grid-checkbox" name="%s[]" value="%s" %s %s />';
            $html = sprintf($html, $column, $value, $permissions, $attribs);
        }

        return $html;
    }

    /**
     * Render an state field
     *
     * @param 	array 	An optional array with configuration options
     * @return string Html
     */
    public function state($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'row'  		=> null,
            'field'		=> 'enabled',
            'clickable'  => true
        ))->append(array(
            'data'		=> array($config->field => $config->row->{$config->field})
        ));

        $row = $config->row;

        // Enabled, but pending
        if ($row->enabled && !$row->published && !$row->expired && $row->publish_on !== null) {
            $access = 0;
            $group  = $this->translate('Pending');
            $date   = $this->getTemplate()->renderHelper('date.humanize', array('date' => $row->publish_on));
            $tip    = $this->translate('Will be published %date%, click to unpublish item', array(
                          '%date%' => $date));
            $color  = '#c09853';
        }
        // Enabled, but expired
        else if ($row->enabled && !$row->published && $row->expired && $row->unpublish_on !== null) {
            $access = 0;
            $group  = $this->translate('Expired');
            $date   = $this->getTemplate()->renderHelper('date.humanize', array('date' => $row->unpublish_on));
            $tip    = $this->translate('Expired %on%, click to unpublish item', array('%on%' => $date));
            $color  = '#3a87ad';
        } elseif (!$row->enabled) {
            $access = 1;
            $group  = $this->translate('Unpublished');
            $tip    = $this->translate('Publish item');
            $color  = '#b94a48';
        } else {
            $access = 0;
            $group  = $this->translate('Published');
            $tip    = $this->translate('Unpublish item');
            $color  = '#468847';
        }

        $config->data->{$config->field} = $access;
        $data = str_replace('"', '&quot;', $config->data);

        $html = '<span style="cursor: pointer;color:'.$color.'" data-action="edit" data-data="'.$data.'" title="'.$tip.'">'.$group.'</span>';

        return $html;
    }

    public function order($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'row'  		=> null,
            'total'		=> null,
            'field'		=> 'ordering',
            'data'		=> array('order' => 0)
        ));

        if (version_compare(JVERSION, '1.6', '>=')) {
            $html = '';

            $config->data->order = -1;
            $updata   = str_replace('"', '&quot;', $config->data);

            $config->data->order = +1;
            $downdata = str_replace('"', '&quot;', $config->data);

            if ($config->sort === 'custom')
            {
                $tmpl = '
                <span>
                    <a class="jgrid" href="#" title="%s" data-action="edit" data-data="%s">
                        <span class="state %s" style="width: 12px; height: 12px; background-repeat: no-repeat"><span class="text">%s</span></span>
                    </a>
                </span>
                ';
            }
            else
            {
                $tmpl = '
                <span class="jgrid hastip" title="'.$this->translate('Please order by this column first by clicking the column title').'">
                    <span class="state %3$s" style="width: 12px; height: 12px; background-repeat: no-repeat; background-position: 0 -12px;">
                        <span class="text">%4$s</span>
                    </span>
                </span>';
            }


            if ($config->row->{$config->field} > 1) {
                $icon = version_compare(JVERSION, '3.0', '>=') ? '<i class="icon-arrow-up"></i>' : $this->translate('Move up');
                $html .= sprintf($tmpl, $this->translate('Move up'), $updata, 'uparrow', $icon);
            }

            $html .= $config->row->{$config->field};

            if ($config->row->{$config->field} != $config->total) {
                $icon = version_compare(JVERSION, '3.0', '>=') ? '<i class="icon-arrow-down"></i>' : $this->translate('Move down');
                $html .= sprintf($tmpl, $this->translate('Move down'), $downdata, 'downarrow', $icon);
            }
        } else {
            $html = parent::order($config);
        }

        return $html;
    }
}
