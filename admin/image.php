function prewievFile($path){
	return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
}

if(is_uploaded_file($_FILES['image']['tmp_name']))
	echo prewievFile($_FILES['image']['tmp_name']);