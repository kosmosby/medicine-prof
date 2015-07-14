<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanControllerBehaviorTrackable extends KControllerBehaviorAbstract
{
    protected $_token;

    protected $_entity;
    
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);
        
        $this->setToken($config->token);
    }
    
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'token' => 'c7326a714a1275378a6d4608f547737b'
        ));
        
        parent::_initialize($config);
    }
    
    public function setToken($token)
    {
        $this->_token = $token;
        
        return $this;
    }
    
    public function getToken()
    {
        return $this->_token;
    }

    protected function _afterAdd(KControllerContextInterface $context)
    {
        if ($context->result instanceof KModelEntityInterface) {
            $this->_entity = $context->result;
        }
    }
    
	protected function _afterRender(KControllerContextInterface $context)
	{
		$event = $this->getModel()->getState()->event;

		if ($event)
		{
            if ($this->getView()->getName() == 'extension') {
                $extension = !empty($this->_entity) ? $this->_entity : $this->getModel()->fetch();
            }
            else
            {
                // it's an uninstall event and the info is coming from the URL
                $info = $this->getRequest()->query->info;
                $extension = json_decode(base64_decode($info));
            }

            if ($extension) {
                $context->result .= $this->track($event, $extension);
            }
		}
	}

	protected function _afterDelete(KControllerContextInterface $context)
	{
		if ($context->status == KHttpResponse::NO_CONTENT)
		{
			$extension = $context->result;

			$url = $this->getObject('request')->getReferrer();

            if ($url)
            {
                $info['name']    = $extension->name;
                $info['version'] = $extension->version;
                $info['joomlatools_user_id'] = $extension->joomlatools_user_id;

                $query            = $url->getQuery(true);
                $query['event']   = 'uninstall';
                $query['info'] = base64_encode(json_encode($info));

                $url->setQuery($query);
                $context->response->setRedirect($url);
            }
		}
	}

	public function getTrackingInfo($extension)
	{
		$version = new JVersion();

		$server = @php_uname('s').' '.@php_uname('r');

		// php_uname is disabled
		if (empty($server)) {
			$server = 'Unknown';
		}

		$info = array(
			'Product' 			=> $extension->name,
			'Version' 			=> $extension->version,
			'Joomla' 			=> $this->_extractVersionInfo($version->getShortVersion()),
			'Koowa'	 			=> class_exists('Koowa') && method_exists('Koowa', 'getInstance') ? Koowa::getInstance()->getVersion() : 0,
			'PHP' 				=> $this->_extractVersionInfo(phpversion()),
			'Database' 			=> $this->_extractVersionInfo(JFactory::getDBO()->getVersion()),
			'Web Server' 		=> @$_SERVER['SERVER_SOFTWARE'],
			'Web Server OS' 	=> $server,
			'Joomla Language' 	=> JFactory::getLanguage()->getName(),
            'Identifier'        => $extension->joomlatools_user_id
		);

		return $info;
	}

	public function track($event, $extension)
	{
		$info = json_encode($this->getTrackingInfo($extension));

        $domain = $this->getObject('request')->getUrl()->toString(KHttpUrl::HOST);

        $statements = array(
            "mixpanel.init('".$this->_token."')",
            "mixpanel.name_tag('".$domain."')",
            "mixpanel.track('".$event."', ".$info.")"
        );

        if(!empty($extension->joomlatools_user_id)) {
            array_splice($statements, 2, 0, array("mixpanel.identify('".$extension->joomlatools_user_id."')"));
        }

        if (!empty($extension->joomlatools_user_id) && !empty($extension->name))
        {
            if ($event == 'uninstall' || $extension->user_id_saved)
            {
                $count = $event == 'uninstall' ? -1 : 1;
                $command = "mixpanel.people.increment('".$extension->name."', ".$count.")";

                array_splice($statements, 2, 0, array($command));
            }
        }

		$return = "<script type=\"text/javascript\">(function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement(\"script\");b.type=\"text/javascript\";b.async=!0;b.src=(\"https:\"===c.location.protocol?\"https:\":\"http:\")+'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';d=c.getElementsByTagName(\"script\")[0];d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){var c=b.split(\".\");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(Array.prototype.slice.call(arguments,0)))}}var g=a;\"undefined\"!==typeof f?g=a[f]=[]:f=\"mixpanel\";g.people=g.people||[];h=['disable','track','track_pageview','track_links','track_forms','register','register_once','unregister','identify','alias','name_tag','set_config','people.set','people.set_once','people.increment','people.track_charge','people.append'];for(e=0;e<h.length;e++)d(g,h[e]);a._i.push([b,c,f])};a.__SV=1.2;})(document,window.mixpanel||[]);</script>"
				. "<script type=\"text/javascript\">".implode('; ', $statements)."</script>";

		return $return;
	}

	protected function _extractVersionInfo($version)
	{
		return substr($version, 0, strpos($version, '.', strpos($version, '.')+1));
	}
}