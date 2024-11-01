<?
if ( isset( $_REQUEST['Submit']) ) {
	update_option( 'wgm-galleryDir', $_REQUEST['wgm-galleryDir'] );
	update_option( 'wgm-defaultAlbum', $_REQUEST['wgm-defaultAlbum'] );
	update_option( 'wgm-inputProtocol', $_REQUEST['wgm-inputProtocol'] );
	update_option( 'wgm-filesPath', $_REQUEST['wgm-filesPath'] );
	update_option( 'wgm-linkImageToPost', $_REQUEST['wgm-linkImageToPost'] );
	update_option( 'wgm-defaultTitle', $_REQUEST['wgm-defaultTitle'] );
	update_option( 'wgm-dropSignature', $_REQUEST['wgm-dropSignature'] );
	update_option( 'wgm-defaultPosterID', $_REQUEST['wgm-defaultPosterID'] );
	update_option( 'wgm-default-styles', $_REQUEST['wgm-default-styles'] );
	update_option( 'wgm-generate-styles', $_REQUEST['wgm-generate-styles'] );
	update_option( 'wgm-imageAlignment', $_REQUEST['wgm-imageAlignment'] );
}

//validate options
$correctConfig = true;
$errors = array();

//access gallery?
$galleryCode = get_settings('wgm-galleryDir').'/init.php';
if ( !@file_exists($galleryCode) ) 
	$errors['galleryDir'] = "This does not appear to be a Gallery directory";
else if ( !@is_readable($galleryCode) )
	$errors['galleryDir'] = "Unable to read init.php from the gallery directory";
else {
	include( $galleryCode );
	if ( !isset($gallery) )
			$errors['galleryDir'] = "Unable to establish contact with Gallery";
	$albums = getGalleryAlbums();
	//default album exists?
	if ( !isset($albums[get_option('wgm-defaultAlbum')])  ){
		//no? set to 1st album
		$first = each($albums);
		update_option( 'wgm-defaultAlbum',  $first['key'] );
	}
	if ( count($albums) == 0 )
		$errors['hidden1']='*';
}

//access file directory?
$wpContent = realpath( dirname(__FILE__).'/../..').'/'.get_settings('wgm-filesPath');
if ( !is_dir($wpContent) )
	$errors['filesPath'] = "This does does not exist or does not appear to be a directory";
else {
	$tmpfname = $wpContent.'/__wpgallerymail__test_write_file';
	$fd = @fopen($tmpfname, 'w');
	if ($fd==FALSE) 
	$errors['filesPath'] = "Unable to create files in this directory. Please check the directory permissions (you may need to change them to 0777)";
	else {
		fclose($fd);
		unlink($tmpfname);
	}
}

update_option( 'wgm-correct-config', count($errors)==0 );

// print any messages on a field
function printMessages( $field ) {
	global $errors;
	if ( $errors[$field]!='' )
		print "<div style=\"color: red; font-weight: bold; padding-top: 2px;\">$errors[$field]</div>";
}

//return a list of all the albums in the gallery
function getGalleryAlbums() {
	$albumDB = new AlbumDB(FALSE);
	$albums=array();
	
	foreach ($albumDB->albumList as $album) {
		if ( $album->isRoot()) {
			$albums[$album->fields[name]] = $album->fields[title];
			appendNestedAlbums(0, $album->fields[name], $albums);
		}
	}
	return $albums;
}

function appendNestedAlbums($level, $albumName, &$albums) {
    global $gallery;
 
    $myAlbum = new Album();
    $myAlbum->load($albumName);
    $numPhotos = $myAlbum->numPhotos(1);

    for ($i=1; $i <= $numPhotos; $i++) {
        if ($myAlbum->isAlbum($i)) {
            $myName = $myAlbum->getAlbumName($i);
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
			$albums[$myName] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp; ", $level+1).$nestedAlbum->fields[title];
			appendNestedAlbums($level + 1, $myName, $albums);
        }
    }
}

