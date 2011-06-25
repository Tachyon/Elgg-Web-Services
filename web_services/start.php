<?php
/**
 * Elgg Webservice plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
function blogapi_init() {
	
	//$time = elgg_get_plugin_setting('enabled_webservices', 'web_services');
}

elgg_register_library('webservice:user', elgg_get_plugins_path() . 'web_services/lib/user.php');
elgg_load_library('webservice:user');
elgg_register_library('webservice:blog', elgg_get_plugins_path() . 'web_services/lib/blog.php');
elgg_load_library('webservice:blog');
elgg_register_library('webservice:core', elgg_get_plugins_path() . 'web_services/lib/core.php');
elgg_load_library('webservice:core');

elgg_register_event_handler('init', 'system', 'blogapi_init');
