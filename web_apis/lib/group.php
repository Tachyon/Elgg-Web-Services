<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
 
 /**
 * Web service for joining a group
 *
 * @param string $username username of author
 * @param string $groupid  GUID of the group
 *
 * @return bool
 */
function group_join($username, $groupid) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$group = get_entity($groupid);
	$return['success'] = false;
	if (($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
		// join or request
		$join = false;
		if ($group->isPublicMembership() || $group->canEdit($user->guid)) {
			// anyone can join public groups and admins can join any group
			$join = true;
		} else {
			if (check_entity_relationship($group->guid, 'invited', $user->guid)) {
				// user has invite to closed group
				$join = true;
			}
		}

		if ($join) {
			if (groups_join_group($group, $user)) {
				$return['success'] = true;
				$return['message'] = elgg_echo("groups:joined");
			} else {
				$return['message'] = elgg_echo("groups:cantjoin");
			}
		} else {
			add_entity_relationship($user->guid, 'membership_request', $group->guid);

			// Notify group owner
			$url = "{$CONFIG->url}mod/groups/membershipreq.php?group_guid={$group->guid}";
			$subject = elgg_echo('groups:request:subject', array(
				$user->name,
				$group->name,
			));
			$body = elgg_echo('groups:request:body', array(
				$group->getOwnerEntity()->name,
				$user->name,
				$group->name,
				$user->getURL(),
				$url,
			));
			if (notify_user($group->owner_guid, $user->getGUID(), $subject, $body)) {
				$return['success'] = true;
				$return['message'] = elgg_echo("groups:joinrequestmade");
			} else {
				$return['message'] = elgg_echo("groups:joinrequestnotmade");
			}
		}
	} else {
		$return['message'] = elgg_echo("groups:cantjoin");
	}
	return $return;
} 
				
expose_function('group.join',
				"group_join",
				array('username' => array ('type' => 'string'),
						'groupid' => array ('type' => 'string'),
					),
				"Join a group",
				'POST',
				true,
				false);
				
 /**
 * Web service for leaving a group
 *
 * @param string $username username of author
 * @param string $groupid  GUID of the group
 *
 * @return bool
 */
function group_leave($username, $groupid) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$group = get_entity($groupid);
	$return['success'] = false;
	set_page_owner($group->guid);
	if (($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
		if ($group->getOwnerGUID() != elgg_get_logged_in_user_guid()) {
			if ($group->leave($user)) {
				$return['success'] = true;
				$return['message'] = elgg_echo("groups:left");
			} else {
				$return['message'] = elgg_echo("groups:cantleave");
			}
		} else {
			$return['message'] = elgg_echo("groups:cantleave");
		}
	} else {
		$return['message'] = elgg_echo("groups:cantleave");
	}
	return $return;
} 
				
expose_function('group.leave',
				"group_leave",
				array('username' => array ('type' => 'string'),
						'groupid' => array ('type' => 'string'),
					),
				"leave a group",
				'POST',
				true,
				false);
				
 /**
 * Web service for posting a new topic to a group
 *
 * @param string $username       username of author
 * @param string $groupid        GUID of the group
 * @param string $title          Title of new topic
 * @param string $description    Content of the post
 * @param string $status         status of the post
 * @param string $access_id      Access ID of the post
 *
 * @return bool
 */
function group_forum_save_post($username, $groupid, $title, $desc, $tags = "", $status = "published", $access_id = ACCESS_DEFAULT) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$group = get_entity($groupid);
	if (!$group) {
		throw new InvalidParameterException('group:notfound');
	}
	$return['success'] = false;
	// make sure user has permissions to write to container
	if (!can_write_to_container($user->guid, $groupid, "all", "all")) {
		$return['message'] = elgg_echo('groups:permissions:error');
	}
	
	$topic = new ElggObject();
	$topic->subtype = 'groupforumtopic';
	$topic->owner_guid = $user->guid;
	$topic->title = $title;
	$topic->description = $desc;
	$topic->status = $status;
	$topic->access_id = $access_id;
	$topic->container_guid = $groupid;
			
	$tags = explode(",", $tags);
	$topic->tags = $tags;

	$result = $topic->save();

	if (!$result) {
		$return['message'] = elgg_echo('discussion:error:notsaved');
	} else {
		$return['success'] = true;
		$return['message'] = elgg_echo('discussion:topic:created');
	}
	return $return;
} 
				
