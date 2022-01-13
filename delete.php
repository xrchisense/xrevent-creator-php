<?php

function deleteFile($filePath){
	if($filePath != null){
		$location = "upload/".$filePath;
		if(file_exists($location)){
			unlink($location);
			echo "Success.";
		} else {
			echo "Failure.";
		}
	} else {
		echo "No filename specified. Use url param file to select one.";
	}
	
}

deleteFile($_GET['filepath']);



?>