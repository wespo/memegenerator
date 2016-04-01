<?php
	//read list of URLs
	$filename = 'imgurURLs.txt';
	if(isset($_GET['bestof'])) //allow alrternative path for best of. No auto-writing to best of.
	{
		$filename = 'bestofImgurURLs.txt';
	}
	$source_file = fopen( $filename, "r" ) or die("Couldn't open $filename");
	$fileData = fread($source_file,filesize($filename));
	fclose($source_file);
	//decode data
	$urls = json_decode($fileData);

	//trim array to 10 elements if need be.
	if(count($urls) > 10)
	{
		array_pop($urls);
	}


	if(isset($_GET['imgurURL']) && ($_GET['imgurURL'] != "") && ($filename == 'imgurURLs.txt'))
	{
		$newURL = $_GET['imgurURL'];
		if(!in_array($newURL,$urls))
		{
			array_unshift($urls,$newURL);
			if(count($urls) > 10) //keep array at 10 elements
			{
				array_pop($urls);
			}
			
			$source_file = fopen( $filename, "w" ) or die("Couldn't open $filename");
			$fileData = fwrite($source_file, json_encode($urls));
			fclose($source_file);
		}
		
	}
	echo json_encode($urls);
?>