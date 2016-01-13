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

?>
