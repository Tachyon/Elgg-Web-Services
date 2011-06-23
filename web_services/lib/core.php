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
function rest_site_test() {
		$response = "Hello";
		return $response;
	} 

expose_function('site.test',
				"rest_site_test",
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
function rest_site_getinfo() {
		$siteinfo['url'] = elgg_get_config('www_root');
		$siteinfo['sitename'] = elgg_get_config('site_name');
		$siteinfo['language'] = elgg_get_config('language');
		return $siteinfo;
	} 

expose_function('site.getinfo',
				"rest_site_getinfo",
				array(),
				"Get site information",
				'GET',
				false,
				false);
