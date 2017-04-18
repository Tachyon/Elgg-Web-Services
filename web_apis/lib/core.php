<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Heartbeat web service
 *
 * @return string $response Hello
 */
function site_test() {
	$response['success'] = true;
	$response['message'] = "Hello";
	return $response;
} 

expose_function('site.test',
				"site_test",
				array(),
				"Get site information",
				'GET',
				false,
				false);

/**
 * Web service to get site information
 *
 * @return string $url URL of Elgg website
 * @return string $sitename Name of Elgg website
 * @return string $language Language of Elgg website
 */
function site_getinfo() {
	$site = elgg_get_config('site');

	$siteinfo['url'] = elgg_get_site_url();
	$siteinfo['sitename'] = $site->name;
	$siteinfo['language'] = elgg_get_config('language');
	
	return $siteinfo;
} 

expose_function('site.getinfo',
				"site_getinfo",
				array(),
				"Get site information",
				'GET',
				false,
				false);
