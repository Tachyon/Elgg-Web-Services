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
		$this->user = new ElggUser();
		$this->user->username = 'test_username_' . rand(); 
		$this->user->email = 'test@test.org';
		$this->user->name = 'I am a Test User';
		$this->user->access_id = ACCESS_PUBLIC;
		$this->user->salt = generate_random_cleartext_password(); 
		$this->user->password = generate_user_password($this->user, "pass123");
		$this->user->save();
		$this->user->makeAdmin();
		// all __construct() code should come after here
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
		
		elgg_set_ignore_access($this->ia);
		// all __destruct() code should go above here
		parent::__destruct();
	}

	public function testGetProfileLabels() {
		$results = $this->client->get('user.profilelabels');
		$user_fields = elgg_get_config('profile_fields');
		
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($results->$key->label, elgg_echo('profile:'.$key));	
			$this->assertEqual($results->$key->type, $type);
		}
	}
	
	public function testUpdateProfile() {
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
		$results = $this->client->post('user.updateprofile', $params);
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($profile[$key], $this->user->$key);
		}
	}
	
	public function testGetProfile() {
		$params = array('username' => $this->user->username);	
		$results = $this->client->get('user.getprofile', $params);
		$user_fields = elgg_get_config('profile_fields');
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($results->$key, $this->user->$key);
		}
	}
}
