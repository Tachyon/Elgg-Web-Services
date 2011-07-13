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

		// all __construct() code should come after here
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->client = new ElggApiClient(elgg_get_site_url());
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
}
