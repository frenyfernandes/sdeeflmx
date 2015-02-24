<?php
  $betsDetail = array();
  $matchId = array();
  $todb = array();
  //echo "<pre>";
  $countryList=array("england","germany","france","spain","europe","italy");
 // var_dump($countryList);
  // $leagueXmlName = explode(":", "Algeria: Algeria Cup - Play Off");



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
  

 
  $selCountryName="SELECT DISTINCT display_name,country FROM user WHERE user_type = 2";
  $exeSelCountry=mysql_query($selCountryName) or die(mysql_error());
  while ($fetchCountry=mysql_fetch_assoc($exeSelCountry)) {
    //  print_r($fetchCountry);
     // countryName($fetchCountry['country'],$fetchCountry['display_name'] );  
    }
  
  countryName("france", "Ligue 1");

  //echo "</pre>";
  function countryName($country, $league)
  {
    $matchId = array();
    
    $fixtures = curl_download_fixtures($country, $league);
    $odds = curl_download_odds($country, $league);
    foreach($fixtures as $fixturez) {
      foreach($odds as $oddz){
        if($fixturez['match_id'] == $oddz['match_id'])
        {
          $betsDetail[]=array_merge($fixturez,$oddz);

         // print_r($betsDetail);
        }
      }
    }
    //selecting user id query
    $selUserId="SELECT user_id FROM user WHERE `display_name` = '$league'";
    $exeselUserId=mysql_query($selUserId) or die(mysql_error());
    $UserId=mysql_fetch_assoc($exeselUserId) or die(mysql_error());
   // print_r("user_id:" . $UserId['user_id'] . "-----------");
    $UserId = $UserId['user_id'];

    // foreach ($betsDetail as $key => $value) {
    //   print_r($value["match_id"] . "<br>");
    // }
    $selMatchId="SELECT match_id FROM bets WHERE `correct_option` IS NULL AND match_id > 0";
    $exeMatchId=mysql_query($selMatchId) or die(mysql_error());

    // echo "undeclared Bets found in DB: " . mysql_num_rows($exeMatchId) . "<br>";
    $temp = $exeMatchId;

    $dbMatchIds = array();
    $alreadyPresent = array();

    // while ($row  = mysql_fetch_assoc($temp)) {
    //   $dbMatchIds[] = $row['match_id'];
    // }

    while ($fetchMatchId=mysql_fetch_assoc($exeMatchId)) {
      // echo "<br>";
      // print_r($fetchMatchId['match_id']);
      $i = 0;
      foreach ($betsDetail as $key => $value) {
        // print_r($value['match_id'] . "<br>");
        if($value['match_id'] == $fetchMatchId['match_id']){
          // echo "Value found: " . $value['match_id']."<br> i = " . $i . "<br>";
          $alreadyPresent[] = $i;
          break;
        } 
        $i++;
      }
    }

   // print_r($alreadyPresent);
    foreach ($alreadyPresent as $key => $value) {
      unset($betsDetail[$value]);
    }

    // print_r($betsDetail);
   // echo "New details:<br>";
    $insertCounter = 0;
    $UserId = (int)$UserId;

   // return;


    foreach ($betsDetail as $key => $value) {
      // print_r($betsDetail[$key]['description']);
      /*$description=$betsDetail[$value]['description'];//['description'];
      print_r($description)."<br>";*/
      
      $description=(string)$betsDetail[$key]['description'];
      $option1=(string)$betsDetail[$key]['option1'];
      $opt1percent=(double)$betsDetail[$key]['opt1percent'];
      $option2=(string)$betsDetail[$key]['option2'];
      $opt2percent=(double)$betsDetail[$key]['opt2percent'];
      $option3=(string)$betsDetail[$key]['option3'];
      $opt3percent=(double)$betsDetail[$key]['opt3percent'];
      $betCreationTime=(string)$betsDetail[$key]['betCreationTime'];
      $betEndsTime=(string)$betsDetail[$key]['betEndsTime'];
      $betReminderTime=(string)$betsDetail[$key]['betReminderTime'];
      $match_id=(string)$betsDetail[$key]['match_id'];
     
      $queryInsert = "INSERT INTO `bets` ";
      $queryInsert .= "(`bet_id`, `creator_id`, `category_id`, `bet_details`, `option1`, `opt1percent`, `option2`, `opt2percent`, `option3`, `opt3percent`, `option4`, `opt4percent`, `creation_time`, `bet_ends`, `rem_time`, `correct_option`, `reportedBy`, `match_id`";
      $queryInsert .= ") VALUES ( ";
      $queryInsert .= "'', $UserId,1,'$description','$option1',$opt1percent,'$option2',";
      $queryInsert .= "$opt2percent,'$option3',$opt3percent, NULL, NULL,";
      $queryInsert .= "'$betCreationTime','$betEndsTime','$betReminderTime', NULL, NULL,$match_id)";
        //echo $queryInsert . "<br>";
       $exeInsert=mysql_query($queryInsert) or die(mysql_error());
      //  $insertCounter++;
    }
    
  }
  //print_r($betsDetail);
  function curl_download_odds($country, $leagueName){
    // echo $leagueName . "<br/>";
    $name = array();
    $betsDetails = array();
    $betDetails = array();
    $i=0;
    $currentDate = gmdate("d.m.Y");
    $curTimestamp = strtotime((string)$currentDate);
    // echo $curTimestamp;
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
    //print_r($betsDetails);
    return $betsDetails;
  }

  function curl_download_fixtures($country, $leagueName){
    // echo $leagueName . "<br/>";
    $name = array();
    $betsDetails = array();
    $betDetails = array();
    $i=0;
    $currentDate = gmdate("d.m.Y");
    $curTimestamp = strtotime((string)$currentDate);
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
    //print_r($betsDetails);
    return $betsDetails;
  }


  
 /* $betCreationTime = gmdate("Y-m-d H:i:s");

  $betDetails = array_merge($fixtures, $odds);
  $betDetails["betCreationTime"] = $betCreationTime . "";
  echo json_encode($betDetails);
*/
?>