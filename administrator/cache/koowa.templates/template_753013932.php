
        <?php echo $this->helper('bootstrap.load'); ?>
        <?php echo $this->helper('com://admin/docman.listbox.categories', array(
            'name' => $el_name,
            'value' => $value_field,
            'deselect' => $deselect,
            'selected' => $value,
            'filter'   => array(
                'page' => $pages
            ),
            'attribs'  => array_merge($attribs, array(
                'id' => $id,
                'data-placeholder' => $this->translate('All Categories')))
        )); ?>
            <script>
                kQuery(function($){
                    $('#s2id_<?php echo $id ?>').show();
                    $('#<?php echo $id ?>_chzn').remove();
                });
            </script>
            