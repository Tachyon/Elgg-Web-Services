<?php
/**
 * Unit tests for user web services
 */
class ElggWebServicesGroupTest extends ElggCoreUnitTest {

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
		
		$this->group = new ElggGroup();
		$this->group->membership = ACCESS_PUBLIC;
		$this->group->access_id = ACCESS_PUBLIC;
		$this->group->save();
		
		$this->forum['title'] = "Test title post";
		$this->forum['desc'] = "Content of test post";
		$this->forum['guid'] = "";
		
		// generating API key
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
		$this->group->delete();
		$this->apikey->delete();
		elgg_set_ignore_access($this->ia);
		// all __destruct() code should go above here
		parent::__destruct();
	}

	public function testGroupJoin() {
		$params = array('username' => $this->user->username,
						'groupid' => $this->group->guid,
						);
		$results = $this->client->post('group.join', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('group.join', $params);
		$this->assertFalse($results->success);
	}
	
	public function testGroupSavePost() {
		$params = array('username' => $this->user->username,
						'groupid' => $this->group->guid,
						'title' => $this->forum['title'],
						'desc' => $this->forum['desc'],
					);
		$results = $this->client->post('group.forum.save_post', $params);
		$this->assertTrue($results->success);
	}
	
	public function testGroupLatestPost() {
		$params = array('groupid' => $this->group->guid,
						'limit' => 1,
						'offset' => 0,
						);
		$results = $this->client->get('group.forum.get_latest_post', $params);
		foreach($results as $guid => $post) {
			$this->forum['guid'] = $guid;
			$this->assertEqual($results->$guid->title, $this->forum['title']);
			$this->assertEqual($results->$guid->description, $this->forum['desc']);
		}
	}
	
	public function testGroupSaveReply() {
		$this->forum['reply'] = 'This is a test reply';
		$params = array('username' => $this->user->username,
						'postid' => $this->forum['guid'],
						'text' => $this->forum['reply'],
						);
		$results = $this->client->post('group.forum.save_reply', $params);
		$this->assertTrue($results->success);
	}
	
	public function testGroupGetReply() {
		$params = array('postid' => $this->forum['guid'],
						'limit' => 1,
						'offset' => 0,
						);
		$results = $this->client->get('group.forum.get_reply', $params);
		foreach($results as $id => $post) {
			$this->forum['replyid'] = $id;
			$this->assertEqual($results->$id->value, $this->forum['reply']);
		}
	}
	
	public function testGroupDeleteReply() {
		$params = array('username' => $this->user->username,
						'id' => $this->forum['replyid'],
						);
		$results = $this->client->post('group.forum.delete_reply', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('group.forum.delete_reply', $params);
		$this->assertFalse($results->success);
		
	}
	
	public function testGroupDeletePost() {
		$params = array('username' => $this->user->username,
						'topicid' => $this->forum['guid'],
						);
		$results = $this->client->post('group.forum.delete_post', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('group.forum.delete_post', $params);
		$this->assertFalse($results->success);
	}
	
	public function testGroupLeave() {
		$params = array('username' => $this->user->username,
						'groupid' => $this->group->guid,
						);
		$results = $this->client->post('group.leave', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('group.leave', $params);
		$this->assertFalse($results->success);
	}
	
}
