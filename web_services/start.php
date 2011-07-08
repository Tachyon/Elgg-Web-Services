<?php
/**
 * Elgg Webservice plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
function web_services_init() {
	$action_base = elgg_get_plugins_path() . 'web_services/actions';
	elgg_register_action('settings/web_services/save', "$action_base/save.php", "admin");
}

$enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services'));

foreach($enabled as $service) {
	elgg_register_library('webservice:'.$service, elgg_get_plugins_path() . 'web_services/lib/'.$service.'.php');
	elgg_load_library('webservice:'.$service);
}


elgg_register_event_handler('init', 'system', 'web_services_init');
