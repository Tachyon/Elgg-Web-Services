<?php
/**
 * Unit tests for user web services
 */
class ElggWebServicesCoreTest extends ElggCoreUnitTest {

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
	
	public function testSiteTest() {
		$results = $this->client->get('site.test');
		$this->assertEqual($results->success, true);
		$this->assertEqual($results->message, "Hello");
	}

	public function testSiteGetinfo() {
		$results = $this->client->get('site.getinfo');
		$site = elgg_get_config('site');
		$this->assertEqual($results->url, elgg_get_site_url());
		$this->assertEqual($results->sitename, $site->name);
		$this->assertEqual($results->language, elgg_get_config('language'));
	}
}
