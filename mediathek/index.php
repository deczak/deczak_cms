<?php

/*

	- Rechte Abfrage wenn Bild flag gesetzt hat

		- Wenn beschränktes sichtrecht, display_filename für die anzeige

	- Möglichkeit für alternative Sprache der title und caption

	  - sprache muss dann per parameter übergeben werden. 

	  - Schauen ob die Sprachdatei geladen werden kann, dann License übersetzen, default EN

	- Nur Bilddateien sollen den nicht-binary mode haben, videos und sonstige dokumente sind immer binary

		- content header vervollständigen die eventuell benötigt werden.

	- Bei Bildern die Möglichkeit andere Auflösungen anzuzeigen für direkten Download

	- Counter Datei

	- Referrer Datei ??

	- Zugriff in Session eintragen

	- Erfassen von direkt Zugriffen ??
	
*/

require_once '../config/directories.php';
require_once '../config/standard.php';

$requestedURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if(!is_string($requestedURI) || empty($requestedURI ))
	exit;

$requestedURI = trim($requestedURI, '/');
$requestedURI = explode('/', $requestedURI);
$requestedURI = array_filter($requestedURI, 'strlen');
unset($requestedURI[0]);
$dstFilelocation = implode('/', $requestedURI).'/';

if(empty($dstFilelocation))
	exit;

$jsonInfo = file_get_contents($dstFilelocation.'info.json');

if($jsonInfo === false)
	exit;

$jsonInfo = json_decode($jsonInfo);

if($jsonInfo === null)
	exit;

$rawOutput = (isset($_GET['binary']) ? true : false);

$requestedSize = (!empty($_GET['size']) ? trim(strip_tags($_GET['size'])) : 'original');
$requestedSize = (!empty($requestedSize) ? $requestedSize : 'original');

switch($requestedSize)
{
	case 'small':	
		
		$dstFilename = $jsonInfo -> sizes -> small ?? false; 
		if($dstFilename !== false && file_exists($dstFilelocation.$dstFilename))
		{
			$dstFilename = $dstFilelocation.$dstFilename;
			break;
		}
		
	case 'medium':	
		
		$dstFilename = $jsonInfo -> sizes -> medium ?? false; 
		if($dstFilename !== false && file_exists($dstFilelocation.$dstFilename))
		{
			$dstFilename = $dstFilelocation.$dstFilename;
			break;
		}
		
	case 'large':	
		
		$dstFilename = $jsonInfo -> sizes -> large ?? false; 
		if($dstFilename !== false && file_exists($dstFilelocation.$dstFilename))
		{
			$dstFilename = $dstFilelocation.$dstFilename;
			break;
		}
		
	case 'xlarge':	
		
		$dstFilename = $jsonInfo -> sizes -> xlarge ?? false; 
		if($dstFilename !== false && file_exists($dstFilelocation.$dstFilename))
		{
			$dstFilename = $dstFilelocation.$dstFilename;
			break;
		}
		
	default:		$dstFilename = $dstFilelocation.$jsonInfo -> filename;
}

$fileInfo = (object)exif_read_data($dstFilename);

