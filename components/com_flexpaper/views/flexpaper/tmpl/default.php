<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

    $this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
    echo $this->loadTemplate('topmenu');


$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'flexpapers'.DS.'tmpl' );
echo $this->loadTemplate('menu');

?>
<div class="grid_18 egitim_sag" style="position: relative; height: 520px; padding-top: 15px;">

    <div><?php echo $this->title;?> > <?php echo $this->activeCourse->title;?> > <font size="2" color="#B02B2C"> ( <?php echo $this->activeCourse->name;?> ) </font></div>

    <div id="documentViewer" class="flexpaper_viewer" style="position:absolute;left:5px;top:40px;width:680px;height:480px;"></div>

    <script type="text/javascript">
            jQuery('#documentViewer').FlexPaperViewer(
                    { config : {
                        SwfFile : escape('<?php echo $this->path;?>docs/swf/<?php echo $this->item->swffile;?>'),
                        PDFFile : '<?php echo $this->path;?>docs/pdf/<?php echo $this->item->pdffile;?>',

                        key : "$fa18de124c0bb7f1153",
                        Scale : 0.6,
                        ZoomTransition : 'easeOut',
                        ZoomTime : 0.5,
                        ZoomInterval : 0.1,
                        FitPageOnLoad : true,
                        FitWidthOnLoad : false,
                        FullScreenAsMaxWindow : false,
                        ProgressiveLoading : false,
                        MinZoomSize : 0.2,
                        MaxZoomSize : 5,
                        SearchMatchAll : true,
                        InitViewMode : '',
                        RenderingOrder : 'flash,html5',
                        StartAtPage : '',

                        ViewModeToolsVisible : true,
                        ZoomToolsVisible : true,
                        NavToolsVisible : true,
                        CursorToolsVisible : true,
                        SearchToolsVisible : true,
                        WMode : 'transparent',
                        localeChain: 'tr_TR'
                    }}
            );
    </script>
</div>


