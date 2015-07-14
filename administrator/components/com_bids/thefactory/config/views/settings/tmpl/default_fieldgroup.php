<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $fieldsets=JTheFactoryConfigHelper::getFieldsets($this->formxml,$this->currentgroup->name);
    //fieldsets are XML Elements , not JFieldsets
?>
<table class="paramlist admintable" width="100%">
<tr>
    <td valign="top" width="50%">
    <?php
        $i=1;
        foreach($fieldsets as $fieldset){
            echo "<div class='width-100 fltlft'><fieldset class='adminform'>
	                <legend>".$fieldset->attributes()->label."</legend>";
	        $this->currentfieldset=$fieldset;
            echo $this->loadTemplate("fieldset");
	        echo "</fieldset></div>";
            if ($i == ceil(count($fieldsets)/2) )
                echo "</td><td valign='top'>";
            $i++;
        }
    ?>
    </td>
</tr>
</table>

