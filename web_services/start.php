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
	elgg_register_action('web_services/run_tests', "$action_base/web_services/run_tests.php", "admin");

	elgg_register_admin_menu_item('develop', 'web_services', 'utilities');

	// register with a low priority so that we can replace all unit tests
	elgg_register_plugin_hook_handler('unit_test', 'system', 'web_services_test');
	elgg_register_admin_menu_item('administer', 'web_services', 'utilities');
}

$enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services'));

foreach($enabled as $service) {
	elgg_register_library('webservice:'.$service, elgg_get_plugins_path() . 'web_services/lib/'.$service.'.php');
	elgg_load_library('webservice:'.$service);
}

/**
 * Unit test registration for web services
 *
 * Returns an array of web services unit test locations. It overrides the rest
 * of the tests.
 *
 * @param type  $hook
 * @param type  $type
 * @return array
 */
function web_services_test($hook, $type, $value, $params) {
	$enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services'));

	$base = elgg_get_plugins_path() . 'web_services/tests';

	//foreach ($enabled as $service) {
	//	$location[] = "$base/$service.php";
	//}

	// right now just register user web services
	//$value = array();
	$value[] = elgg_get_plugins_path() . 'web_services/tests/user.php';
	$value[] = elgg_get_plugins_path() . 'web_services/tests/blog.php';
	$value[] = elgg_get_plugins_path() . 'web_services/tests/group.php';
	$value[] = elgg_get_plugins_path() . 'web_services/tests/wire.php';
	$value[] = elgg_get_plugins_path() . 'web_services/tests/file.php';
	$value[] = elgg_get_plugins_path() . 'web_services/tests/core.php';
	return $value;
}


elgg_register_event_handler('init', 'system', 'web_services_init');
