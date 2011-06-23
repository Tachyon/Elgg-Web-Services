<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

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
	
expose_function('user.getprofile',
				"rest_user_getprofile",
				array('username' => array ('type' => 'string'),
					),
				"Get user profile information with username",
				'GET',
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
				

