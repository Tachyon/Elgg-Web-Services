<?php
/**
 * Unit tests for user web services
 */
class ElggWebServicesUserTest extends ElggCoreUnitTest {

	/**
	 * Called before each test object.
	 */
	public function __construct() {
		$this->ia = elgg_set_ignore_access(TRUE);
		parent::__construct();
		$this->user = new ElggUserTest();
		$this->user->username = 'test_username_' . rand(); 
		$this->user->email = 'test@test.org';
		$this->user->name = 'I am a Test User';
		$this->user->access_id = ACCESS_PUBLIC;
		$this->user->salt = generate_random_cleartext_password(); 
		$this->user->password = generate_user_password($this->user, "pass123");
		$this->user->save();
		$this->user->makeAdmin();
		// all __construct() code should come after here
		$this->user2 = new ElggUserTest();
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->client = new ElggApiClient(elgg_get_site_url(), '2dcbe06b8318d8b3ea72523f7135ae6edfcc75c1');
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
		elgg_set_ignore_access($this->ia);
		// all __destruct() code should go above here
		parent::__destruct();
	}

	public function testGetProfileFields() {
		$results = $this->client->get('user.get_profile_fields');
		$user_fields = elgg_get_config('profile_fields');
		
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($results->$key->label, elgg_echo('profile:'.$key));	
			$this->assertEqual($results->$key->type, $type);
		}
	}
	
	public function testSaveProfile() {
		$profile = array('description' => 'description test',
						'briefdescription' => 'briefdescription test',
						'location' => 'India',
						'interests' => 'my interest',
						'skills' => 'my skills',
						'contactemail' => 'myemail@email.com',
						'phone' => '01234567890',
						'mobile' => '11234567890',
						'website' => 'http://mywebsite.com',
						'twitter' => 'tweet',
						);
		$params = array('username' => $this->user->username,
						'profile' => $profile
						);	
		$user_fields = elgg_get_config('profile_fields');
		$results = $this->client->post('user.save_profile', $params);
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($profile[$key], $this->user->$key);
		}
	}
	
	public function testGetProfile() {
		$params = array('username' => $this->user->username);	
		$results = $this->client->get('user.get_profile', $params);
		$user_fields = elgg_get_config('profile_fields');
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($results->$key, $this->user->$key);
		}
	}
	
	public function testGetUserByEmail() {
		$params = array('email' => 'test@test.org');	
		$results = $this->client->get('user.get_user_by_email', $params);
		$found = 0;
		foreach ($results as $name) {
			if(strcmp($name,$this->user->username)==0) {
				$found = 1;
			}
		}
		if ( !$found ){
			assertTrue(false);
		}
	}
	
	public function testCheckUsernameAvailability() {
		$params = array('username' => $this->user->username);	
		$results = $this->client->get('user.check_username_availability', $params);
		$this->assertFalse($results);
		$params = array('username' => $this->user->username . rand());	
		$results = $this->client->get('user.check_username_availability', $params);
		$this->assertTrue($results);
	}
	
	public function testRegister() {
		$params = array('name' => 'I am test user 2',
						'email' => 'test2@test.org',
						'username' => 'test_username_' . rand(),
						'password' => 'pass123',
						);		
		$results = $this->client->get('user.register', $params);
		$this->assertTrue($results->success);
		$results = $this->client->get('user.register', $params);
		$this->assertFalse($results->success);
		$this->user2 = get_user_by_username($params['username']);
	}
	
	public function testFriendAdd() {
		$params = array('username' => $this->user->username,
						'friend' => $this->user2->username,
					);		
		$results = $this->client->post('user.friend.add', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('user.friend.add', $params);
		$this->assertFalse($results->success);
	}
	
	public function testGetFriends() {
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
					);				
		$results = $this->client->get('user.friend.get_friends', $params);
		$this->assertTrue($results->success);
		$this->assertEqual($results->friends[0]->username, $this->user2->username);
		
		$params = array('username' => $this->user2->username,
						'limit' => 1,
						'offset' => 0,
					);				
		$results = $this->client->get('user.friend.get_friends', $params);
		$this->assertFalse($results->success);
	}
	
	public function testGetFriendsOf() {
		$params = array('username' => $this->user->username,
						'limit' => 1,
						'offset' => 0,
					);				
		$results = $this->client->get('user.friend.get_friends_of', $params);
		$this->assertFalse($results->success);
		$params = array('username' => $this->user2->username,
						'limit' => 1,
						'offset' => 0,
					);				
		$results = $this->client->get('user.friend.get_friends_of', $params);
		$this->assertTrue($results->success);
		$this->assertEqual($results->friends[0]->username, $this->user->username);
	}
	
	public function testFriendRemove() {
		$params = array('username' => $this->user->username,
						'friend' => $this->user2->username,
					);		
		$results = $this->client->post('user.friend.remove', $params);
		$this->assertTrue($results->success);
		$results = $this->client->post('user.friend.remove', $params);
		$this->assertFalse($results->success);
	}
}
