<?php
/**
 * Elgg Webservice plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
function web_apis_init() {
	$action_base = elgg_get_plugins_path() . 'web_apis/actions';
	elgg_register_action('settings/web_apis/save', "$action_base/save.php", "admin");
	elgg_register_action('web_apis/run_tests', "$action_base/web_apis/run_tests.php", "admin");

	elgg_register_admin_menu_item('develop', 'web_apis', 'utilities');

	// register with a low priority so that we can replace all unit tests
	elgg_register_plugin_hook_handler('unit_test', 'system', 'web_apis_test');
	elgg_register_admin_menu_item('administer', 'web_apis', 'utilities');
}

//$enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_apis'));
$enabled = ['user','blog','wire','core','group','file','likes'];

foreach($enabled as $service) {
	elgg_register_library('webservice:'.$service, elgg_get_plugins_path() . 'web_apis/lib/'.$service.'.php');
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
function web_apis_test($hook, $type, $value, $params) {
	//$enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_apis'));
  $enabled = ['user','blog','wire','core','group','file','likes'];

	$base = elgg_get_plugins_path() . 'web_apis/tests';

	//foreach ($enabled as $service) {
	//	$location[] = "$base/$service.php";
	//}

	// right now just register user web services
	//$value = array();
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/user.php';
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/blog.php';
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/group.php';
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/wire.php';
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/file.php';
	$value[] = elgg_get_plugins_path() . 'web_apis/tests/core.php';
	return $value;
}


elgg_register_event_handler('init', 'system', 'web_apis_init');
