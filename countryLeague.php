<?php
  ini_set('max_execution_time', 300);
 
  $countryList=array('africa', 'albania', 'algeria', 'argentina', 'austria', 'algeria', 'angola', 'armenia', 'asia', 'australia', 'azerbaijan', 'belarus', 'belgium', 'bolivia', 'bosnia', 'brazil', 'bulgaria', 'cameroon', 'canada', 'chile', 'china', 'colombia', 'congo', 'croatia', 'cyprus', 'czech', 'costarica', 'denmark', 'equador', 'egypt', 'elsalvador', 'england', 'estonia', 'europe', 'finland', 'france', 'georgia', 'germany', 'ghana', 'greece', 'guatemala', 'holland', 'honduras', 'hungary', 'iceland', 'india', 'indonesia', 'international', 'iran', 'ireland', 'israel', 'italy', 'japan', 'jordan', 'kazakhstan', 'kenya', 'korea', 'kuwait', 'latvia', 'lithuania', 'macedonia', 'malaysia', 'malta', 'mexico', 'moldova', 'montenegro', 'morocco', 'newzealand', 'nigeria', 'norway', 'paraguay', 'peru', 'poland', 'portugal', 'qatar', 'romania', 'russia', 'saudiarabia', 'scotland', 'serbia', 'singapore', 'slovakia', 'slovenia', 'southafrica', 'southamerica', 'spain', 'sweden', 'switzerland', 'thailand', 'tunisia', 'turkey', 'uae', 'usa', 'ukraine', 'uruguay', 'uzbekistan', 'venezuela', 'vietnam', 'wales', 'worldcup');

  // $leagueXmlName = explode(":", "Algeria: Algeria Cup - Play Off");
  
  // countryName("france", "Ligue 1");

 foreach ($countryList as $key => $value) {
  print_r($value."<br>");
   curl_download_fixtures($value);
 }
 // curl_download_fixtures("france");

  function curl_download_fixtures($country){

    // echo $curTimestamp;
    $Url="http://www.tipgin.net/datav2/accounts/mbulut/soccer/fixtures/".$country.".xml";
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch,CURLOPT_ENCODING, '');
    $output = curl_exec($ch);
    curl_close($ch);

    // if(simplexml_load_string($output)){
      $xml = simplexml_load_string($output);
    // }
    //print_r($xml);
    foreach ($xml->league as $league) {

      $leagueXmlName = explode(":", (string)$league->attributes()->name);
      $leagueXmlName = explode("-", $leagueXmlName[1]);
      $leagueXmlName = $leagueXmlName[0];    
      $leagueXmlName = strtolower(trim($leagueXmlName));
      // echo $leagueXmlName . "|";
      if($leagueXmlName == "Pro league"){
        echo "-----------------------------" . $league->attributes()->country."<br>";
        break;
      }

    }
   
 }


  
 /* $betCreationTime = gmdate("Y-m-d H:i:s");

  $betDetails = array_merge($fixtures, $odds);
  $betDetails["betCreationTime"] = $betCreationTime . "";
  echo json_encode($betDetails);
*/
?>