?>

<div class="wrap"> 
<h2>WP-Gallery-Mail Options</h2> 
<p>For more information, please see the <a href="../wp-content/plugins/wp-gallery-mail/docs/README-wp-gallery-mail.html">documentation</a>.</p> <?
if ( count($errors)>0 )
	print "<p style=\"font-weight: bold; color:red\">There are errors on this form, email processing cannot happen until these errors are fixed</p>";
?> <form name="myform" method="post" action="<?= $_SERVER['PHP_SELF'].'?page='.$_REQUEST['page']?>"> 
	<fieldset class="options"> 
	<legend>General</legend> 
	<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Gallery Directory</th> 
			<td>
				<? printMessages('galleryDir'); ?>
				<input type="text" name="wgm-galleryDir" id="wgm-galleryDir" value="<?php form_option('wgm-galleryDir'); ?>" size="60" />
				<br />The local directory on the server where Gallery is installed
				<br />Wordpress is installed at <em><?= realpath( dirname(__FILE__).'/../../..')?></em>
			</td>
		</tr>
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Default Album</th> 
			<td>
				<? 
				if ( count($albums) == 0 )
					print "<span style=\"color: red; font-weight: bold;\">There are no albums in the Gallery! Add some albums.</span>";
				else if ( $errors['galleryDir']!='' )
					print "<span style=\"color: red;\">Unable to select default album until the Gallery installation is correctly located. Set the Gallery directory above and press 'Update Options' to retrieve your album list.</span>";
				else { 
					print "<select name=\"wgm-defaultAlbum\" id=\"defaultAlbum\">";
					foreach ($albums as $album=>$name) {
							if ( $album == get_settings('wgm-defaultAlbum')) $selected = " selected='selected'";
						else $selected = '';
						echo "\n\t<option value='$album' $selected>$name</option>";
					} 
					print "</select><br />If no Gallery album is specified in the email, place the images in this album";
				}?>
			</td>
		</tr>
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Email Input Method</th> 
			<td>
				<select name="wgm-inputProtocol" id="wgm-inputProtocol" > 
					<option value="0" <?php selected(0, get_settings('wgm-inputProtocol')); ?>>POP3 - For being called manually or from a CRON job</option> 
					<option value="1" <?php selected(1, get_settings('wgm-inputProtocol')); ?>>SMTP - For being called from an email server when mail is delivered</option> 
				</select>
				<br />If set to POP3, you need to set up the <a href="options-writing.php">Writing by Email</a> section with the mailbox details.
				<? printMessages('inputProtocol'); ?>
			</td> 
		</tr> 

		<tr valign="middle"> 
			<th width="33%" scope="row">File Storage Directory</th> 
			<td>
				<? printMessages('filesPath'); ?>
				<input type="text" name="wgm-filesPath" id="wgm-filesPath" value="<?php form_option('wgm-filesPath'); ?>" size="40" />
				<br />This directory, under wp-content will store emailed files and provide a temporary space for emailed images. Can be left blank to just use wp-content.
			</td>
		</tr>

		<tr valign="middle"> 
			<th width="33%" scope="row">Link Image to Blog Post</th> 
			<td>
				<input name="wgm-linkImageToPost" type="checkbox" id="wgm-linkImageToPost" value="1" <?php checked('1', get_settings('wgm-linkImageToPost')); ?>/>
				<br />Adds a link back to the blog entry inside the Gallery image description
				<? printMessages('linkImageToPost'); ?>
			</td>
		</tr>
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Default Post Title</th> 
			<td>
				<input type="text" name="wgm-defaultTitle" id="wgm-defaultTitle" value="<?php form_option('wgm-defaultTitle'); ?>" size="60" />	
				<br />Will be used if no title is specified in the subject of the post
				<? printMessages('defaultTitle'); ?>
			</td>
		</tr>
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Drop Signature?</th> 
			<td>
				<input name="wgm-dropSignature" type="checkbox" id="wgm-dropSignature" value="1" <?php checked('1', get_settings('wgm-dropSignature')); ?>/>
				<br />Removes all content after a line beginning with --
				<? printMessages('dropSignature'); ?>
			</td>
		</tr>
		
		<tr valign="middle"> 
			<th width="33%" scope="row">Default Poster</th> 
			<td>
				<select name="wgm-defaultPosterID" id="wgm-defaultPosterID">
				<?php
				$val = $wpdb->get_results("SELECT ID,user_nickname FROM $wpdb->users ORDER BY user_nickname");
				$zero->ID=0;
				$zero->user_nickname='Disabled. Only allows posts from known email addresses';
				$users[0]=$zero;
				$users = array_merge( $users, $val );
				foreach ($users as $user) :
				if ($user->ID == get_settings('wgm-defaultPosterID')) $selected = " selected='selected'";
				else $selected = '';
					echo "\n\t<option value='$user->ID' $selected>$user->user_nickname</option>";
				endforeach;
				?>
       			</select><br />This poster will be used when the email address of the sender is not recognised
				<? printMessages('defaultPosterID'); ?>
			</td>
		</tr>
		<tr valign="middle"> 
			<th width="33%" scope="row">Default Image Alignment</th></th> 
			<td>
				<select name="wgm-imageAlignment" id="wgm-imageAlignment" > 
					<option value="0" <?php selected( '0', get_settings('wgm-imageAlignment')); ?>>None</option> 
					<option value="1" <?php selected( '1', get_settings('wgm-imageAlignment')); ?>>Left</option> 
					<option value="2" <?php selected( '2', get_settings('wgm-imageAlignment')); ?>>Right</option> 
					<option value="3" <?php selected( '3', get_settings('wgm-imageAlignment')); ?>>Centre</option> 
				</select>
			</td> 
		</tr> 		
	</table> 
	</fieldset>

	<fieldset class="options"> 
		<legend>Styles</legend>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr> 
			<th width="33%" scope="row">Generate Styles?</th> 
			<td>
				<input name="wgm-generate-styles" type="checkbox" id="wgm-generate-styles" value="1" <?php checked('1', get_settings('wgm-generate-styles')); ?>/>
				Add the styles underneath to the page?
			</td>
		</tr>
		<tr> 
			<td colspan="2"><textarea name="wgm-default-styles" id="wgm-default-styles" style="width: 98%;" rows="10" cols="50"><?= get_settings('wgm-default-styles')?>
