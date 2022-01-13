<?php    
	if(isset($_POST['folder']) && isset($_POST['file'])){
		mkdir("upload/".$_POST['folder'], 0777, true);
		file_put_contents("upload/".$_POST['folder']."/EventLayout.json", $_POST['file']);      
		echo "Success";
	}else{
		echo "Failure";
	}  
?>
