<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Web service for making a blog post
 *
 * @param string $username username of author
 * @param string $title    the title of blog
 * @param string $excerpt  the excerpt of blog
 * @param string $text     the content of blog
 * @param string $tags     tags for blog
 *
 * @return bool
 */
function rest_blog_post($username, $title, $excerpt, $text, $tags, $password) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$pam = new ElggPAM('user');
	$credentials = array('username' => $username, 'password' => $password);
	$result = $pam->authenticate($credentials);
	if (!$result) {
		return $pam->getFailureMessage();
	}
	$obj = new ElggObject();
	$obj->subtype = "blog";
	$obj->owner_guid = $user->guid;
	$obj->access_id = ACCESS_PUBLIC;
	$obj->method = "api";
	$obj->description = elgg_substr(strip_tags($text), 0, 140);
	$obj->title = elgg_substr(strip_tags($title), 0, 140);
	$obj->status = 'published';
	$obj->comments_on = 'On';
	$obj->excerpt = elgg_substr(strip_tags($excerpt), 0, 140);
	$obj->tags = elgg_substr(strip_tags($tags), 0, 140);
	$guid = $obj->save();
	add_to_river('river/object/blog/create',
	'create',
	$user->guid,
	$obj->guid
	);

	return "success";
	} 
	
expose_function('blog.post',
				"rest_blog_post",
				array('username' => array ('type' => 'string'),
						'title' => array ('type' => 'string'),
						'excerpt' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
						'tags' => array ('type' => 'string'),
						'password' => array ('type' => 'string'),
					),
				"Post a blog post",
				'GET',
				false,
				false);
