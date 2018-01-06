<?php
require_once("exceptions.php");
#this script must be included in another script to use properly
if ('upload_file.php' == basename($_SERVER['SCRIPT_FILENAME']))
	throw new PermissionDeniedException();

include('SimpleImage.php');

#when something uses an image
#permissions for the something are assumed to be the same as the something
#if a user can a job and it has pictures, they can see those pictures
#it may be useful to specify permissions for images specific to the thing using them
#to allow for user viewable pics and technician viewable pics


#upload_image($_FILES['file'], 0, "pict", $id);
#echo "The image id is " . $id . "<br >\n";

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

//$name = $_FILES['file']['name']
//$file = $_FILES['file']['tmp_name']

//return codes
//0 - ok
//1 - permission denied for upload
//2 - file upload error
//3 - unsupported file uploaded
function upload_image($file, $uploader, $prefix, &$id)
{	//$uploader - userid of the person doing the upload
	//$prefix - the string to prefix to the image name stored on the server
	global $config;
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
		$destination_file = 'invoices/' . $file['name'];
		if ($thefile->save($destination_thumb) == TRUE)
		{
			echo 'Successfully uploaded' . "<br >\n";
		}
		else
		{
			echo "Failed to upload the invoice<br >\n";
			@unlink($destination_file);
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
	
