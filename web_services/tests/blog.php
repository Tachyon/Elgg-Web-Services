<?php
/**
 * Unit tests for user web services
 */
class ElggWebServicesBlogTest extends ElggCoreUnitTest {

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
		
		$this->blog['title'] = 'Blog Title';
		$this->blog['text'] = 'text of test blog post';
		$this->blog['excerpt'] = 'excerpt of test blog post';
		$this->blog['tags'] = 'tags1, tags1';
		$this->blog['access'] = 2;
		
		$this->user2->addFriend($this->user->guid);
		
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
		$this->user2->delete();
		$this->apikey->delete();
		elgg_set_ignore_access($this->ia);
		// all __destruct() code should go above here
		parent::__destruct();
	}

	public function testSavePost() {
		$params = array('username' => $this->user->username,
						'title' => $this->blog['title'],
						'text' => $this->blog['text'],
						'excerpt' => $this->blog['excerpt'],
						'tags' => $this->blog['tags'],
						'access' => $this->blog['access'],
						);
		$results = $this->client->post('blog.save_post', $params);
		$this->assertTrue($results->success);
	}
	
	public function testGetLatestPosts() {
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
						);
		$result = $this->client->get("blog.get_latest_posts", $params);
		$this->assertNull($results->error);
	}
	
	public function testGetPost() {
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
						);
		$result = $this->client->get("blog.get_latest_posts", $params);
		foreach($result as $guid => $post) {
			$params = array('guid' => $guid,
							'username' => $this->user->username,
							);
		}
		$result = $this->client->get("blog.get_post", $params);
		$this->assertEqual($result->title, $this->blog['title']);
		$this->assertEqual($result->content, $this->blog['text']);
		$this->assertEqual($result->excerpt, $this->blog['excerpt']);
		$this->assertEqual($result->tags, $this->blog['tags']);
		$this->assertEqual($result->access_id, $this->blog['access']);
	}
	
	public function testGetFriendsPosts() {
		$params = array('username' => $this->user2->username,
						'limit' => 1,
						'offset' => 0,
						);
		$result = $this->client->get("blog.get_friends_posts", $params);
		foreach($result as $guid => $post) {
			$this->assertEqual($result->$guid->title, $this->blog['title']);
			$this->assertEqual($result->$guid->content, $this->blog['text']);
			$this->assertEqual($result->$guid->excerpt, $this->blog['excerpt']);
			$this->assertEqual($result->$guid->tags, $this->blog['tags']);
			$this->assertEqual($result->$guid->access_id, $this->blog['access']);
		}
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
						);
		$result = $this->client->get("blog.get_friends_posts", $params);
		$this->assertEqual($result->error->message , elgg_echo("blog:message:noposts"));
	}
	
	public function testDeletePost() {
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
						);
		$result = $this->client->get("blog.get_latest_posts", $params);
		foreach($result as $guid => $post) {
			$params = array('guid' => $guid,
							'username' => $this->user->username,
							);
		}
		$result = $this->client->post("blog.delete_post", $params);
		$this->assertTrue($result->success);
	}
	
}
