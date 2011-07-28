<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Web service to get profile labels
 *
 * @return string $profile_labels Array of profile labels
 */
function user_get_profile_fields() {	
	$user_fields = elgg_get_config('profile_fields');
	foreach ($user_fields as $key => $type) {
		$profile_labels[$key]['label'] = elgg_echo('profile:'.$key);
		$profile_labels[$key]['type'] = $type;
	}
	return $profile_labels;
}
	
expose_function('user.get_profile_fields',
				"user_get_profile_fields",
				array(),
				"Get user profile labels",
				'GET',
				false,
				false);

/**
 * Web service to get profile information
 *
 * @param string $username username to get profile information
 *
 * @return string $user_fields Array of profile information with labels as the keys
 */
function user_get_profile($username) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$user_fields = elgg_get_config('profile_fields');
	foreach ($user_fields as $key => $type) {
		$user_fields[$key] = $user->$key;
	}
	return $user_fields;
}

expose_function('user.get_profile',
				"user_get_profile",
				array('username' => array ('type' => 'string')
					),
				"Get user profile labels",
				'GET',
				false,
				false);
/**
 * Web service to update profile information
 *
 * @param string $username username to update profile information
 *
 * @return bool 
 */
function user_save_profile($username, $profile) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$owner = get_entity($user->guid);
	$profile_fields = elgg_get_config('profile_fields');
	foreach ($profile_fields as $shortname => $valuetype) {
		$value = $profile[$shortname];
		$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');

		if ($valuetype != 'longtext' && elgg_strlen($value) > 250) {
			$error = elgg_echo('profile:field_too_long', array(elgg_echo("profile:{$shortname}")));
			return $error;
		}

		if ($valuetype == 'tags') {
			$value = string_to_tag_array($value);
		}
		$input[$shortname] = $value;
	}
	
	$name = strip_tags($profile['name']);
	if ($name) {
		if (elgg_strlen($name) > 50) {
			return elgg_echo('user:name:fail');
		} elseif ($owner->name != $name) {
			$owner->name = $name;
			return $owner->save();
			if (!$owner->save()) {
				return elgg_echo('user:name:fail');
			}
		}
	}
	
	if (sizeof($input) > 0) {
		foreach ($input as $shortname => $value) {
			$options = array(
				'guid' => $owner->guid,
				'metadata_name' => $shortname
			);
			elgg_delete_metadata($options);
			if (isset($accesslevel[$shortname])) {
				$access_id = (int) $accesslevel[$shortname];
			} else {
				// this should never be executed since the access level should always be set
				$access_id = ACCESS_DEFAULT;
			}
			if (is_array($value)) {
				$i = 0;
				foreach ($value as $interval) {
					$i++;
					$multiple = ($i > 1) ? TRUE : FALSE;
					create_metadata($owner->guid, $shortname, $interval, 'text', $owner->guid, $access_id, $multiple);
				}
			} else {
				create_metadata($owner->guid, $shortname, $value, 'text', $owner->guid, $access_id);
			}
		}
	}
	return "Success";
}
	
expose_function('user.save_profile',
				"user_save_profile",
				array('username' => array ('type' => 'string'),
					 'profile' => array ('type' => 'array'),
					),
				"Get user profile information with username",
				'POST',
				true,
				false);

/**
 * Web service to get all users registered with an email ID
 *
 * @param string $email Email ID to check for
 *
 * @return string $foundusers Array of usernames registered with this email ID
 */
function user_get_user_by_email($email) {
	if (!validate_email_address($email)) {
		throw new RegistrationException(elgg_echo('registration:notemail'));
	}

	$user = get_user_by_email($email);
	if (!$user) {
		throw new InvalidParameterException('registration:emailnotvalid');
	}
	foreach ($user as $key => $singleuser) {
		$foundusers[$key] = $singleuser->username;
	}
	return $foundusers;
}

expose_function('user.get_user_by_email',
				"user_get_user_by_email",
				array('email' => array ('type' => 'string'),
					),
				"Get Username by email",
				'GET',
				false,
				false);

/**
 * Web service to check availability of username
 *
 * @param string $username Username to check for availaility 
 *
 * @return bool
 */           
function user_check_username_availability($username) {
	$user = get_user_by_username($username);
	if (!$user) {
		return true;
	} else {
		return false;
	}
}

expose_function('user.check_username_availability',
				"user_check_username_availability",
				array('username' => array ('type' => 'string'),
					),
				"Get Username by email",
				'GET',
				false,
				false);

/**
 * Web service to register user
 *
 * @param string $name     Display name 
 * @param string $email    Email ID 
 * @param string $username Username
 * @param string $password Password 
 *
 * @return bool
 */           
