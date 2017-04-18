<?php
/**
 * Web services unit tests
 */

echo 'fff';

echo '<div>';
echo elgg_echo('web_apis:tests:instructions');
echo '</div>';

echo '<div>';
echo elgg_view('output/url', array(
	'href' => 'action/web_apis/run_tests',
	'text' => elgg_echo('web_apis:tests:run'),
	'is_action' => true,
	'class' => 'elgg-button elgg-button-submit',
));
echo '</div>';
