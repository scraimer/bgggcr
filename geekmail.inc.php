<?php

require_once('config.inc.php');
require_once('http.inc.php');

/** 
 * $recipients = a comma-seperated list of BGG usernames
 * $subject = the subject line of the geekmail
 * $message = the content of the geekmail
 *
 * Returns TRUE on success and FALSE on failure.
 */
function geekmail_send( $recipients, $subject, $message )
{
	global $config;
	
	$url = "https://" . $config['bgg']['domain'] . "/geekmail_controller.php";
	$params = array(
		'action' => 'save',
		'messageid' => '',
		'touser' => $recipients,
		'subject' => $subject,
		'savecopy' => 1,
		'geek_link_select_1' => '',
		'sizesel' => 10,
		'body' => $message,
		'B1' => 'Send',
		'folder' => 'inbox',
		'label' => '',
		'ajax' => 1,
		'searchid' => 0,
		'pageID' => 1
	);

	$result = http_post( $url, $params, $config['bgg']['cookie'] );

	return ($result !== FALSE);
}

/** 
 * Use the geekmail 'preview' to convert BGG markup to HTML
 * 
 * $recipients = a comma-seperated list of BGG usernames
 * $subject = the subject line of the geekmail
 * $message = the content of the geekmail
 *
 * Returns TRUE on success and FALSE on failure.
 */
function geekmail_make_preview( $message )
{
	global $config;
	
	$url = "https://" . $config['bgg']['domain'] . "/geekmail_controller.php";
	$params = array(
		'action' => 'save',
		'messageid' => '',
		'touser' => $config['bgg']['username'],
		'subject' => 'Citizen Recognition Preview',
		'savecopy' => 1,
		'geek_link_select_1' => '',
		'sizesel' => 10,
		'body' => $message,
		'B1' => 'Preview',
		'label' => '',
		'ajax' => 1,
	);

	$result = http_post( $url, $params, $config['bgg']['cookie'] );

	return $result;
}

/** UNTESTED. Requires extra CSS and JS to make it work */
function geekmail_get_avatar( $username )
{
	$result = geekmail_make_preview( "[user=" . $username . "][/user]" );
	$content = $result['content'];
	$json = json_decode($content);
	$output = $json->{"output"};

	$subject_start = strpos( $output, ">Subject:" );
	$start = strpos( $output, "<div", $subject_start );
	$end = strpos( $output, "<form", $start );
	$roi = substr( $output, $start, $end - $start );

	return $roi;
}

?>
