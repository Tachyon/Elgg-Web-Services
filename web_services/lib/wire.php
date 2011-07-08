<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
 
 /**
 * Web service for making a wire post
 *
 * @param string $username username of author
 * @param string $text     the content of wire post
 * @param string $acess    access level for post{-1, 0, 1, 2, -2}
 * @param string $password password of user
 *
 * @return bool
 */
function rest_wire_post($username, $text, $access = ACCESS_PUBLIC) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	if (empty($text)) {
		return elgg_echo("thewire:blank");
	}
	$access_id = strip_tags($access);
	$guid = thewire_save_post($text, $user->guid, $access_id, "api");
	if (!$guid) {
		return elgg_echo("thewire:error");
	}

	return "success";
	} 
				
expose_function('wire.post',
				"rest_wire_post",
				array('username' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
						'access' => array ('type' => 'string', 'required' => false),
					),
				"Post a wire post",
				'GET',
				true,
				false);
				
/**
 * Web service for read latest wire post of user
 *
 * @param string $username username of author
 *
 * @return bool
 */
function rest_wire_read($username) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}

	$params = array(
		'types' => 'object',
		'subtypes' => 'thewire',
		'owner_guid' => $user->guid,
		'limit' => 1,
	);
	$latest_wire = elgg_get_entities($params);

	$wire['guid'] = $latest_wire[0]->guid;
	$wire['time_created'] = $latest_wire[0]->time_created;
	$wire['description'] = $latest_wire[0]->description;
	return $wire;
	} 
				
expose_function('wire.read',
				"rest_wire_read",
				array('username' => array ('type' => 'string'),
					),
				"Read lates wire post",
				'GET',
				false,
				false);
				
/**
 * Web service for read latest wire post by friends
 *
 * @param string $username username
 * @param string $limit    number of results to display
 * @param string $offset   offset of list
 *
 * @return bool
 */
function rest_wire_friend($username, $password, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}

	$posts = get_user_friends_objects($user->guid, 'thewire', $limit, $offset);

	foreach($posts as $single ) {
		$wire[$single->guid]['time_created'] = $single->time_created;
		$wire[$single->guid]['description'] = $single->description;
	}
	return $wire;
	} 
				
expose_function('wire.friend',
				"rest_wire_friend",
				array('username' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Read lates wire post",
				'GET',
				true,
				false);