function user_register($name, $email, $username, $password) {
	$user = get_user_by_username($username);
	if (!$user) {
		$return['success'] = true;
		$return['guid'] = register_user($username, $password, $name, $email);
	} else {
		$return['success'] = false;
		$return['message'] = elgg_echo('registration:userexists');
	}
	return $return;
}

expose_function('user.register',
				"user_register",
				array('name' => array ('type' => 'string'),
						'email' => array ('type' => 'string'),
						'username' => array ('type' => 'string'),
						'password' => array ('type' => 'string'),
					),
				"Register user",
				'GET',
				false,
				false);

/**
 * Web service to add as friend
 *
 * @param string $username Username
 * @param string $friend Username to be added as friend
 *
 * @return bool
 */           
function user_friend_add($username, $friend) {
	$user = get_user_by_username($username);
	$return['success'] = false;
	if (!$user) {
		$return['message'] = elgg_echo('registration:usernamenotvalid');
	}
	
	$friend_user = get_user_by_username($friend);
	if (!$friend_user) {
		$return['message'] = elgg_echo("friends:add:failure", array($friend_user->name));
	}
	
	if($friend_user->isFriendOf($user->guid)) {
		$return['message'] = elgg_echo('friends:alreadyadded', array($friend_user->name));
	}
	
	try {
		if (!$user->addFriend($friend_user->guid)) {
			$errors = true;
		}
		
	} catch (Exception $e) {
		$errors = true;
		$return['message'] = elgg_echo("friends:add:failure", array($friend_user->name));
	}

	if (!$errors) {
		// add to river
		add_to_river('river/relationship/friend/create', 'friend', $user->guid, $friend_user->guid);
		$return['success'] = true;
		$return['message'] = elgg_echo('friends:add:successful' , array($friend_user->name));
	}
	return $return;
}

expose_function('user.friend.add',
				"user_friend_add",
				array('username' => array ('type' => 'string'),
						'friend' => array ('type' => 'string'),
					),
				"Add a user as friend",
				'POST',
				true,
				false);	
				

/**
 * Web service to remove friend
 *
 * @param string $username Username
 * @param string $friend Username to be removed from friend
 *
 * @return bool
 */           
function user_friend_remove($username, $friend) {
	$user = get_user_by_username($username);
	$return['success'] = false;
	if (!$user) {
		$return['message'] = elgg_echo('registration:usernamenotvalid');
	}
	
	$friend_user = get_user_by_username($friend);
	if (!$friend_user) {
		$return['message'] = elgg_echo("friends:remove:failure", array($friend_user->name));
	}
	
	if(!$friend_user->isFriendOf($user->guid)) {
		$return['message'] = elgg_echo('friends:remove:notfriend', array($friend_user->name));
	}
	
	try {
		if (!$user->removeFriend($friend_user->guid)) {
			$errors = true;
		}
	} catch (Exception $e) {
		$errors = true;
		$return['message'] = elgg_echo("friends:remove:failure", array($friend_user->name));
	}

	if (!$errors) {
		$return['message'] = elgg_echo("friends:remove:successful", array($friend->name));
		$return['success'] = true;
	}
	return $return;
}

expose_function('user.friend.remove',
				"user_friend_remove",
				array('username' => array ('type' => 'string'),
						'friend' => array ('type' => 'string'),
					),
				"Remove friend",
				'POST',
				true,
				false);				
				
/**
 * Web service to get friends of a user
 *
 * @param string $username Username
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */           
function user_get_friends($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	$friends = get_user_friends($user->guid, '' , $limit, $offset);
	
	$success = false;
	foreach($friends as $friend) {
		$return[$friend->guid]['username'] = $friend->username;
		$return[$friend->guid]['name'] = $friend->name;
		$success = true;
	}
	
	if(!$success) {
		$return['error']['message'] = elgg_echo('friends:none');
	}
	return $return;
}

expose_function('user.friend.get_friends',
				"user_get_friends",
				array('username' => array ('type' => 'string', 'required' => true),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Register user",
				'GET',
				false,
				false);	
				
/**
 * Web service to obtains the people who have made a given user a friend
 *
 * @param string $username Username
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */           
function user_get_friends_of($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	$friends = get_user_friends_of($user->guid, '' , $limit, $offset);
	
	$success = false;
	foreach($friends as $friend) {
		$return[$friend->guid]['username'] = $friend->username;
		$return[$friend->guid]['name'] = $friend->name;
		$success = true;
	}
	
	if(!$success) {
		$return['error']['message'] = elgg_echo('friends:none');
	}
	return $return;
}

expose_function('user.friend.get_friends_of',
				"user_get_friends_of",
				array('username' => array ('type' => 'string', 'required' => true),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Register user",
				'GET',
				false,
				false);	

