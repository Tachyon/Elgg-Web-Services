<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Web service to get file list by all users
 *
 * @param string $limit  (optional) default 10
 * @param string $offset (optional) default 0
 *
 * @return array $file Array of files uploaded
 */
function file_get_files($username, $limit = 10, $offset = 0) {	
	if($username) {
		$user = get_user_by_username($username);
		if (!$user) {
			throw new InvalidParameterException('registration:usernamenotvalid');
		}
		$params = array(
			'types' => 'object',
			'subtypes' => 'file',
			'owner_guid' => $user->guid,
			'limit' => $limit,
			'full_view' => FALSE
		);
	} else {
		$params = array(
			'types' => 'object',
			'subtypes' => 'file',
			'limit' => $limit,
			'full_view' => FALSE
		);
	}
	$latest_file = elgg_get_entities($params);
	if($latest_file) {
		foreach($latest_file as $single ) {
			$file[$single->guid]['title'] = $single->title;
			$file[$single->guid]['owner_guid'] = $single->owner_guid;
			$file[$single->guid]['container_guid'] = $single->container_guid;
			$file[$single->guid]['access_id'] = $single->access_id;
			$file[$single->guid]['time_created'] = $single->time_created;
			$file[$single->guid]['time_updated'] = $single->time_updated;
			$file[$single->guid]['last_action'] = $single->last_action;
		}
	}
	else {
		$file['error']['message'] = elgg_echo('file:none');
	}
	return $file;
}
	
expose_function('file.get_files',
				"file_get_files",
				array('username' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get file uploaded by all users",
				'GET',
				false,
				false);
				
/**
 * Web service to get file list by friends
 *
 * @param string $username  username
 * @param string $limit     (optional) default 10
 * @param string $offset    (optional) default 0
 *
 * @return array $file Array of files uploaded
 */
function file_get_files_by_friend($username, $limit = 10, $offset = 0) {	
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$latest_file = get_user_friends_objects($user->guid, 'file', $limit, $offset);
	if($latest_file) {
		foreach($latest_file as $single ) {
			$file[$single->guid]['title'] = $single->title;
			$file[$single->guid]['owner_guid'] = $single->owner_guid;
			$file[$single->guid]['container_guid'] = $single->container_guid;
			$file[$single->guid]['access_id'] = $single->access_id;
			$file[$single->guid]['time_created'] = $single->time_created;
			$file[$single->guid]['time_updated'] = $single->time_updated;
			$file[$single->guid]['last_action'] = $single->last_action;
		}
	}
	else {
		$file['error']['message'] = elgg_echo('file:none');
	}
	return $file;
}
	
expose_function('file.get_files_by_friend',
				"file_get_files_by_friend",
				array('username' => array ('type' => 'string'),
					  'limit' => array ('type' => 'int', 'required' => false),
					  'offset' => array ('type' => 'int', 'required' => false),
					),
				"Get file uploaded by friends",
				'GET',
				true,
				false);

function file_upload_file($fieldname, $description)
{
	$result = array();
	$result["code"] = $_FILES[$fieldname]['error'];

	// Check if the file upload had any error
	if ($result["code"] !=  UPLOAD_ERR_OK)
	{
		$result["message"] = "There was an error in the file upload.";
		return $result;
	}
	$filename = $fieldname;

	$file = new ElggFile();

	if (!$file)
	{
		$result["message"] = "File uploaded, but unable to create a FileObject.";
		return $result;
	}

	$owner_guid = elgg_get_logged_in_user_guid();
	$file->title = $filename;
	$file->description = $description;
	$fileStoreName = elgg_strtolower(time() . $filename);
	$file->setFilename($fileStoreName);
	$mime_type = ElggFile::detectMimeType($_FILES[$fieldname]['tmp_name'], $_FILES[$fieldname]['type']);
	$file->setMimeType($mime_type);
	$file->originalfilename = $_FILES[$fieldname]['name'];

	//"Touch" the fileStoreName file (creates if non-existent)
	$fh = $file->open("write");
	$file->close();

	move_uploaded_file($_FILES[$fieldname]['tmp_name'], $file->getFilenameOnFilestore());

	$guid = $file->save();

	$result["guid"] = $file->getGUID();

	//TODO Create thumbnails
	return $result;
}

expose_function('file.upload_file',
				"file_upload_file",
				array('fieldname' => array ('type' => 'string'),
					  'description' => array ('type' => 'string'),
					),
				"Upload a file as multi-part form data and specify the key used to set post data. File will be saved as same name as the key. Returns the GUID of the ElggFile",
				'POST',
				false,
				true);


function get_file($file_guid)
{
	global $CONFIG;
	$file_obj = get_entity($file_guid);
	// getFilenameOnFilestore will return /var/www/<data_dir>/path/to/file
	$full_path = $file_obj->getFilenameOnFilestore();
	
	//TODO: Check if $full_path is valid or not
	$exploded = explode("elgg_data", $full_path);
	$rel_path = $exploded[1];
	$result['file_url'] = dirname($CONFIG->url) . '/' . basename($CONFIG->dataroot) . $rel_path;
	return $result;
}

expose_function('file.download_file',
				"get_file",
				array('file_guid' => array ('type' => 'string')),
				"Get the URL of the file rep by file_guid",
				'GET',
				false,
				false);
