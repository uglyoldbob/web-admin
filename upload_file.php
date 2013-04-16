<?php

include('include/SimpleImage.php');

#things in the images table

#who uploaded it
#image size
#image filename
#image thumbnail?

#when something uses an image
#permissions for the something are assumed to be the same as the something
#if a user can a job and it has pictures, they can see those pictures
#it may be useful to specify permissions for images specific to the thing using them
#to allow for user viewable pics and technician viewable pics


upload_file();

function check_for_upload_permission($uploader)
{	#does this person have permission to upload a(nother) file?
	#they could be at their limit for the number of files allowed
	return 0; #the user has permission
}

function upload_file($uploader, $prefix)
{	//$uploader - userid of the person doing the upload
	//$prefix - the string to prefix to the image name stored on the server

    if (check_for_upload_permission($uploader) != 0)
    {
	return 1;	//fail to upload
    }
    if ($_FILES['file']['error'] > 0)
    {
        echo "Error: " . $_FILES['file']['error'] . "<br />";
        return 2;	//file error
    }
    // array of valid extensions
    $validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
    // get extension of the uploaded file
    $fileExtension = strrchr($_FILES['file']['name'], ".");
    // check if file Extension is on the list of allowed ones
    if (in_array($fileExtension, $validExtensions)) 
    {
        // we are renaming the file so we can upload files with the same name
        // we simply put current timestamp in fron of the file name
	$thefile = new SimpleImage();
	$thefile->load($_FILES['file']['tmp_name']);

	if ($thefile->getWidth() > $thefile->getHeight())
	{
		$thefile->resize(640,480);
	}
	else
	{
		$thefile->resize(480,640);
	}

	#create image in the database and get the filename it should be stored as

        $newName = $prefix . '_' . $_FILES['file']['name'];
        $destination = 'uploads/' . $newName;
#        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) 
#        {
#            echo 'File ' .$newName. ' succesfully copied';
#        }
	
	if ($thefile->save($destination) == TRUE)
	{
		echo 'Successfully copied to ' . $destination . '? ' .  "<br >\n";
		echo '<img src="' . $destination . "\"> <br >\n";
	}
	else
	{
		echo "Failed to upload the image<br >\n";
	}

    }
    else
    {
        echo 'You must upload an image...';
    }
}

?>
