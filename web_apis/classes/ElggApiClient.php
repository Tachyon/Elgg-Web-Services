<?php
/**
 * Elgg Web Services API Client
 *
 * @package ElggWebServices
 *
 * @uses curl
 */

class ElggApiClient {

	/**
	 * @var string The location of the Elgg site
	 */
	protected $siteUrl;

	/**
	 * @var string The API key for Elgg's web services
	 */
	protected $apiKey;

	/**
	 * @var string A token that allows the client to act for the user.
	 */
	protected $authToken;

	protected $httpStatus;
	protected $apiStatus;
	protected $lastApiCall;
	protected $lastDataFormat;
	protected $lastError;
	protected $lastErrorMsg;

	// not sure about these error codes
	const SUCCESS = 0;
	const BAD_HTTP_METHOD = 100;
	const NO_CURL = 101;
	const NO_STATUS = 102;

	/**
	 * Constructor
	 *
	 * @param string $siteUrl
	 * @param string $apiKey (optional)
	 */
	public function __construct($siteUrl, $apiKey = '') {

		if (!function_exists('curl_init')) {
			throw new Exception("The php curl library is required for ElggApiClient");
		}

		$this->apiKey = $apiKey;

		// check base url for trailing slash
		if (substr($siteUrl, -1) != '/') {
			$siteUrl .= '/';
		}
		$this->siteUrl = $siteUrl;
	}

	/**
	 * Gets the user auth token from the server
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function obtainAuthToken($username, $password) {
		if (!$username || !$password) {
			return false;
		}

		$params = array(
			'username' => $username,
			'password' => $password,
		);
		$token = $this->post('auth.gettoken', $params);
		if ($token) {
			$this->authToken = $token;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the Latest HTTP Status from an API Call
	 *
	 * @return int
	 */
	public function getLastHttpStatus() {
		return $this->httpStatus;
	}

	/**
	 * Get the Latest API Status
	 *
	 * @return int
	 */
	public function getLastApiStatus() {
		return $this->apiStatus;
	}

	/**
	 * Get the Latest API Call
	 *
	 * @return string
	 */
	public function getLastApiCall() {
		return $this->lastApiCall;
	}

	/**
	 * Get the Latest Error Code
	 *
	 * @return int
	 */
	public function getLastError() {
		return (int)$this->lastError;
	}

	/**
	 * Get the Latest Error Message from API
	 *
	 * @return string
	 */
	public function getLastErrorMessage() {
		return (string)$this->lastErrorMsg;
	}

	/**
	 * Perform a GET request
	 *
	 * @param string $method
	 * @param array  $params
	 * @return stdClass object with results
	 */
	public function get($method, $params = null) {
		return $this->apiCall($method, 'GET', $params);
	}

	/**
	 * Perform a POST request
	 *
	 * @param string $method
	 * @param array  $params
	 * @return stdClass object with results
	 */
	public function post($method, $params = NULL) {
		return $this->apiCall($method, 'POST', $params);
	}

	/**
	 * Perform a web services API call
	 *
	 * @param string $method
	 * @param string $httpMethod
	 * @param array  $params
	 * @return stdClass object with results or false for failure
	 */
	public function apiCall($method, $httpMethod, $params = NULL) {
		$httpMethod = strtoupper($httpMethod);

		if ($httpMethod != 'GET' && $httpMethod != 'POST') {
			$this->lastError = self::BAD_HTTP_METHOD;
			return false;
		}

		if (isset($params) && is_array($params)) {
			$encodedQuery = http_build_query($params);
		}

		// Build url
		$apiUrl = $this->siteUrl . "services/api/rest/json/";
		$apiUrl .= "?method=$method";
		if ($this->apiKey) {
			$apiUrl .= '&api_key=' . $this->apiKey;
		}

		// always include the auth token in case it is needed
		if (isset($this->authToken)) {
			$apiUrl .= "&auth_token=" . urlencode($this->authToken);
		}

		// GET
		if ($httpMethod == "GET" && isset($encodedQuery)) {
			$apiUrl .= '&' . $encodedQuery;
		}

		if (!function_exists('curl_init')) {
			$this->lastError = self::NO_CURL;
			return false;
		}

		// Initialize curl and setup api headers
		$curlHandle = curl_init();

		if ($httpMethod == 'POST') {
			curl_setopt($curlHandle, CURLOPT_POST, true);
			curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $encodedQuery);
		}

		curl_setopt($curlHandle, CURLOPT_URL, $apiUrl);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

		$userAgent = 'Elgg API Client 1.0 (curl) ' . phpversion();
		curl_setopt($curlHandle, CURLOPT_USERAGENT, $userAgent);

		$data = curl_exec($curlHandle);

		if (!$this->checkHttpStatus($curlHandle)) {
			curl_close($curlHandle);
			return false;
		}
		curl_close($curlHandle);

		$this->lastApiCall = $method;

		if (!$this->checkApiStatus($data)) {
			return false;
		}

		return $this->getResults($data);
	}

	/**
	 * Check the status on the current request
	 *
	 * @param resource $ch cURL handle
	 * @return bool
	 */
	protected function checkHttpStatus($ch) {
		$this->httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($this->httpStatus != 200) {
			return false;
		}

		return true;
	}

	/**
	 * Check the API status field for errors
	 *
	 * @param string $data JSON encoded string
	 * @return bool
	 */
	protected function checkApiStatus($data) {
		$obj = json_decode($data);
		if (!isset($obj->status)) {
			$this->lastError = self::NO_STATUS;
			return false;
		}

		$this->apiStatus = $obj->status;
		if ($this->apiStatus != 0) {
			$this->lastError = $this->apiStatus;
			$this->lastErrorMsg = $obj->message;
			return false;
		}

		return true;
	}

	/**
	 * Get the results of the API call
	 *
	 * @param string $data JSON encoded data
	 * @return mixed
	 */
	protected function getResults($data) {
		$obj = json_decode($data);
		return $obj->result;
	}
}
