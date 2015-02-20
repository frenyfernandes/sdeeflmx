<?php
  $betsDetails = array();

  function curl_download($Url, $betsDetails){
    $name = array();
    $i=0;
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
    foreach ($league->match as $match) {
       if($Url == "http://www.tipgin.net/datav2/accounts/mbulut/soccer/fixtures/france.xml" ){
        if($match->attributes()->id == "4102818"){
          $date=$match->attributes()->date;
          $dateInRequiredFormat=str_replace(".","-",$date);
          $time=$match->attributes()->time;

          $dateAndTimeArray = array($dateInRequiredFormat,'',$time . ":00");
          $dateAndTimeArray=join(" ",$dateAndTimeArray);

          $betEndsTime = date_create($dateAndTimeArray);
          $betEndsTime = date_format($betEndsTime,"Y-m-d H:i:s");

          $time .= ":00";

          $betReminderTime=date_create($betEndsTime);
          date_add($betReminderTime,date_interval_create_from_date_string("110 minutes"));
          $betReminderTime = date_format($betReminderTime,"Y-m-d H:i:s");

          $betsDetails["betEndsTime"] = $betEndsTime;
          $betsDetails["betReminderTime"] = $betReminderTime;
          $betsDetails["option1"] = "HOME";
          $betsDetails["option2"] = "DRAW";
          $betsDetails["option3"] = "AWAY";
          $betsDetails["description"] = $match->home->attributes()->name . " v/s " . $match->away->attributes()->name;
          // echo "date and time:  ".$dateAndTime."<br>";
          // echo "home:  ".$match->home->attributes()->name."<br>";
          // echo "away:  ".$match->away->attributes()->name."<br>";
         }
       }
       elseif($Url =="http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/france.xml"){
         if($match->attributes()->id == "4102818"){
            if($match->odds->type->attributes()->name == "1x2"){
              foreach($match->odds->type->bookmaker as $bookmaker){
                if($bookmaker->attributes()->id == "781"){

                  

                  foreach($bookmaker->odd as $odd){
                    $oddName = $odd->attributes()->name . "";
                    if($oddName == "1"){
                        $betsDetails["opt1percent"] = $odd->attributes()->value . "";
                        // echo "opt1percent: " . $bookmaker->odd->attributes()->value;
                    } elseif ($oddName == "X") {
                        $betsDetails["opt2percent"] = $odd->attributes()->value . "";
                    } elseif ($oddName == "2") {
                        $betsDetails["opt3percent"] = $odd->attributes()->value . "";
                    }  
                  }
                }
              }
            }
          }
        }
      }
    }
    return $betsDetails;
  }

  $fixtures = curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/fixtures/france.xml", array());
  // print_r($fixtures);

  $odds = curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/france.xml", array());
  // print_r($odds);

  $betDetails = array_merge($fixtures, $odds);
  // echo "<pre>";
  
  // query
  echo json_encode($betDetails);
  // echo "</pre>";
?>