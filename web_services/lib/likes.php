<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
 
 /**
 * Web service to like an entity
 *
 * @param string $entity_guid guid of object to like
 *
 * @return bool
 */
function rest_likes_add($entity_guid) {
	if (elgg_annotation_exists($entity_guid, 'likes')) {
		return elgg_echo("likes:alreadyliked");
	}
	// Let's see if we can get an entity with the specified GUID
	$entity = get_entity($entity_guid);
	if (!$entity) {
		return elgg_echo("likes:notfound");
	}

	// limit likes through a plugin hook (to prevent liking your own content for example)
	if (!$entity->canAnnotate(0, 'likes')) {
		return elgg_echo("likes:notallowed");
	}

	$user = elgg_get_logged_in_user_entity();
	$annotation = create_annotation($entity->guid,
									'likes',
									"likes",
									"",
									$user->guid,
									$entity->access_id);

	// tell user annotation didn't work if that is the case
	if (!$annotation) {
		return elgg_echo("likes:failure");
	}
	add_to_river('annotation/annotatelike', 'likes', $user->guid, $entity->guid, "", 0, $annotation);
	return elgg_echo("likes:likes");
} 
				
expose_function('likes.add',
				"rest_likes_add",
				array('entity_guid' => array ('type' => 'int'),
					),
				"Add a like",
				'POST',
				true,
				false);
				
/**
 * Web service to unlike an entity
 *
 * @param string $entity_guid guid of object to like
 *
 * @return bool
 */
function rest_likes_delete($entity_guid) {
	$likes = elgg_get_annotations(array(
		'guid' => $entity_guid,
		'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
		'annotation_name' => 'likes',
	));
	if ($likes) {
		if ($likes[0]->canEdit()) {
			$likes[0]->delete();
			return elgg_echo("likes:deleted");
		}
	}

	return elgg_echo("likes:notdeleted");
} 
				
expose_function('likes.delete',
				"rest_likes_delete",
				array('entity_guid' => array ('type' => 'int'),
					),
				"Delete a like",
				'POST',
				true,
				false);
				
