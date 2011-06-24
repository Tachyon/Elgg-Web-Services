<?php
/**
 * Web Services plugin settings
 */

// set default value
if (isset($vars['entity']->enabled_webservices)) {
	echo "<h1>".$vars['entity']->enabled_webservices."</h1>";
	echo elgg_get_plugin_setting('enabled_webservices', 'web_services');
}

echo '<div>';
echo elgg_get_plugin_setting('enabled_webservices', 'web_services');
echo elgg_echo('web_services:selectfeatures').var_dump($vars);
echo ' ';
echo elgg_view("input/checkboxes", array(
			'internalname' => 'params[enabled_webservices]',
			'value' => array($vars['entity']->enabled_webservices),
			'options' => array("User" => 'user', "Blog" => 'blog', "Wire" => 'wire', "Core" => 'core')
			));

echo '</div>';


