<?php
/**
 * Unit tests for user web services
 */
class ElggWebServicesFileTest extends ElggCoreUnitTest {

	/**
	 * Called before each test object.
	 */
	public function __construct() {
		$this->ia = elgg_set_ignore_access(TRUE);
		parent::__construct();
		$this->user = new ElggUser();
		$this->user->username = 'test_username_' . rand(); 
		$this->user->email = 'test@test.org';
		$this->user->name = 'I am a Test User';
		$this->user->access_id = ACCESS_PUBLIC;
		$this->user->salt = generate_random_cleartext_password(); 
		$this->user->password = generate_user_password($this->user, "pass123");
		$this->user->container_guid = 0;
		$this->user->owner_guid = 0;
		$this->user->save();
		// all __construct() code should come after here
		$this->user2 = new ElggUser();
		$this->user2->username = 'test_username_' . rand(); 
		$this->user2->container_guid = 0;
		$this->user2->owner_guid = 0;
		$this->user2->access_id = ACCESS_PUBLIC;
		$this->user2->save();
		
		$this->file = new ElggFile();
		$this->file->title = 'This is a test file';
		$this->file->owner_guid = $this->user->guid;
		$this->file->access_id = ACCESS_PUBLIC;
		$this->file->save();
		
		$this->user2->addFriend($this->user->guid);
		
		// generating API key
		$site = elgg_get_config('site');
		$keypair = create_api_user($CONFIG->site_id);
		if ($keypair)
		{
			$this->apikey = new ElggObject();
			$this->apikey->subtype = 'api_key';
			$this->apikey->access_id = ACCESS_PUBLIC;
			$this->apikey->title = "File web services";
			$this->apikey->public = $keypair->api_key;
			$this->apikey->save();
		}
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->client = new ElggApiClient(elgg_get_site_url(), $this->apikey->public);
		$result = $this->client->obtainAuthToken($this->user->username, 'pass123');
		if (!$result) {
		   echo "Error in getting auth token!\n";
		}
	}

	/**
	 * Called after each test method.
	 */
	public function tearDown() {
		// do not allow SimpleTest to interpret Elgg notices as exceptions
		$this->swallowErrors();
	}

	/**
	 * Called after each test object.
	 */
	public function __destruct() {
		$this->user->delete();
		$this->user2->delete();
		//$this->file->delete();
		$this->apikey->delete();
		elgg_set_ignore_access($this->ia);
		// all __destruct() code should go above here
		parent::__destruct();
	}

	public function testGetFiles() {
		$params = array('username' => $this->user->username,
					  'limit' => 1,
					  'offset' => 0,
					);
		$results = $this->client->get('file.get_files', $params);
		foreach($results as $guid => $file) {
			$this->assertEqual($results->$guid->title, $this->file->title);
		}
		$params = array('username' => $this->user2->username,
					  'limit' => 1,
					  'offset' => 0,
					);
		$results = $this->client->get('file.get_files', $params);
		foreach($results as $guid => $file) {
			$this->assertEqual($results->error->message, elgg_echo('file:none'));
		}
		$params = array('username' => 0,
					  'limit' => 1,
					  'offset' => 0,
					);
		$results = $this->client->get('file.get_files', $params);
		foreach($results as $guid => $file) {
			$this->assertEqual($results->$guid->title, $this->file->title);
		}
	}
	
	public function testGetFilesByFriend() {
		$params = array('username' => $this->user2->username,
					  'limit' => 1,
					  'offset' => 0,
					);
		$results = $this->client->get('file.get_files_by_friend', $params);
		foreach($results as $guid => $file) {
			$this->assertEqual($results->$guid->title, $this->file->title);
		}
		$params = array('username' => $this->user->username,
					  'limit' => 1,
					  'offset' => 0,
					);
		$results = $this->client->get('file.get_files_by_friend', $params);
		foreach($results as $guid => $file) {
			$this->assertEqual($results->error->message, elgg_echo('file:none'));
		}
	}
	
}
