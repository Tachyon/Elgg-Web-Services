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
		$username = "unittest";
		$password = "unittest";
		$name = "I am a Test User";
		$email = "unittest@elgg.org";
		register_user($username, $password, $name, $email);
		// all __construct() code should come after here
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->client = new ElggApiClient(elgg_get_site_url(), '2dcbe06b8318d8b3ea72523f7135ae6edfcc75c1');
		$result = $this->client->obtainAuthToken('unittest', 'unittest');
		if (!$result) {
		   echo "Wrror in getting auth token!\n";
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
		$user = get_user_by_username('unittest');
		var_dump($user->delete());
		
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
		$params = array('username' => 'unittest',
						'profile' => $profile
						);	
		$user = get_user_by_username('unittest');
		$user_fields = elgg_get_config('profile_fields');
		
		$results = $this->client->post('user.updateprofile', $params);
		
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($profile[$key], $user->$key);
		}
	}
	
	public function testGetProfile() {
		$params = array('username' => 'unittest');	
		$results = $this->client->get('user.getprofile', $params);
		$user = get_user_by_username('unittest');
		$user_fields = elgg_get_config('profile_fields');
		foreach ($user_fields as $key => $type) {
			$this->assertEqual($results->$key, $user->$key);
		}
	}
}
