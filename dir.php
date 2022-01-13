<?php


function listDir($folderName){
	if($folderName != null){
		$dir_iterator = new RecursiveDirectoryIterator("./" . $folderName);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		// could use CHILD_FIRST if you so wish

		foreach ($iterator as $file) {
			if ($file->isFile()) {
				echo $file->getFilename() . ",";
			}
		}
	} else {
		echo "No UID specified. Use url param UID to select one.";
	}
}

listDir($_GET['UID']);

?>