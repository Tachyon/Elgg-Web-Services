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
function rest_group_join($username, $groupid, $password) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$group = get_entity($groupid);
	
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
				return elgg_echo("groups:joined");
			} else {
				return elgg_echo("groups:cantjoin");
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
				return elgg_echo("groups:joinrequestmade");
			} else {
				return elgg_echo("groups:joinrequestnotmade");
			}
		}
	} else {
		return elgg_echo("groups:cantjoin");
	}
} 
				
expose_function('group.join',
				"rest_group_join",
				array('username' => array ('type' => 'string'),
						'groupid' => array ('type' => 'string'),
					),
				"Join a group",
				'GET',
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
function rest_group_leave($username, $groupid) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$group = get_entity($groupid);
	
	set_page_owner($group->guid);
	if (($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
		if ($group->getOwnerGUID() != elgg_get_logged_in_user_guid()) {
			if ($group->leave($user)) {
				return elgg_echo("groups:left");
			} else {
				return elgg_echo("groups:cantleave");
			}
		} else {
			return elgg_echo("groups:cantleave");
		}
	} else {
		return elgg_echo("groups:cantleave");
	}
} 
				
expose_function('group.leave',
				"rest_group_leave",
				array('username' => array ('type' => 'string'),
						'groupid' => array ('type' => 'string'),
					),
				"leave a group",
				'GET',
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
function rest_group_post($username, $groupid, $title, $desc, $tags = "", $status = "published", $access_id = ACCESS_DEFAULT) {
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$group = get_entity($groupid);
	if (!$group) {
		register_error(elgg_echo('group:notfound'));
		forward();
	}

	// make sure user has permissions to write to container
	if (!can_write_to_container($user->guid, $groupid, "all", "all")) {
		return elgg_echo('groups:permissions:error');
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
		return elgg_echo('discussion:error:notsaved');
	}
	return elgg_echo('discussion:topic:created');
} 
				
expose_function('group.post',
				"rest_group_post",
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
