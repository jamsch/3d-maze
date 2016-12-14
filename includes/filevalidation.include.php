<?php 
	$allowed_exts = [
						'txt', //text
						'rtf',
						'doc',
						'docx',
						'pages',
						'jpg', //image
						'jpeg',
						'JPG',
						'png',
						'gif',
						'tiff',
						'bmp',
						'mov', //video
						'wmv',
						'avi',
						'mv4',
						'mp4',
						'webm',
						'mp3', //audio
						'wav',
						'mpa',
						'ogg',
						'mid',
						'midi',
						'psd',
						'ai', //design
						'svg',
						'obj', //3D
						'max',
						'fbx',
						'log', //data
						'csv',
						'xml',
						'html', //web
						'css',
						'js',
						'ttf', //font
						'fon',
						'fnt',
						];

	$allowed_exts_JSON = json_encode($allowed_exts);
	$max_size = 3145728; //3mb
	$max_files = 4;
?>