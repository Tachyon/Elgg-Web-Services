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
 * @param string $access   Access level of blog
 *
 * @return bool
 */
function rest_blog_post($username, $title, $text, $excerpt = "", $tags = "blog" , $access = ACCESS_PUBLIC) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$obj = new ElggObject();
	$obj->subtype = "blog";
	$obj->owner_guid = $user->guid;
	$obj->access_id = strip_tags($access);
	$obj->method = "api";
	$obj->description = strip_tags($text);
	$obj->title = elgg_substr(strip_tags($title), 0, 140);
	$obj->status = 'published';
	$obj->comments_on = 'On';
	$obj->excerpt = strip_tags($excerpt);
	$obj->tags = strip_tags($tags);
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
				array('username' => array ('type' => 'string', 'required' => true),
						'title' => array ('type' => 'string', 'required' => true),
						'text' => array ('type' => 'string', 'required' => true),
						'excerpt' => array ('type' => 'string', 'required' => false),
						'tags' => array ('type' => 'string', 'required' => false),
						'access' => array ('type' => 'string', 'required' => false),
					),
				"Post a blog post",
				'POST',
				true,
				false);
				
/**
 * Web service for read a blog post
 *
 * @param string $guid     GUID of a blog entity
 * @param string $username Username of reader (Send NULL if no user logged in)
 * @param string $password Password for authentication of username (Send NULL if no user logged in)
 *
 * @return string $title       Title of blog post
 * @return string $content     Text of blog post
 * @return string $excerpt     Excerpt
 * @return string $tags        Tags of blog post
 * @return string $owner_guid  GUID of owner
 * @return string $access_id   Access level of blog post (0,-2,1,2)
 * @return string $status      (Published/Draft)
 * @return string $comments_on On/Off
 */
function rest_blog_read($guid, $username) {
	$return = array();
	$blog = get_entity($guid);

	if (!elgg_instanceof($blog, 'object', 'blog')) {
		$return['content'] = elgg_echo('blog:error:post_not_found');
		return $return;
	}
	
	$user = get_user_by_username($username);
	if ($user) {
		if (!has_access_to_entity($blog, $user)) {
			$return['content'] = elgg_echo('blog:error:post_not_found');
			return $return;
		}
		
		if ($blog->status!='published' && $user->guid!=$blog->owner_guid) {
			$return['content'] = elgg_echo('blog:error:post_not_found');
			return $return;
		}
	} else {
		if($blog->access_id!=2) {
			$return['content'] = elgg_echo('blog:error:post_not_found');
			return $return;
		}
	}

	$return['title'] = htmlspecialchars($blog->title);
	$return['content'] = $blog->description;
	$return['excerpt'] = $blog->excerpt;
	$return['tags'] = $blog->tags;
	$return['owner_guid'] = $blog->owner_guid;
	$return['access_id'] = $blog->access_id;
	$return['status'] = $blog->status;
	$return['comments_on'] = $blog->comments_on;
	return $return;
}
	
expose_function('blog.read',
				"rest_blog_read",
				array('guid' => array ('type' => 'string'),
						'username' => array ('type' => 'string'),
					),
				"Read a blog post",
				'GET',
				true,
				false);
				
/**
 * Web service for delete a blog post
 *
 * @param string $guid     GUID of a blog entity
 * @param string $username Username of reader (Send NULL if no user logged in)
 * @param string $password Password for authentication of username (Send NULL if no user logged in)
 *
 * @return bool
 */
function rest_blog_delete($guid, $username) {
	$return = array();
	$blog = get_entity($guid);

	if (!elgg_instanceof($blog, 'object', 'blog')) {
		$return['content'] = elgg_echo('blog:error:post_not_found');
		return $return;
	}
	
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$blog = get_entity($guid);
	if($user->guid!=$blog->owner_guid) {
		return elgg_echo('blog:message:notauthorized');
	}

	if (elgg_instanceof($blog, 'object', 'blog') && $blog->canEdit()) {
		$container = get_entity($blog->container_guid);
		if ($blog->delete()) {
			return elgg_echo('blog:message:deleted_post');
		} else {
			return elgg_echo('blog:error:cannot_delete_post');
		}
	} else {
		return elgg_echo('blog:error:post_not_found');
	}
}
	
