<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
 
function rest_site_getinfo() {
		$siteinfo['url'] = elgg_get_config('www_root');
		$siteinfo['sitename'] = elgg_get_config('site_name');
		$siteinfo['language'] = elgg_get_config('language');
		return $siteinfo;
	} 

expose_function('site.getinfo',
                "rest_site_getinfo",
                array( ),
                "Get site information",
                'GET',
                false,
                false);
