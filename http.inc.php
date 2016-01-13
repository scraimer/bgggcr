<?php

require_once('config.inc.php');

function http_post( $url, $params, $cookie = NULL )
{
	$headers = array(
		'Content-type: application/x-www-form-urlencoded'
	);
	
	if( $cookie )
	{
		array_push( $headers, 'Cookie: ' . $cookie );
	}

	$options = array(
		'http' => array(
		   'header'  => $headers,
        	'method'  => 'POST',
        	'content' => http_build_query( $params ),	
		),
	);

	$context = stream_context_create( $options );
	$stream = fopen( $url, 'r', false, $context );

	$meta = stream_get_meta_data( $stream );
	$result = array(
		'headers' => $meta['wrapper_data'],
		'meta' => $meta,
		'content' => stream_get_contents( $stream ),
	);

	return $result;
}

?>