</textarea>
		</td></tr>
	</table>		
	</fieldset>
		
	<fieldset class="options"> 
		<legend>Information</legend>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr> 
			<th scope="row" style="text-align: left">URL to Processor (for manual calling with POP3)</th> 
		</tr>
		<tr> 
			<td><span style="font-size: 80%; padding-left: 30px"><? $url=get_settings('siteurl').'/wp-content/plugins/wp-gallery-mail/wp-gallery-mail-processor.php'; print "<a href=\"$url\">$url</a>" ?></span></td> 
		</tr>
		<tr> 
			<th scope="row" style="text-align: left">Path to Processor (for cron jobs with POP3)</th> 
		</tr>
		<tr> 
			<td><span style="font-size: 80%; padding-left: 30px"><?= dirname(__FILE__).'/wp-gallery-mail-redirector.php'?></span></td> 
		</tr>
		<tr> 
			<th scope="row" style="text-align: left">Pipe Command (for SMTP mail forwards)</th> 
		</tr>
		<tr> 
			<td><span style="font-size: 80%; padding-left: 30px">|php -q <?= dirname(__FILE__).'/wp-gallery-mail-redirector.php'?></span></td> 
		</tr> 		
	</table>		
	</fieldset>
	
	<p class="submit"> 
		<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" /> 
	</p>

</form> 
</div> 
