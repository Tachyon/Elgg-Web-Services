<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

function rest_user_getprofile($username) {
		$user = get_user_by_username($username);
		if (!$user) {
			throw new InvalidParameterException("Bad username");
		}
		
		$user_fields = elgg_get_config('profile_fields');
		foreach( $user_fields as $key => $type){
			$user_fields[$key] =  $user->$key;
		}
		return $user_fields;
	} 
	
expose_function('user.getprofile',
                "rest_user_getprofile",
                array( 'username' => array ('type' => 'string'),
                     ),
                "Get user profile informtion with username",
                'GET',
                false,
                false);

function rest_user_getbyemail($email) {
		if (!validate_email_address($email)) {
			throw new RegistrationException(elgg_echo('Invalid email address'));
		}

		$user = get_user_by_email($email);
		if (!$user) {
			throw new InvalidParameterException("User not registered");
		}
		foreach($user as $key => $singleuser) {
			$foundusers[$key] = $singleuser->username;
		}
		return $foundusers;
	} 

expose_function('user.getbyemail',
                "rest_user_getbyemail",
                array( 'email' => array ('type' => 'string'),
					),
                "Get Username by email",
                'GET',
                false,
                false);

/**
 * Function to check availability of username
 * 
 * @input Webservice
 * @output Saket Saurabh
 *
 * @usage http://mysite.com/services/api/rest/xml/?method=user.checkavail&username=admin
 */

            
function rest_user_checkailability($username) {
		$user = get_user_by_username($username);
		if (!$user) {
			return "Username available";
		}
		else {
			return "Username already registered";
		}
	} 

expose_function('user.checkavail',
                "rest_user_checkailability",
                array( 'username' => array ('type' => 'string'),
					),
                "Get Username by email",
                'GET',
                false,
                false);
                
/**
 * Function to register user
 * 
 * @input Webservice
 * @output Saket Saurabh
 *
 * @usage http://mysite.com/services/api/rest/xml/?method=user.checkavail&username=admin
 */

            
function rest_user_register($name, $email, $username, $password) {
		$user = get_user_by_username($username);
		if (!$user) {
			return register_user($username, $password, $name, $email);
		}
		else {
			throw new InvalidParameterException("Username already registered");
		}
	} 

expose_function('user.register',
                "rest_user_register",
                array( 'name' => array ('type' => 'string'),
					   'email' => array ('type' => 'string'),
					   'username' => array ('type' => 'string'),
                       'password' => array ('type' => 'string'),
					),
                "Register user",
                'GET',
                false,
                false);
