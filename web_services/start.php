<?php
/**
 * Elgg BlogPost Webservice plugin 
 * 
 * @package BlogPostWebservice
 * @author Saket Saurabh
 *
 */
function blogapi_init() {

}
				
function rest_blog_post($username, $title, $excrept, $text, $tags) {
		$user = get_user_by_username($username);
		if (!$user) {
			throw new InvalidParameterException("Bad username");
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
	$obj->excerpt = elgg_substr(strip_tags($excrept), 0, 140);
	$obj->tags = elgg_substr(strip_tags($tags), 0, 140);
	$guid = $obj->save();
	add_to_river('river/object/blog/create',
	'create',
	$user->guid,
	$obj->guid
	);

	return "success";
	} 

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
	
expose_function('blog.post',
                "rest_blog_post",
                array( 'username' => array ('type' => 'string'),
					   'title' => array ('type' => 'string'),
					   'excrept' => array ('type' => 'string'),
                       'text' => array ('type' => 'string'),
					   'tags' => array ('type' => 'string'),
                     ),
                "Post a blog post",
                'GET',
                false,
                false);

expose_function('user.getprofile',
                "rest_user_getprofile",
                array( 'username' => array ('type' => 'string'),
                     ),
                "Get user profile informtion with username",
                'GET',
                false,
                false);


elgg_register_event_handler('init', 'system', 'blogapi_init');
