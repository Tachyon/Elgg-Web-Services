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
function wire_save_post($username, $text, $access = ACCESS_PUBLIC) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$return['success'] = false;
	if (empty($text)) {
		$return['message'] = elgg_echo("thewire:blank");
		return $return;
	}
	$access_id = strip_tags($access);
	$guid = thewire_save_post($text, $user->guid, $access_id, "api");
	if (!$guid) {
		$return['message'] = elgg_echo("thewire:error");
		return $return;
	}
	$return['success'] = true;
	return $return;
	} 
				
expose_function('wire.save_post',
				"wire_save_post",
				array('username' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
						'access' => array ('type' => 'string', 'required' => false),
					),
				"Post a wire post",
				'POST',
				true,
				false);
				
/**
 * Web service for read latest wire post of user
 *
 * @param string $username username of author
 *
 * @return bool
 */
function wire_get_post($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}

	$params = array(
		'types' => 'object',
		'subtypes' => 'thewire',
		'owner_guid' => $user->guid,
		'limit' => $limit,
		'offset' => $offset,
	);
	$latest_wire = elgg_get_entities($params);

	foreach($latest_wire as $single ) {
		$wire[$single->guid]['time_created'] = $single->time_created;
		$wire[$single->guid]['description'] = $single->description;
	}
	return $wire;
	} 
				
expose_function('wire.get_posts',
				"wire_get_post",
				array('username' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
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
function wire_get_friends_posts($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}

	$posts = get_user_friends_objects($user->guid, 'thewire', $limit, $offset);

	if($posts) {
		foreach($posts as $single ) {
			$wire[$single->guid]['time_created'] = $single->time_created;
			$wire[$single->guid]['description'] = $single->description;
		}
	} else {
		$wire['error']['message'] = elgg_echo('thewire:noposts');
	}
	return $wire;
} 
				
expose_function('wire.get_friends_posts',
				"wire_get_friends_posts",
				array('username' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Read lates wire post",
				'GET',
				true,
				false);
				
/**
 * Web service for delete a wire post
 *
 * @param string $username username
 * @param string $wireid   GUID of wire post to delete
 *
 * @return bool
 */
function wire_delete($username, $wireid) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$thewire = get_entity($wireid);
	$return['success'] = false;
	if ($thewire->getSubtype() == "thewire" && $thewire->canEdit($user->guid)) {
		$children = elgg_get_entities_from_relationship(array(
			'relationship' => 'parent',
			'relationship_guid' => $wireid,
			'inverse_relationship' => true,
		));
		if ($children) {
			foreach ($children as $child) {
				$child->reply = false;
			}
		}
		$rowsaffected = $thewire->delete();
		if ($rowsaffected > 0) {
			$return['success'] = true;
			$return['message'] = elgg_echo("thewire:deleted");
		} else {
			$return['message'] = elgg_echo("thewire:notdeleted");
		}
	}
	else {
		$return['message'] = elgg_echo("thewire:notdeleted");
	}
	return $return;
} 
				
expose_function('wire.delete_posts',
				"wire_delete",
				array('username' => array ('type' => 'string'),
						'wireid' => array ('type' => 'int'),
					),
				"Delete a wire post",
				'POST',
				true,
				false);