expose_function('group.forum.save_post',
				"group_forum_save_post",
				array('username' => array ('type' => 'string'),
						'groupid' => array ('type' => 'int'),
						'title' => array ('type' => 'string'),
						'desc' => array ('type' => 'string'),
						'tags' => array ('type' => 'string', 'required' => false),
						'status' => array ('type' => 'string', 'required' => false),
						'access_id' => array ('type' => 'int', 'required' => false),
					),
				"Post to a group",
				'POST',
				true,
				false);
				
/**
 * Web service for deleting a topic from a group
 *
 * @param string $username username of author
 * @param string $topicid  Topic ID
 *
 * @return bool
 */
function group_forum_delete_post($username, $topicid) {
	$topic = get_entity($topicid);
	
	$return['success'] = false;
	if (!$topic || !$topic->getSubtype() == "groupforumtopic") {
		$return['message'] = elgg_echo('discussion:error:notdeleted');
		return $return;
	}

	$user = get_user_by_username($username);
	if (!$user) {
		$return['message'] = elgg_echo('registration:usernamenotvalid');
		return $return;
	}

	if (!$topic->canEdit($user->guid)) {
		$return['message'] = elgg_echo('discussion:error:permissions');
	}

	$container = $topic->getContainerEntity();

	$result = $topic->delete();
	if ($result) {
		$return['success'] = true;
		$return['message'] = elgg_echo('discussion:topic:deleted');
	} else {
		$return['message'] = elgg_echo('discussion:error:notdeleted');
	}
	return $return;
} 
				
expose_function('group.forum.delete_post',
				"group_forum_delete_post",
				array('username' => array ('type' => 'string'),
						'topicid' => array ('type' => 'int'),
					),
				"Post to a group",
				'POST',
				true,
				false);
				
/**
 * Web service get latest post in a group
 *
 * @param string $groupid GUID of the group
 * @param string $limit   (optional) default 10
 * @param string $offset  (optional) default 0
 *
 * @return bool
 */
function group_forum_latest_post($groupid, $limit = 10, $offset = 0) {
	$group = get_entity($groupid);
	if (!$group) {
		return elgg_echo('group:notfound');
	}
	
	$options = array(
		'type' => 'object',
		'subtype' => 'groupforumtopic',
		'container_guid' => $groupid,
		'limit' => $limit,
		'offset' => $offset,
		'full_view' => false,
		'pagination' => false,
	);
	$content = elgg_get_entities($options);
	if($content) {
		foreach($content as $single ) {
			$post[$single->guid]['title'] = $single->title;
			$post[$single->guid]['description'] = $single->description;
			$post[$single->guid]['owner_guid'] = $single->owner_guid;
			$post[$single->guid]['container_guid'] = $single->container_guid;
			$post[$single->guid]['access_id'] = $single->access_id;
			$post[$single->guid]['time_created'] = $single->time_created;
			$post[$single->guid]['time_updated'] = $single->time_updated;
			$post[$single->guid]['last_action'] = $single->last_action;
		}
	}
	else {
		$post = elgg_echo('discussion:topic:notfound');
	}
	return $post;
} 
				
