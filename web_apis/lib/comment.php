<?php

 /**
 * Web service for comment a blog identified by guid
 *
 * @param string $username username of author
 * @param string $guid     parent blog's guid
 * @param string $text     the content of comment 
 * @param string $acess    access level for post{-1, 0, 1, 2, -2}
 *
 * @return bool
 */
function blog_comment($username, $guid, $text) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$return['success'] = false;
	if (empty($text)) {
		$return['message'] = elgg_echo("blog:blank");
		return $return;
	}

  $parent = get_entity($guid);
  
  $comment = new ElggComment();
  $comment->description = $text;
  $comment->container_guid = $guid;
  $comment->owner_guid = $user->guid;
  $comment->access_id = $parent->access_id;
  $saveid = $comment->save();

	if (!$saveid) {
		$return['message'] = elgg_echo("comment:error");
		return $return;
	}
  
	elgg_create_river_item(array(
        'view' => 'river/object/comment/create',
        'action_type' => 'comment',
        'subject_guid' => $user->guid,
        'object_guid' => $comment->getGUID(),
        'target_guid' => $parent->guid,
													));

	$return['success'] = true;
	return $return;
	} 
				
elgg_ws_expose_function('blog.save_comment',
				"blog_comment",
				array('username' => array ('type' => 'string'),
            'guid' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
					),
				"Post a blog comment",
				'POST',
				true,
				false);
	
 /**
 * Web service for comment a wirepost identified by guid
 *
 * @param string $username username of author
 * @param string $guid     parent wire guid
 * @param string $text     the content of comment 
 * @param string $acess    access level for post{-1, 0, 1, 2, -2}
 *
 * @return bool
 */
function wire_comment($username, $guid, $text, $access = ACCESS_PUBLIC) {
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

  $saveid = thewire_save_post($text, $user->guid, $access_id, $guid, "api");
	if (!$saveid) {
		$return['message'] = elgg_echo("thewire:error");
		return $return;
	}
	$return['success'] = true;
  return $return;
} 

elgg_ws_expose_function('wire.save_comment',
				"wire_comment",
				array('username' => array ('type' => 'string'),
            'guid' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
	          'access' => array ('type' => 'string', 'required' => false),
					),
				"Post a wire comment",
				'POST',
				true,
				false);
	


?>
