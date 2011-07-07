<?php
/**
 * Web Services plugin settings
 */


echo '<div>';
echo ' ';
echo elgg_view("input/checkboxes", array(
			'name' => 'params[enabled_webservices]',
			'value' => unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services')),
			'options' => array("User" => 'user', "Blog" => 'blog', "Wire" => 'wire', "Core" => 'core', "Group" => 'group')
			));

echo '</div>';


