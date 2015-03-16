<?php

class SynoDLMSearchCpasbien {

   private $sHost 	= 'http://www.cpasbien.pw';
   private $sCurl  	= 'http://www.cpasbien.pw/recherche/';
   private $sDlUrl  = 'http://www.cpasbien.pw/telechargement/';

   public function __construct() {
   }

   public function prepare($curl, $query) {
	  curl_setopt($curl, CURLOPT_URL, $this->sCurl);
	  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	  curl_setopt($curl, CURLOPT_POST, true);
	  curl_setopt($curl, CURLOPT_POSTFIELDS, 'champ_recherche='. urlencode($query));
   }

   public function parse($oPlugin, $sResponse) {

	$oDomDocument = new DomDocument();
	$oDomDocument->LoadHTML($sResponse);
	$aList 		= $oDomDocument->getElementsByTagName('div');
	$iNbResults	= 0;

	foreach($aList as $oDiv)
	{
	   	if (true === $oDiv->hasAttributes() && null !== $oDiv->attributes->getNamedItem('class')) 
	   	{
			$sClass = $oDiv->attributes->getNamedItem('class')->nodeValue;
			if ($sClass === 'ligne0' || $sClass === 'ligne1')
			{
			   $aResult = array();
			   $aResult['title']	= $oDiv->firstChild->nodeValue;
			   $aResult['page'] 	= $oDiv->firstChild->attributes->getNamedItem('href')->nodeValue;
			   $aResult['download'] = $this->sDlUrl . str_replace('.html', '.torrent', basename($aResult['page']));

			   $oSize  				= $oDiv->firstChild->nextSibling;
			   $aResult['size'] 	= $this->formatFilesize($oSize->nodeValue); 
			   $oSeeds				= $oSize->nextSibling;
			   $aResult['seeds']	= $oSeeds->nodeValue;

			   $oLeechs	= $oSeeds->nextSibling;
			   $aResult['leechs']	= $oLeechs->nodeValue;

			   $sTitle	= str_replace('<br>', ' ',  $oDiv->firstChild->attributes->getNamedItem('title')->nodeValue);
			   $iPos 	= strrpos($sTitle, '-');

			   $aResult['category'] =  substr($sTitle, 0, $iPos - 1);

			   $oDate = new datetime( trim(substr($sTitle, $iPos + 3)), new DateTimeZone('Europe/London'));
			   $aResult['datetime'] = $oDate->format('Y-m-d H:i');

			   $aResult['hash'] 	= md5($aResult['title'] . $aResult['size']); 

			  $oPlugin->addResult(
					 $aResult['title'],
					 $aResult['download'],
					 $aResult['size'],
					 $aResult['datetime'],
					 $aResult['page'],
					 $aResult['hash'],
					 $aResult['seeds'],
					 $aResult['leechs'],
					 $aResult['category']
				);
				$iNbResults++;
			}
		}
	}

	return $iNbResults;
   }

   public function formatFilesize($sFilesize)
   {
		$sFilesize = substr($sFilesize, 0 ,-2);
   		$aFilesize 	= explode(' ', $sFilesize);

		if (true == isset($aFilesize[1]))
		{
			$sUnit 		= strtolower($aFilesize[1]);

			switch($sUnit)
			{
			    case 'ko':
				   	$sFilesize = round((int) $aFilesize[0] * 1024, 2);
				   break;

				case 'mo':
				   	$sFilesize = round((int) $aFilesize[0] * 1024 * 1024, 2);
				   break;

				case 'go':
				   	$sFilesize = round((int) $aFilesize[0] * 1024 * 1024 * 1024, 2);
				   break;

			}

		}
		return $sFilesize;
   }
}

?>
