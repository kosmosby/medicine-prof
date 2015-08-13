<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Vcard View
 *
 * Complies to version 2.1 of the vCard specification
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\View
 * @see     http://www.imc.org/pdi/
 * @see     http://en.wikipedia.org/wiki/VCard
 */
class KViewVcard extends KViewAbstract
{
    /**
     * The Vcard properties
     *
     * @var array
     */
    protected $_properties;

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'mimetype' => 'text/x-vcard; version=2.1',
        ));

        parent::_initialize($config);
    }

    /**
     * Return the views output
     *
     * @param KViewContext	$context A view context object
     * @return string  The output of the view
     */
    protected function _actionRender(KViewContext $context)
    {
        //Set the filename
        $filename = $this->getObject('lib:filter.filename')->sanitize($this->_properties['FN']);
        $this->filename = $filename.'.vcf';

        //Render the vcard
        $data   = 'BEGIN:VCARD';
        $data   .= "\r\n";
        $data   .= 'VERSION:2.1';
        $data   .= "\r\n";

        foreach( $this->_properties as $key => $value )
        {
            $data   .= "$key:$value";
            $data   .= "\r\n";
        }

        $data   .= 'REV:'. date( 'Y-m-d' ) .'T'. date( 'H:i:s' ). 'Z';
        $data   .= "\r\n";
        $data   .= 'END:VCARD';
        $data   .= "\r\n";

        $this->setContent($data);

        parent::_actionRender($context);
    }

    /**
     * A structured representation of the name of the person, place or thing
     *
     * @param   string  $family Family name
     * @param   string  $first  First name
     * @param   string  $additional Additional name
     * @param   string  $prefix Prefix
     * @param   string  $suffix Suffix
     * @return  KViewVCard
     */
    public function setName( $family = '', $first = '', $additional = '', $prefix = '', $suffix = '' )
    {
        $this->_properties["N"]     = "$family;$first;$additional;$prefix;$suffix";
        $this->setFormattedName( trim( "$prefix $first $additional $family $suffix" ) );
        return $this;
    }

    /**
     * The formatted name string
     *
     * @param   string  $name Name
     * @return  KViewVCard
     */
    public function setFormattedName($name)
    {
        $this->_properties['FN'] = $this->_quoted_printable_encode($name);
        return $this;
    }

    /**
     * The name and optionally the unit(s) of the organization
     *
     * This property is based on the X.520 Organization Name attribute and the X.520 Organization Unit attribute
     *
     * @param   string  $org Organisation
     * @return  KViewVCard
     */
    public function setOrg( $org )
    {
        $this->_properties['ORG'] =  trim( $org );
        return $this;
    }

    /**
     * Specifies the job title, functional position or function of the individual
     * within an organization (V. P. Research and Development)
     *
     * @param   string  $title Title
     * @return  KViewVCard
     */
    public function setTitle( $title )
    {
        $this->_properties['TITLE'] = trim( $title );
        return $this;
    }

    /**
     * The role, occupation, or business category within an organization (eg. Executive)
     *
     * @param   string  $role Role
     * @return  KViewVCard
     */
    public function setRole( $role )
    {
        $this->_properties['ROLE'] = trim( $role );
        return $this;
    }


    /**
     * The canonical number string for a telephone number for telephony communication
     *
     * @param   string $number Phone number
     * @param   string $type Type [PREF|WORK|HOME|VOICE|FAX|MSG|CELL|PAGER|BBS|CAR|MODEM|ISDN|VIDEO] or a combination, e.g. "PREF;WORK;VOICE"
     * @return   KViewVCard
     */
    public function setPhoneNumber($number, $type = 'PREF;WORK;VOICE')
    {
        $this->_properties['TEL;'.$type] = $number;
        return $this;
    }

    /**
     * A structured representation of the physical delivery address
     *
     * @param   string $postoffice Postoffice
     * @param   string $extended Extended
     * @param   string $street Street
     * @param   string $city City
     * @param   string $region Region
     * @param   string $zip Zip
     * @param   string $country Country
     * @param   string $type Type [DOM|INTL|POSTAL|PARCEL|HOME|WORK] or a combination e.g. "WORK;PARCEL;POSTAL"
     * @return  KViewVCard
     */
    public function setAddress( $postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'WORK;POSTAL' )
    {
        $data = $this->_encode( $postoffice );
        $data .= ';' . $this->_encode( $extended );
        $data .= ';' . $this->_encode( $street );
        $data .= ';' . $this->_encode( $city );
        $data .= ';' . $this->_encode( $region);
        $data .= ';' . $this->_encode( $zip );
        $data .= ';' . $this->_encode( $country );

        $this->_properties['ADR;'.$type] = $data;
        return $this;
    }

    /**
     * Addressing label for physical delivery to the person/object
     *
     * @param   string $postoffice Postoffice
     * @param   string $extended Extended
     * @param   string $street Street
     * @param   string $city City
     * @param   string $region Region
     * @param   string $zip Zip
     * @param   string $country Country
     * @param   string $type Type [DOM|INTL|POSTAL|PARCEL|HOME|WORK] or a combination e.g. "WORK;PARCEL;POSTAL"
     * @return  KViewVCard
     */
    public function setLabel($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'WORK;POSTAL')
    {
        $label = '';
        if ($postoffice != '') {
            $label.= $postoffice;
            $label.= "\r\n";
        }

        if ($extended != '') {
            $label.= $extended;
            $label.= "\r\n";
        }

        if ($street != '') {
            $label.= $street;
            $label.= "\r\n";
        }

        if ($zip != '') {
            $label.= $zip .' ';
        }

        if ($city != '') {
            $label.= $city;
            $label.= "\r\n";
        }

        if ($region != '') {
            $label.= $region;
            $label.= "\r\n";
        }

        if ($country != '') {
            $label.= $country;
            $label.= "\r\n";
        }

        $this->_properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] = $this->_quoted_printable_encode($label);
        return $this;
    }

    /**
     * The address for electronic mail communication
     *
     * @param   string $address Email
     * @return  KViewVCard
     */
    public function setEmail($address)
    {
        $this->_properties['EMAIL;PREF;INTERNET'] = $address;
        return $this;
    }

    /**
     * An URL is a representation of an Internet location that can be used to obtain real-time information
     *
     * @param   string $url Url
     * @param   string $type Type [WORK|HOME]
     * @return  KViewVCard
     */
    public function setVcardURL($url, $type = 'WORK')
    {
        $this->_properties['URL;'.$type] = $url;
        return $this;
    }

    /**
     * An image or photograph of the individual associated with the vCard
     *
     * @param   string $photo Photo data to be encoded
     * @param   string $type Type [GIF|JPEG]
     * @return  KViewVCard
     */
    public function setPhoto($photo, $type = 'JPEG')
    {
        $this->_properties["PHOTO;TYPE=$type;ENCODING=BASE64"] = base64_encode($photo);
        return $this;
    }

    /**
     * Date of birth of the individual
     *
     * @param   string $date Date YYYY-MM-DD
     * @return  KViewVCard
     */
    public function setBirthday($date)
    {
        $this->_properties['BDAY'] = $date;
        return $this;
    }


    /**
     * Specifies supplemental information or a comment that is associated with the vCard
     *
     * @param   string $note Note
     * @return  KViewVCard
     */
    public function setNote($note)
    {
        $this->_properties['NOTE;ENCODING=QUOTED-PRINTABLE'] = $this->_quoted_printable_encode($note);
        return $this;
    }

    /**
     * Force the route to fully qualified and not escaped by default
     *
     * @param   string|array    $route   The query string used to create the route
     * @param   boolean $fqr    If TRUE create a fully qualified route. Default TRUE.
     * @param   boolean $escape If TRUE escapes the route for xml compliance. Default FALSE.
     * @return  KHttpUrl        The route
     */
    public function getRoute($route = '', $fqr = true, $escape = false)
    {
        return parent::getRoute($route, $fqr, $escape);
    }

    /**
     * Encode
     *
     * @param   string  String to encode
     * @return  string  Encoded string
     */
    protected function _encode($string)
    {
        $result = $this->_quoted_printable_encode($string);
        $result = str_replace(';',"\;",$string);

        return $result;
    }

    /**
     * Quote for printable output
     *
     * @param   string  $input Input
     * @param   int     $line_max Max line length
     * @return  string
     */
    protected function _quoted_printable_encode($input, $line_max = 76)
    {
        $hex        = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
        $lines      = preg_split("/(?:\r\n|\r|\n)/", $input);
        $eol        = "\r\n";
        $linebreak  = '=0D=0A';
        $escape     = '=';
        $output     = '';

        for ($j = 0; $j < count($lines); $j++)
        {
            $line       = $lines[$j];
            $linlen     = strlen($line);
            $newline    = '';

            for($i = 0; $i < $linlen; $i++)
            {
                $c      = substr($line, $i, 1);
                $dec    = ord($c);

                if ( ($dec == 32) && ($i == ($linlen - 1)) ) { // convert space at eol only
                    $c = '=20';
                }
                elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
                    $h2 = floor($dec/16);
                    $h1 = floor($dec%16);
                    $c  = $escape.$hex["$h2"] . $hex["$h1"];
                }

                if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
                    $output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
                    $newline = "    ";
                }

                $newline .= $c;
            }

            $output .= $newline;
            if ($j<count($lines)-1) {
                $output .= $linebreak;
            }
        }

        return trim($output);
    }
}