expose_function('blog.delete',
				"rest_blog_delete",
				array('guid' => array ('type' => 'string'),
						'username' => array ('type' => 'string'),
					),
				"Read a blog post",
				'GET',
				true,
				false);

								
/**
 * Web service for read latest wire post by friends
 *
 * @param string $username username
 * @param string $password password
 * @param string $limit    number of results to display
 * @param string $offset   offset of list
 *
 * @return string $time_created Time at which blog post was made
 * @return string $title        Title of blog post
 * @return string $content      Text of blog post
 * @return string $excerpt      Excerpt
 * @return string $tags         Tags of blog post
 * @return string $owner_guid   GUID of owner
 * @return string $access_id    Access level of blog post (0,-2,1,2)
 * @return string $status       (Published/Draft)
 * @return string $comments_on  On/Off
 */
function rest_blog_friend($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}

	$posts = get_user_friends_objects($user->guid, 'blog', $limit = 10, $offset = 0);
	if($posts) {
		foreach($posts as $single ) {
			$blog[$single->guid]['time_created'] = $single->time_created;
			$blog[$single->guid]['title'] = htmlspecialchars($single->title);
			$blog[$single->guid]['content'] = $single->description;
			$blog[$single->guid]['excerpt'] = $single->excerpt;
			$blog[$single->guid]['tags'] = $single->tags;
			$blog[$single->guid]['owner_guid'] = $single->owner_guid;
			$blog[$single->guid]['access_id'] = $single->access_id;
			$blog[$single->guid]['status'] = $single->status;
			$blog[$single->guid]['comments_on'] = $single->comments_on;
		}
	} else {
		$blog = elgg_echo("blog:message:noposts");
	}
	return $blog;
	} 
				
expose_function('blog.friend',
				"rest_blog_friend",
				array('username' => array ('type' => 'string'),
						'password' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Read lates wire post",
				'GET',
				true,
				false);
				
/**
 * Web service for read latest wire post by friends
 *
 * @param string $username username
 * @param string $limit    number of results to display
 * @param string $offset   offset of list
 *
 * @return string $time_created Time at which blog post was made
 * @return string $title        Title of blog post
 * @return string $content      Text of blog post
 * @return string $excerpt      Excerpt
 * @return string $tags         Tags of blog post
 * @return string $owner_guid   GUID of owner
 * @return string $access_id    Access level of blog post (0,-2,1,2)
 * @return string $status       (Published/Draft)
 * @return string $comments_on  On/Off
 */
function rest_blog_user($username, $limit = 10, $offset = 0) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$posts = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'blog',
			'owner_guids' => $user->guid,
			'limit' => $limit,
			'offset' => $offset,
			'container_guids' => $$user->guid,
			'created_time_lower' => 0,
			'created_time_upper' => 0
		));
	
	if($posts) {
		foreach($posts as $single ) {
			$blog[$single->guid]['time_created'] = $single->time_created;
			$blog[$single->guid]['title'] = htmlspecialchars($single->title);
			$blog[$single->guid]['content'] = $single->description;
			$blog[$single->guid]['excerpt'] = $single->excerpt;
			$blog[$single->guid]['tags'] = $single->tags;
			$blog[$single->guid]['owner_guid'] = $single->owner_guid;
			$blog[$single->guid]['access_id'] = $single->access_id;
			$blog[$single->guid]['status'] = $single->status;
			$blog[$single->guid]['comments_on'] = $single->comments_on;
		}
	} else {
		$blog = elgg_echo("blog:message:noposts");
	}
	return $blog;
	} 
				
expose_function('blog.user',
				"rest_blog_user",
				array('username' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false),
						'offset' => array ('type' => 'int', 'required' => false),
					),
				"Read latest wire post by a single user",
				'GET',
				true,
				false);