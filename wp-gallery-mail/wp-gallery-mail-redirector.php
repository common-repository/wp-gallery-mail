<?php

/**
* wp-gallery-mail.php  V0.2
*
* Copyright (c) 2003-2005 The Wordpress Team
* Copyright (c) 2004-2005 - John B. Hewitt - jb@stcpl.com.au
* Copyright (c) 2004 - Dan Cech - dcech@lansmash.com
* Copyright (c) 2005 Ian Tomey
*
* Licensed under the GNU GPL. For full terms see the file COPYING.
*
* -= README =-
*
* A script that will redirect calls from MTAs and CRON back via the webserver,
* hopefully running Wordpress & Gallery as the correct user in the process
**/
	
	// Load up some libraries
	$ROOTDIR = realpath(dirname(__FILE__).'/../../..'); //area where wordpress is, i.e. /var/www/wordpress
	require($ROOTDIR . '/wp-config.php');

	$parsed = parse_url(get_settings('siteurl'));
	$wpUrlPath = $parsed['path'];

	// load configuration

	if ( !get_option('wgm-correct-config') )
		die ("Options not configured correctly, please see the options plugin page for wp-gallery-mail");
	
	$FILES_PATH = '/wp-content/'.get_option( 'wgm-filesPath' ).'/';
	$input_protocol = get_option( 'wgm-inputProtocol' );
			
	error_reporting(E_ERROR | E_PARSE);
	
	// if set up for smtp then we have been piped in the email, write it out to a file
	if ( $input_protocol == 1 ) {
		//Retreive email and write it to our directory for processing via the webserver
		$fname = '__wpgm_incoming_mail_'.rand();
		$outfilename = realpath( $ROOTDIR. $FILES_PATH) . '/' .$fname;
		$fin = fopen( "php://stdin", "r" );
		$fout = fopen( $outfilename, "wb" );
		while (!feof($fin)) {
			$data = fread($fin, 1024);
			fwrite( $fout, $data, strlen($data) );
		}
		fclose($fin);
		fclose($fout);
		chmod( $outfilename, 0777 );
		$callParams = "?email_file=$fname";
	}
	

$a = callScriptOnWebserver( $parsed['host'],  $wpUrlPath."/wp-content/plugins/wp-gallery-mail/wp-gallery-mail-processor.php$callParams" );

// finished, maybe check result at some later date

function callScriptOnWebserver($host, $request)  {
	$connection = fsockopen( $host,80 );

	if(!$connection)
		die("Unable to open connection to $host");

	$request  = "GET $request HTTP/1.1\r\n";
	$request .= "Host: $host\r\n";
	$request .= "Connection: close\r\n\r\n";

	fputs($connection, $request);
	while (!feof($connection))
		$result .= fgets ($connection,128);
	fclose( $connection );
	
	return $result;
}	
	
	
?>