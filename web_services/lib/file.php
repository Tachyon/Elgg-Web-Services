<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Web service to get file list by all users
 *
 * @param string $limit  (optional) default 10
 * @param string $offset (optional) default 0
 *
 * @return array $file Array of files uploaded
 */
function file_get_files($username, $limit = 10, $offset = 0) {	
	if($username) {
		$user = get_user_by_username($username);
		if (!$user) {
			throw new InvalidParameterException('registration:usernamenotvalid');
		}
		$params = array(
			'types' => 'object',
			'subtypes' => 'file',
			'owner_guid' => $user->guid,
			'limit' => $limit,
			'full_view' => FALSE
		);
	} else {
		$params = array(
			'types' => 'object',
			'subtypes' => 'file',
			'limit' => $limit,
			'full_view' => FALSE
		);
	}
	$latest_file = elgg_get_entities($params);
	if($latest_file) {
		foreach($latest_file as $single ) {
			$file[$single->guid]['title'] = $single->title;
			$file[$single->guid]['owner_guid'] = $single->owner_guid;
			$file[$single->guid]['container_guid'] = $single->container_guid;
			$file[$single->guid]['access_id'] = $single->access_id;
			$file[$single->guid]['time_created'] = $single->time_created;
			$file[$single->guid]['time_updated'] = $single->time_updated;
			$file[$single->guid]['last_action'] = $single->last_action;
		}
	}
	else {
		$file['error']['message'] = elgg_echo('file:none');
	}
	return $file;
}
	
expose_function('file.get_files',
				"file_get_files",
				array('username' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get file uploaded by all users",
				'GET',
				false,
				false);
				
/**
 * Web service to get file list by friends
 *
 * @param string $username  username
 * @param string $limit     (optional) default 10
 * @param string $offset    (optional) default 0
 *
 * @return array $file Array of files uploaded
 */
function file_get_files_by_friend($username, $limit = 10, $offset = 0) {	
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$latest_file = get_user_friends_objects($user->guid, 'file', $limit, $offset);
	if($latest_file) {
		foreach($latest_file as $single ) {
			$file[$single->guid]['title'] = $single->title;
			$file[$single->guid]['owner_guid'] = $single->owner_guid;
			$file[$single->guid]['container_guid'] = $single->container_guid;
			$file[$single->guid]['access_id'] = $single->access_id;
			$file[$single->guid]['time_created'] = $single->time_created;
			$file[$single->guid]['time_updated'] = $single->time_updated;
			$file[$single->guid]['last_action'] = $single->last_action;
		}
	}
	else {
		$file['error']['message'] = elgg_echo('file:none');
	}
	return $file;
}
	
expose_function('file.get_files_by_friend',
				"file_get_files_by_friend",
				array('username' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get file uploaded by friends",
				'GET',
				true,
				false);
