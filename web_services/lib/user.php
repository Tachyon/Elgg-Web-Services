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
function rest_user_getprofilelabels() {	
	$user_fields = elgg_get_config('profile_fields');
	foreach ($user_fields as $key => $type) {
		$profile_labels[$key]['label'] = elgg_echo('profile:'.$key);
		$profile_labels[$key]['type'] = $type;
	}
	return $profile_labels;
}
	
expose_function('user.profilelabels',
				"rest_user_getprofilelabels",
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
function rest_user_getprofile($username) {
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
	
/**
 * Web service to update profile information
 *
 * @param string $username username to update profile information
 *
 * @return bool 
 */
function rest_user_updateprofile($username, $profile) {
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
				create_metadata($owner->getGUID(), $shortname, $value, 'text', $owner->getGUID(), $access_id);
			}
		}
	}
	return "Success";
}
	
expose_function('user.updateprofile',
				"rest_user_updateprofile",
				array('username' => array ('type' => 'string'),
					 'profile' => array ('type' => 'array'),
					),
				"Get user profile information with username",
				'POST',
				false,
				false);

/**
 * Web service to get all users registered with an email ID
 *
 * @param string $email Email ID to check for
 *
 * @return string $foundusers Array of usernames registered with this email ID
 */
function rest_user_getbyemail($email) {
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

expose_function('user.getbyemail',
				"rest_user_getbyemail",
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
function rest_user_checkailability($username) {
	$user = get_user_by_username($username);
	if (!$user) {
		return true;
	} else {
		return false;
	}
}

expose_function('user.checkavail',
				"rest_user_checkailability",
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
function rest_user_register($name, $email, $username, $password) {
	$user = get_user_by_username($username);
	if (!$user) {
		return register_user($username, $password, $name, $email);
	} else {
		throw new InvalidParameterException('registration:userexists');
	}
}

expose_function('user.register',
				"rest_user_register",
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
function rest_user_addfriend($username, $friend) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	
	$friend_user = get_user_by_username($friend);
	if (!$friend_user) {
		throw new InvalidParameterException(elgg_echo("friends:add:failure", array($friend_user->name)));
	}
	
	if($friend_user->isFriendOf($user->guid)) {
		throw new InvalidParameterException(elgg_echo('friends:alreadyadded', array($friend_user->name)));
	}
	
	try {
		if (!$user->addFriend($friend_user->guid)) {
			$errors = true;
		}
		
	} catch (Exception $e) {
		$errors = true;
		throw new InvalidParameterException(elgg_echo("friends:add:failure", array($friend_user->name)));
	}

	if (!$errors) {
		// add to river
		add_to_river('river/relationship/friend/create', 'friend', $user->guid, $friend_user->guid);
		return true;
	}
}

expose_function('user.addfriend',
				"rest_user_addfriend",
				array('username' => array ('type' => 'string'),
						'password' => array ('type' => 'string'),
						'friend' => array ('type' => 'string'),
					),
				"Register user",
				'GET',
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
function rest_user_removefriend($username, $friend) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	
	$friend_user = get_user_by_username($friend);
	if (!$friend_user) {
		throw new InvalidParameterException(elgg_echo("friends:remove:failure", array($friend_user->name)));
	}
	
	if(!$friend_user->isFriendOf($user->guid)) {
		throw new InvalidParameterException(elgg_echo('friends:remove:notfriend', array($friend_user->name)));
	}
	
	try {
		if (!$user->removeFriend($friend_user->guid)) {
			$errors = true;
		}
	} catch (Exception $e) {
		$errors = true;
		throw new InvalidParameterException(elgg_echo("friends:remove:failure", array($friend_user->name)));
	}

	if (!$errors) {
		system_message(elgg_echo("friends:remove:successful", array($friend->name)));
		return true;
	}
}

expose_function('user.removefriend',
				"rest_user_removefriend",
				array('username' => array ('type' => 'string'),
						'password' => array ('type' => 'string'),
						'friend' => array ('type' => 'string'),
					),
				"Register user",
				'GET',
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
function rest_user_getfriend($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	$friends = get_user_friends($user->guid, '' , $limit, $offset);

	$return = array();
	foreach($friends as $friend) {
		$return[$friend->username] = $friend->name;
	}
	return $return;
}

expose_function('user.getfriend',
				"rest_user_getfriend",
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
function rest_user_getfriendof($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
	}
	$friends = get_user_friends_of($user->guid, '' , $limit, $offset);

	$return = array();
	foreach($friends as $friend) {
		$return[$friend->username] = $friend->name;
	}
	return $return;
}

expose_function('user.getfriendof',
				"rest_user_getfriendof",
				array('username' => array ('type' => 'string', 'required' => true),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Register user",
				'GET',
				false,
				false);	

