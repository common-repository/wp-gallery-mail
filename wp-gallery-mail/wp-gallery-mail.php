<?php
/*
Plugin Name: wp-gallery-mail
Plugin URI: http://www.iantomey.com/categories/code/wordpress-gallery-mail/
Description: A plugin that allows posting from emails and storing images in the emails in Gallery albums. <a href="../wp-content/plugins/wp-gallery-mail/docs/README-wp-gallery-mail.html">Documentation</a> * <a href="admin.php?page=wp-gallery-mail/optionsPage.php">Plugin Options Page</a>
Version: 0.4
Author: Ian Tomey
Author URI: http://www.iantomey.com	
*/

/*  Copyright 2005  Ian Tomey

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


function wgmAddOptionsPage() {
	if (function_exists('add_options_page'))
		add_options_page('wp-gallery-mail', 'wp-gallery-mail', 8,dirname(__FILE__ ).'/optionsPage.php');
	}

function wgmAddHead() {
	if ( !get_option( 'wgm-generate-styles' ) )
		return;
	print "<style type=\"text/css\">\n";
	print "<!--\n";
	print get_option('wgm-default-styles');
	print "-->\n";
	print "</style>\n";
}

$defaultStyles = <<<EOD
.thumbFromGallery{
	padding: 1px;
	margin: 2px;
	border: 1px solid black;
}

.galleryCentreBlock {
	text-align: center;
	padding: 6px;
	border: 1px solid #bbbbbb;
	background-color: #dddddd;
	margin-bottom: 3px;
}

.galleryCentreImage {
	max-width: 450px;
	width: expression(this.width > 450 ? 450: true);
	padding: 1px;
	border: 1px solid black;
}
EOD;

add_option( 'wgm-galleryDir',  realpath( dirname(__FILE__).'/../../..').'/gallery', 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-defaultAlbum', '', 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-inputProtocol', 0, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-filesPath', '', 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-defaultPosterID', 1, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-linkImageToPost', 1, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-defaultTitle', 'Emailed Post', 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-dropSignature', 1, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-generate-styles', 1, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-default-styles', $defaultStyles, 'wp-gallery-mail plugin setting', 'no' );
add_option( 'wgm-imageAlignment', 0, 'wp-gallery-mail plugin setting', 'no' );

add_option( 'wgm-correct-config', 0, 'wp-gallery-mail plugin setting', 'no' );

add_action('admin_menu', 'wgmAddOptionsPage');
add_action('wp_head', 'wgmAddHead');


?>