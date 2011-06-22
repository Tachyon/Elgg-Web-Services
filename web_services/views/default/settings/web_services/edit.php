<?php
/**
 * Web Services plugin settings
 */

// set default value


echo '<div>';
echo elgg_echo('web_services:selectfeatures');
echo ' ';
echo elgg_view("input/checkboxes",
  array('internalname'=>'params[my_var]',
        'value'=>array($vars['entity']->my_var),
        'options'=>array("Label for My Var"=>'my_var')));

echo '</div>';
