<?php
/**
 * @version		v2.5.0 JoomLearn Silver Edition
 * @package		lms
 * @copyright	(C) 2007 Integrated Technology & Design http://www.joomlashowroom.com
 * @license  	GNU/GPL		
 * @author		JoomlaShowroom.com
 * @author mail	info@JoomlaShowroom.com
 * @website		www.JoomlaShowroom.com
 *
*/



class rscUniqFname {



	var $fname = '' ;



	function rscUniqFName( $prefix='', $suffix='' ) {

		//$randstr = uniqid('') . "_" . session_id() ; // getmypid not allowed on lycos
        $randstr = '';

		$randfilename = $prefix . $randstr . $suffix ;


		$this->fname = $randfilename ;

	}



	function getName(){

		return $this->fname ;

	}

}



class rscImage  {



    /********************************************************

    *                      Variables                        *

    ********************************************************/

    var $image;

    var $inputfilepathname;

    var $deletesrc = 1;

    var $outputfilepathname;



    /********************************************************

    *                   public methods                      *

    ********************************************************/

    function rscImage ($fromfilename) {

        if (strstr($fromfilename,".jpg")){

            $this->image = $this->LoadJPG($fromfilename);

        }

        if (strstr($fromfilename,".png")){

            $this->image = $this->LoadPNG($fromfilename);

        }

		$this->inputfilepathname = $fromfilename ;

		$this->fontsize = 12 ;

    }



	function LoadJPG ($imgname) {

		$im = imagecreatefromjpeg ($imgname); /* Attempt to open */

		if (!$im) { /* See if it failed */

			$im  = imagecreate (300, 100); /* Create a blank image */

			$bgc = imagecolorallocate ($im, 255, 255, 255);

			$tc  = imagecolorallocate ($im, 0, 0, 0);

			imagefilledrectangle ($im, 0, 0, 300, 100, $bgc);

			/* Output an errmsg */

			imagestring ($im, 1, 5, 5, "JPG: Error loading $imgname", $tc);

		}

		return $im;

	}



	function LoadPNG ($imgname) {

		$im = imagecreatefrompng ($imgname); /* Attempt to open */

		if (!$im) { /* See if it failed */

			$im  = imagecreate (300, 100); /* Create a blank image */

			$bgc = imagecolorallocate ($im, 255, 255, 255);

			$tc  = imagecolorallocate ($im, 0, 0, 0);

			imagefilledrectangle ($im, 0, 0, 300, 100, $bgc);

			/* Output an errmsg */

			imagestring ($im, 1, 5, 5, "PNG: Error loading $imgname", $tc);

		}

		return $im;

	}



    function addText($text='Some Text',$x=10,$y=10,$fsize=20,$align="left"){

    	$tc  = imagecolorallocate ($this->image, 0, 0, 0);

		//imagestring ($this->image, 5, $x, $y, $text, $tc);



		$bbox = imagettfbbox ($fsize, 0, JPATH_SITE."/components/com_flexpaper/fonts/Vera.ttf", $text);

		$width = $bbox[2] - $bbox[0];



		switch ($align){

		  	case "center" :

				$xoff = $x - ($width / 2);

				break ;

		  	case "right" :

				$xoff = $x - $width;

				break ;

			default :

				$xoff = $x ;

		}



		imagettftext ($this->image, $fsize, 0, $xoff, $y, $tc, JPATH_SITE."/components/com_flexpaper/fonts/VeraBI.ttf", $text);



    }



    function drawGraph() {



        header("Content-Type: image/png");

		imagepng( $this->image );

        imagedestroy( $this->image );

		// Clean up file too

		//if ( $this->deletesrc ) unlink( $this->filepathname );

        exit();

    }



    function writeGraph($filename) {

		$this->outputfilepathname = $filename;



		imagepng( $this->image, $this->outputfilepathname );

        imagedestroy( $this->image );

		// Clean up file too

		//if ( $this->deletesrc ) unlink( $this->filepathname );

    }

    

    function getInfo(){

        echo "Uniqid: " . uniqid('') . "<br />";

        echo "SessionID: " . session_id() . "<br />";

        echo "mypid: " . getmypid() . "<br />";

    }



}



?>

