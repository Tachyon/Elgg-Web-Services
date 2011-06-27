<?php
/**
 * Saves plugin settings.
 *
 * @uses array $_REQUEST['params']    Serialized array of options
 * @uses int   $_REQUEST['plugin_id'] The ID of the plugin
 *
 * @package Elgg.Web_services
 */
$params = get_input('params');
$plugin_id = get_input('plugin_id');
$plugin = elgg_get_plugin_from_id($plugin_id);

if (!($plugin instanceof ElggPlugin)) {
	register_error(elgg_echo('plugins:settings:save:fail', array($plugin_id)));
	forward(REFERER);
}

$plugin_name = $plugin->getManifest()->getName();

$result = false;

// allow a plugin to override the save action for their settings

foreach ($params as $k => $v) {
	$result = $plugin->setSetting($k, serialize($v));
	if (!$result) {
		register_error(elgg_echo('plugins:settings:save:fail', array($plugin_name)));
		forward(REFERER);
		exit;
	}
}


system_message(elgg_echo('plugins:settings:save:ok', array($plugin_name)));
forward(REFERER);
