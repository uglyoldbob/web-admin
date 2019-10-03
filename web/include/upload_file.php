<?php
#this script must be included in another script to use properly
if ('upload_file.php' == basename($_SERVER['SCRIPT_FILENAME']))
	throw new \webAdmin\PermissionDeniedException();

include_once('SimpleImage.php');

#things in the images table

#who uploaded it
#image size (vga and thumb combined size)
#image filename
#image thumbnail?

#when something uses an image
#permissions for the something are assumed to be the same as the something
#if a user can a job and it has pictures, they can see those pictures
#it may be useful to specify permissions for images specific to the thing using them
#to allow for user viewable pics and technician viewable pics


#upload_image($_FILES['file'], 0, "pict", $id);
#echo "The image id is " . $id . "<br >\n";

function no_image()
{
	return '<div style="width:128px;height:96px;border:1px solid #000;">No Image</div>';
}

function check_for_upload_permission($uploader)
{	#does this person have permission to upload a(nother) file?
	#they could be at their limit for the number of files allowed
	#TODO: actually implement permission checking
	return 0; #the user has permission
}

function check_for_read_permission($img_id)
{	//TODO: actually perform a check to see if the user has read permissions
	return true;
}

function fix_file()
{	//fixes the $_FILE variable when multiple files are uploaded
	//TODO: implement this (code snippets are easily found)
}

function create_image_entry($uploader)
{
	global $mysql_db;
	$query = "INSERT INTO images (uploader) VALUES ('" . $uploader . "');";
	if (!$mysql_db->query($query))
	{
		throw new Exception("Error: " . $mysql_db->error . "<br >\n");
	}
	else
	{
		return $mysql_db->insert_id;
	}
}

function finish_image_entry($img_id, $vga, $thumb)
{
	global $mysql_db;
	$query = "UPDATE images SET " .
		"`size`='" . (filesize($vga) + filesize($thumb)) . "', " .
		"`file_vga`='" . $vga . "', " .
		"`file_thumb`='" . $thumb . "' " .
		"WHERE `id`='" . $img_id . "';";
	echo "The query is <emph>" . $query . "</emph><br >\n";
	if (!$mysql_db->query($query))
	{
		throw new Exception("Error: " . $mysql_db->error . "<br >\n");
	}
	else
	{	//no error
	}
}

function remove_entry($img_id)
{
	global $mysql_db;
	$query = "DELETE FROM images WHERE id='" . $img_id . "';";
	if (!$mysql_db->query($query))
	{
		throw new Exception("Error: " . $mysql_db->error . "<br >\n");
	}
	else
	{	//no error
	}
}

//$name = $_FILES['file']['name']
//$file = $_FILES['file']['tmp_name']

//return codes
//0 - ok
//1 - permission denied for upload
//2 - file upload error
//3 - unsupported file uploaded
function upload_image($file, $uploader, $prefix, &$id, $config)
{	//$uploader - userid of the person doing the upload
	//$prefix - the string to prefix to the image name stored on the server
	if ($file['error'] > 0)
	{
		echo "Error: " . $_FILES['file']['error'] . "<br />";
		return 2;   //file error
	}


	if (check_for_upload_permission($uploader) != 0)
	{
		return 1;	//fail to upload
	}
	// array of valid extensions
	$validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
	// get extension of the uploaded file
	$fileExtension = strrchr($file['name'], ".");
	// check if file Extension is on the list of allowed ones
	if (in_array($fileExtension, $validExtensions)) 
	{
		// we are renaming the file so we can upload files with the same name
		// we simply put current timestamp in fron of the file name
		$thefile = new SimpleImage();
		$thefile->load($file['tmp_name']);

		if ($thefile->getWidth() > $thefile->getHeight())
		{
			$thefile->resize(640,480);
		}
		else
		{
			$thefile->resize(480,640);
		}

		#create image in the database and get the filename it should be stored as
		$img_id = create_image_entry($uploader);

		$vga_name = $prefix . '_' . $img_id . "_vga";
		$thumb_name = $prefix . '_' . $img_id . "_thumb";
		$destination_vga = 'uploads/' . $vga_name . ".jpg";
		$destination_thumb = 'uploads/' . $thumb_name . ".jpg";

		if ($thefile->save($destination_vga) == TRUE)
		{
			$thefile->scale(20);	//128x96 or 96x128
			if ($thefile->save($destination_thumb) == TRUE)
			{
				echo 'Successfully uploaded' . "<br >\n";
				echo '<img src="' . rootPageURL($config) . '/uploads/image.php?id=' . $img_id . ".jpg\"> <br >\n";
				finish_image_entry($img_id, $destination_vga, $destination_thumb);
			}
			else
			{
				echo "Failed to upload the thumb image<br >\n";
				remove_entry($img_id);
				@unlink($destination_vga);
				@unlink($destination_thumb);
				return 2;
			}
		}
		else
		{
			echo "Failed to upload the vga image<br >\n";
			remove_entry($img_id);
			@unlink($destination_vga);
			@unlink($destination_thumb);
			return 2;
		}
	}
	else
	{
		echo 'You must upload an image...';
		return 3;
	}
	$id = $img_id;
	return 0;	//everything is ok
}

?>
