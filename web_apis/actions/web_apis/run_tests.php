<?php
/**
 * Run the web services unit tests
 */

elgg_set_context('web_apis');

$test_runner = elgg_get_root_path() . 'engine/tests/suite.php';

require $test_runner;

exit;
