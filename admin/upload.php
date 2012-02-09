<?php
function uploadFile($file,$path){
	if(!is_dir(substr($path,0,strrpos($path,"/"))))
		mkdir(substr($path,0,strrpos($path,"/")),0777,true);
	
	if(!file_exists($path)){
		if (pathinfo($path, PATHINFO_EXTENSION) === "png")
			if(imagepng($file,$path))
				return "Fil sparat i: " . $path;
			else 
				return "Okänt fel!";
		else
			move_uploaded_file($file,$path);
	} else
		return "Fil finns redan";
	return "Fil sparat i: " . $path;
}
function safePath($safeFile){
	$safeFile = substr($safeFile,0,strrpos($safeFile,"."));
	$safeFile = str_replace("#", "Num", $safeFile);
	$safeFile = str_replace("$", "Dollar", $safeFile);
	$safeFile = str_replace("%", "Percent", $safeFile);
	$safeFile = str_replace("^", "", $safeFile);
	$safeFile = str_replace("&", "and", $safeFile);
	$safeFile = str_replace("*", "", $safeFile);
	$safeFile = str_replace("?", "", $safeFile);
	return $safeFile;
}
function processImage($path,$maxWidth,$maxHeight){
	$imgSize = getImageSize($path);
	$width = $imgSize[0];
	$height = $imgSize[1];
	$type = $imgSize[2];
	$image=null;
	switch ($type){
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($path);
			break;
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($path);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($path);
			break;
		default:
			echo "Okänd bildtyp";
	}
	if ($width > $maxWidth){
		$newHeight = round($maxWidth/$width*$height);
		$newWidth = $maxWidth;
	}
	if ($height > $maxHeight){
		$newWidth = round($maxHeight/$height*$width);
		$newHeight = $maxHeight;
	}
	$newImage = imagecreatetruecolor($newWidth,$newHeight);
	imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	return $newImage;
}
?>