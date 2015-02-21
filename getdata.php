<?php
  $betsDetail = array();
  echo "<pre>";
  // $leagueXmlName = explode(":", "Algeria: Algeria Cup - Play Off");
  $leagueXmlName = explode(":", "Algeria: Algeria Cup");
  $leagueXmlName = explode("-", $leagueXmlName[1]);
  print_r($leagueXmlName[0]);


  $con=mysql_connect("localhost","root","");
  if(!$con)
  {
    echo "error".die(mysql_error());
  }
  $db=mysql_select_db("betterthegame",$con);
  if(!$db)
  {
    echo "no db".die(mysql_error());
  }

 
  $sql3="SELECT display_name,country FROM user WHERE user_type = 2";
  $query3=mysql_query($sql3) or die(mysql_error());
  $row3=mysql_fetch_assoc($query3) or die(mysql_error());
  while ($row3=mysql_fetch_assoc($query3)) {
    // print_r($row3);
     // countryName($row3['country']);  
     
  }
  
  countryName("france", "Ligue 1");

  echo "</pre>";
  function countryName($country, $league)
  {
  
    $fixtures = curl_download_fixtures($country, $league);
    //$odds = curl_download_odds($country, $league);
   //$odds = curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/".$country.".xml");
  }
  function curl_download_odds($country, $leagueName){
    echo $leagueName . "<br/>";
    $name = array();
    $betsDetails = array();
    $betDetails = array();
    $i=0;
    $currentDate = gmdate("d.m.Y");
    $curTimestamp = strtotime((string)$currentDate);
    echo $curTimestamp;
    $Url="http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/".$country.".xml";
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
    $xml = simplexml_load_string($output);
    // print_r($xml);
    foreach ($xml->league as $league) {

      $leagueXmlName = explode(":", (string)$league->attributes()->name);
      $leagueXmlName = explode("-", $leagueXmlName[1]);
      $leagueXmlName = $leagueXmlName[0];    
      $leagueXmlName = trim($leagueXmlName);

      if($leagueXmlName == $leagueName){
        foreach ($league->match as $match) {
          $betDetails['match_id'] = (string)$match->attributes()->id."";
          if(isset($match->odds->type) && $match->odds->type->attributes()->name == "1x2"){
            foreach($match->odds->type->bookmaker as $bookmaker){
             if($bookmaker->attributes()->id == "781"){
                foreach($bookmaker->odd as $odd){
                  $oddName = $odd->attributes()->name . "";
                  if($oddName == "1"){
                      $betDetails["opt1percent"] = $odd->attributes()->value . "";
                      // echo "opt1percent: " . $bookmaker->odd->attributes()->value;
                  } elseif ($oddName == "X") {
                      $betDetails["opt2percent"] = $odd->attributes()->value . "";
                  } elseif ($oddName == "2") {
                      $betDetails["opt3percent"] = $odd->attributes()->value . "";
                  }  
                }
                // print_r($betDetails);
                $betsDetails[] = $betDetails;
              }
            }
          }
        }
      }
    }
    print_r($betsDetails);
    return $betsDetails;
  }

  function curl_download_fixtures($country, $leagueName){
    echo $leagueName . "<br/>";
    $name = array();
    $betsDetails = array();
    $betDetails = array();
    $i=0;
    $currentDate = gmdate("d.m.Y");
    $curTimestamp = strtotime((string)$currentDate);
    echo $curTimestamp;
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
    $xml = simplexml_load_string($output);
    //print_r($xml);
    foreach ($xml->league as $league) {

      $leagueXmlName = explode(":", (string)$league->attributes()->name);
      $leagueXmlName = explode("-", $leagueXmlName[1]);
      $leagueXmlName = $leagueXmlName[0];    
      $leagueXmlName = trim($leagueXmlName);
      // echo $leagueXmlName . "|";

      if($leagueXmlName == $leagueName){
        foreach ($league->match as $match) {
          // $matchTimestam = strtotime((string)$match->attributes()->date);

          // if($matchTimestam > $curTimestamp){
          // print_r($match);
          $betDetails = array();
          $date=$match->attributes()->date;
          $dateInRequiredFormat=str_replace(".","-",$date);
          $time=$match->attributes()->time;
          $dateAndTimeArray = array($dateInRequiredFormat,'',$time . ":00");
          $dateAndTimeArray=join(" ",$dateAndTimeArray);

          $betEndsTime = date_create($dateAndTimeArray);
          $betEndsTime = date_format($betEndsTime,"Y-m-d H:i:s");

          $betReminderTime=date_create($betEndsTime);
          date_add($betReminderTime,date_interval_create_from_date_string("110 minutes"));
          $betReminderTime = date_format($betReminderTime,"Y-m-d H:i:s");
          $betDetails["match_id"] = (string)($match->attributes()->id); 
          $betDetails["betEndsTime"] = $betEndsTime;
          $betDetails["betReminderTime"] = $betReminderTime;
          $betDetails["option1"] = "HOME";
          $betDetails["option2"] = "DRAW";
          $betDetails["option3"] = "AWAY";
          $betDetails["description"] = $match->home->attributes()->name . " v/s " . $match->away->attributes()->name;
          // }
          // print_r($betDetails);
          $betCreationTime = gmdate("Y-m-d H:i:s");
          $betDetails["betCreationTime"] = $betCreationTime . "";
          $betsDetails[] = $betDetails;
        } 

      }
    }
    print_r($betsDetails);
    return $betsDetails;
  }


  
 /* $betCreationTime = gmdate("Y-m-d H:i:s");

  $betDetails = array_merge($fixtures, $odds);
  $betDetails["betCreationTime"] = $betCreationTime . "";
  echo json_encode($betDetails);
*/
?>