if($rawOutput)
{
	$timeStamp	= time();
	$interval	= 86400 * 7;

	if(!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		if(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + $interval > $timeStamp)
		{ 
			header("HTTP/1.1 304"); 
			exit; 
		} 
	}

	header('Content-Type: ' . $fileInfo -> MimeType);
	header('Content-Length: ' . $fileInfo -> FileSize);
	header('Content-Disposition: inline; filename='. $jsonInfo -> filename);
	header('Last-Modified: '.gmdate('r',$timeStamp));
	header('Expires: '.gmdate('r',($timeStamp + $interval)));
	header('Cache-Control: public, max-age='. $interval);
	header('Etag: '. md5_file($dstFilename));
	header('Content-transfer-encoding: binary');
	header('Pragma: public');
	echo file_get_contents($dstFilename);
}
else
{
	if($jsonInfo -> gear -> by_meta) 
	{
		$cameraModel = $fileInfo -> Model ?? '';
		$lensModel 	 = $fileInfo -> LensModel ?? '';
		$lensModel 	 = (empty($lensModel) ? ($fileInfo -> {'UndefinedTag:0xA434'} ?? '') : $lensModel);
	}
	else
	{
		$cameraModel = $jsonInfo -> gear -> camera; 
		$lensModel   = $jsonInfo -> gear -> lens;
	}

	?><!DOCTYPE html>
	<html>
		<head>
			<meta charset="UTF-8">
			<title><?= $jsonInfo -> filename; ?></title>
			<meta name="description" content="<?= $jsonInfo -> title; ?>">
			<meta NAME="robots" content="INDEX,FOLLOW">
  			<meta name="viewport" content="width=device-width">
			<style>
				* { box-sizing: border-box; color:white; font-family:sans-serif; }
				h4 { font-weight:400; margin:0px; padding:5px 0px 0px 0px; font-size:1.1em; }
				.wrapper { width:100%; max-width:1200px; margin:0 auto; padding:0 20px; }

				.imagebox { display:flex; flex-direction:column; justify-content:center; height:100vh; align-items:center; }
				.imagebox .top { width:100vw; text-align:left; }
				.imagebox .top a { cursor:pointer; font-size:0.8em; color:yellow; font-weight:bold; }
				.imagebox .center { width:100vw; height:calc(90vh - 40px); padding:20px; }
				.imagebox .center img { height:100%; width:100%; object-fit:contain; filter:drop-shadow(0px 0px 5px rgba(0,0,0,.5)) drop-shadow(0px 0px 5px rgba(0,0,0,.5)); }

				.imagebox .bottom { width:100vw; text-align:left; }
				.imagebox .bottom > div { display:flex; justify-content:space-between; }

				@media screen and (max-width: 700px) {
					.imagebox .bottom > div {
						flex-direction:column;
					}
					.imagebox .bottom > div > div {
						text-align:left!important;
					}

					.imagebox .center { 
						height:calc(90vh - 100px);
					}
				}
			</style>
			<meta property="og:title" content="<?= $jsonInfo -> title; ?>"> 
			<meta property="og:description" content="<?= $jsonInfo -> caption; ?>"> 
			<meta property="og:url" content="<?= CMS_SERVER_URL; ?>mediathek/<?= $dstFilelocation; ?>?size=xlarge"> 
			<meta property="og:image" content="<?= CMS_SERVER_URL; ?>mediathek/<?= $dstFilelocation; ?>?binary&size=large">
		</head>
		<body style="background-color:rgb(0,37,51); margin:0px;">

			<div class="imagebox">

				<?php if(!empty($_SERVER['HTTP_REFERER'])) { ?>

				<div class="top">

					<div class="wrapper">
			
						<a onclick="window.history.back(); return false;">&vltri; Go back</a>

					</div>

				</div>

				<?php } ?>

				<div class="center">

					<img 
						src="/mediathek/<?= $dstFilelocation; ?>?binary&size=xlarge" alt="<?= $jsonInfo -> title; ?><?= (!empty($jsonInfo -> caption) ? '. '.$jsonInfo -> caption.'<br>' : ''); ?>" >
	
				</div>

				<div class="bottom">

					<div class="wrapper">

						<div>

							<h4><?= $jsonInfo -> title; ?></h4>
							<span style="font-size:0.7em;">
								<?= (!empty($jsonInfo -> caption) ? $jsonInfo -> caption.'<br>' : ''); ?>
								<?= (!empty($jsonInfo -> author) ? 'Author: '.$jsonInfo -> author.'<br>' : ''); ?>
							</span>

						</div>

						<div style="text-align:right;">

							<span style="font-size:0.7em; ">
								<?= (!empty($jsonInfo -> license) ? 'License: '.$jsonInfo -> license.'<br>' : ''); ?>
								<?= $cameraModel .'<br>'; ?>
								<?= $lensModel; ?>
							</span>

						</div>

					</div>

				</div>

			</div>

		</body>
	</html><?php
}