<?php
/**
 * Elgg Webservice plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
function blogapi_init() {

}

include("lib/blog.php");
include("lib/user.php");
include("lib/core.php");


elgg_register_event_handler('init', 'system', 'blogapi_init');
