<?php

/* Get the name of the uploaded file name*/
$filename = $_FILES['file']['name'];

/* Get the folder name specified by form text input field*/
$folderUID = $_POST['folder'];

mkdir("upload/".$folderUID, 0777, true);

/* Choose where to save the uploaded file */
$location = "upload/".$folderUID."/".$filename;

/* Save the uploaded file to the local filesystem */
if ( move_uploaded_file($_FILES['file']['tmp_name'], $location) ) { 
  echo 'Success'; 
} else { 
  echo 'Failure'; 
}

?>