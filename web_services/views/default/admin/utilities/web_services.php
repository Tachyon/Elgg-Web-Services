<?php
/**
 * Web services unit tests
 */

echo '<div>';
echo elgg_echo('web_services:tests:instructions');
echo '</div>';

echo '<div>';
echo elgg_view('output/url', array(
	'href' => 'action/web_services/run_tests',
	'text' => elgg_echo('web_services:tests:run'),
	'is_action' => true,
	'class' => 'elgg-button elgg-button-submit',
));
echo '</div>';