expose_function('group.forum.get_latest_post',
				"group_forum_latest_post",
				array('groupid' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get posts from a group",
				'GET',
				true,
				false);

/**
 * Web service get replies on a post
 *
 * @param string $postid GUID of the group
 * @param string $limit   (optional) default 10
 * @param string $offset  (optional) default 0
 *
 * @return bool
 */
function group_forum_get_reply($postid, $limit = 10, $offset = 0) {
	$group = get_entity($postid);
	$options = array(
		'guid' => $postid,
		'annotation_name' => 'group_topic_post',
		'limit' => $limit,
		'offset' => $offset,
	);
	$content = elgg_get_annotations($options);
	if($content) {
		foreach($content as $single ) {
			$post[$single->id]['value'] = $single->value;
			$post[$single->id]['name'] = $single->name;
			$post[$single->id]['enabled'] = $single->enabled;
			$post[$single->id]['owner_guid'] = $single->owner_guid;
			$post[$single->id]['entity_guid'] = $single->entity_guid;
			$post[$single->id]['access_id'] = $single->access_id;
			$post[$single->id]['time_created'] = $single->time_created;
			$post[$single->id]['name_id'] = $single->name_id;
			$post[$single->id]['value_id'] = $single->value_id;
			$post[$single->id]['value_type'] = $single->value_type;
		}
	}
	else {
		$post = elgg_echo('discussion:reply:noreplies');
	}
	return $post;
} 
				
expose_function('group.forum.get_reply',
				"group_forum_get_reply",
				array('postid' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get posts from a group",
				'GET',
				true,
				false);
				
/**
 * Web service post a reply
 *
 * @param string $username username
 * @param string $postid   GUID of post
 * @param string $text     text of reply
 *
 * @return bool
 */
function group_forum_save_reply($username, $postid, $text) {
	$entity_guid = (int) get_input('entity_guid');
	$return['success'] = false;
	if (empty($text)) {
		$return['message'] = elgg_echo('grouppost:nopost');
		return $return;
	}

	$topic = get_entity($postid);
	if (!$topic) {
		$return['message'] = elgg_echo('grouppost:nopost');
		return $return;
	}

	$user = get_user_by_username($username);
	if (!$user) {
		$return['message'] = elgg_echo('registration:usernamenotvalid');
		return $return;
	}

	$group = $topic->getContainerEntity();
	if (!$group->canWriteToContainer($user)) {
		$return['message'] = elgg_echo('groups:notmember');
		return $return;
	}

	$reply_id = $topic->annotate('group_topic_post', $text, $topic->access_id, $user->guid);
	if ($reply_id == false) {
		$return['message'] = elgg_echo('groupspost:failure');
		return $return;
	}
	
	add_to_river('river/annotation/group_topic_post/reply', 'reply', $user->guid, $topic->guid, "", 0, $reply_id);
	$return['success'] = true;
	return $return;
} 
				
expose_function('group.forum.save_reply',
				"group_forum_save_reply",
				array('username' => array ('type' => 'string'),
						'postid' => array ('type' => 'string'),
						'text' => array ('type' => 'string'),
					),
				"Post a reply to a group",
				'POST',
				true,
				false);
				
/**
 * Web service delete a reply
 *
 * @param string $username username
 * @param string $id       Annotation ID of reply
 *
 * @return bool
 */
function group_forum_delete_reply($username, $id) {
	$reply = elgg_get_annotation_from_id($id);
	$return['success'] = false;
	if (!$reply || $reply->name != 'group_topic_post') {
		$return['message'] = elgg_echo('discussion:reply:error:notdeleted');
		return $return;
	}
	
	$user = get_user_by_username($username);
	if (!$user) {
		$return['message'] = elgg_echo('registration:usernamenotvalid');
		return $return;
	}

	if (!$reply->canEdit($user->guid)) {
		$return['message'] = elgg_echo('discussion:error:permissions');
		return $return;
	}

	$result = $reply->delete();
	if ($result) {
		$return['success'] = true;
		$return['message'] = elgg_echo('discussion:reply:deleted');
		return $return;
	} else {
		$return['message'] = elgg_echo('discussion:reply:error:notdeleted');
		return $return;
	}
} 
				
expose_function('group.forum.delete_reply',
				"group_forum_delete_reply",
				array('username' => array ('type' => 'string'),
						'id' => array ('type' => 'string'),
					),
				"Delete a reply from a group",
				'POST',
				true,
				false);