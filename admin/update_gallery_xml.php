<?php
function update_gallery_xml($rum,$tab,$rub,$desk,$names){
	$path=$rum."/galleri_".$tab;
	$simOld = simplexml_load_file($path."/data.xml");
	$sim = new SimpleXMLElement("<galleri></galleri>");
	$imgTypes = array( ".jpeg", ".png", ".osv" );
	if($handle = opendir( $path )){
		$con=0;
		$files=array();
		for($i=0;$i<count($simOld->bild);$i++){
			$files[]=$simOld->bild[$i]->id;
		}
		while (false !== ($file = readdir($handle))) {
			if ($file == "." || $file == ".." || $file == "data.xml" || is_dir('../rum/'.$file)) continue;
			if(!in_array($file,$files)){
				$simOld->addChild("bild");
				$simOld->bild[$con]->addChild("id",$file );
				if($rub[$con])
					$simOld->bild[$con]->addChild("namn",$rub[$con] );
				else
					$simOld->bild[$con]->addChild("namn","" );
				if($desk[$con])
					$simOld->bild[$con]->addChild("beskrivning",$desk[$con] );
				else
					$simOld->bild[$con]->addChild("beskrivning","" );
				$con++;
			}
		} 
		if(($han=fopen($path."/data.xml","w")))
			fwrite( $han, $sim->asXML());
	}
}
?>