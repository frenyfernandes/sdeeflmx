<?php

  require_once('./lib/connection.php');
  require_once('./lib/database.php');

  $betsDetail = array();
  $matchId = array();
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
  
  countryName("europe", "Champions League");

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

  function curl_download_live($Url, $matchIds){
    $winners = array();

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
    foreach ($matchIds as $key => $matchId) {
      foreach ($xml->league as $league) {
        foreach ($league->match as $match) {
          if($match->attributes()->id == $matchId){
            if($match->attributes()->status == "FT"){
            $score_home=$match->home->attributes()->goals;
            $score_away=$match->away->attributes()->goals;
            if((int)$score_home > (int)$score_away ){
              $winner = $match->home->attributes()->name;
              echo  $matchId . ": " . $winner . "<br/>";
              $winners[$matchId] = (string)$winner;
            } else {
                $winner = $match->away->attributes()->name;
                echo  $matchId . ": " . $winner. "<br/>";
                $winners[$matchId] = (string)$winner;
              }
            } else {
              // echo $matchId . ": " . "match is still not over". "<br/>";
            }
          }
        }
      }
    }
    return $winners;
  }

  function declareWinner($match_id, $winner, $database){
    $resultBetsId = $database->query("SELECT bet_id, creator_id FROM bets WHERE match_id = $match_id");
    $resultBetsId = $database->fetch($resultBetsId);

    $resultBetsId = mysqli_fetch_assoc($resultBetsId);
    // print_r($resultBetsId);
    // echo "\nwinner: " . $winner;
    $creatorId = $resultBetsId['creator_id'];
    $bet_id = $resultBetsId['bet_id'];

    $answer = $winner;



     $tabUserBets   = "user_bets" ;
     $tabBets     = "bets";
     $tabUser     = "user";
     $tabUserExp  = "user_exp" ;
     $action    = "lost";
     // $usersArray = array();
     $failureDeviceToken = array();
     $successDeviceToken =array() ;
     $responseMeassage = array();
     $bet_option = '' ;
     $bet_per = '' ;
     $bettingPercentage = '' ;
     $betAmount = '';
     $amount = '' ;
     $amountArray = array();
     $prevAmount = '';
     $totalAmount = '' ;
     $totalAmountArray = array(); 
     $expAmount = 120  ; //fix amount to increment for experience 
     $exp  = '';
     $winner = '';

     $tabSettings = "user_settings";
    // Put your device token here (without spaces):
    // $deviceToken = '69558e1440475d1558b04ab0c8144384600ff119b00c6af5909090090909090e6bd318b7a01ec6cc';
     // 69558e1440475d1558b04ab0c8144384600ff119b00c6af5e6bd318b7a01ec6c
    // Put your private key's passphrase here:
    $passphrase = 'betterthegame7';

    // Put your alert message here:
    // $message = 'My first push notification!';
    ////////////////////////////////////////////////////////////////////////////////

    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'BetterTheGameApplication.pem');

    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        
       // stream_context_set_option($ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer');

    // Open a connection to the APNS server

    $fp = stream_socket_client(
      'ssl://gateway.push.apple.com:2195', $err,
      $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$fp)
      exit("Failed to connect: $err $errstr" . PHP_EOL);

    // echo '<br/>Connected to APNS' . PHP_EOL;



    $qryUserId = "SELECT * FROM ".$tabUserBets." WHERE bet_id=".$bet_id ; 
    $exeUserId = $database->query($qryUserId);

    $qryUpdateAns = "UPDATE ".$tabBets." SET correct_option='".$answer."' WHERE bet_id=$bet_id";
    $exeUpdateBet = $database->query($qryUpdateAns);

      while($row=$database->fetch($exeUserId))
      {
        $userid  = $row['user_id'];

        // print_r($row);
          $qrySelectDevice = "SELECT * FROM ".$tabUser." WHERE user_id=".$userid ;
          $exeSelectDevice = $database->query($qrySelectDevice);
          $result = $database->fetch($exeSelectDevice);

          
          $qryUserBets = "SELECT * FROM ".$tabBets." WHERE bet_id=$bet_id";
          $exeUserbets = $database->query($qryUserBets);
          $resultUserBets = $database->fetch($exeUserbets);
          $cat_id = $resultUserBets['category_id'];
          $betDetails = $resultUserBets['bet_details'];

        if($row['bet_option']==$answer)
        {
          $winner = $userid;
          $betAmount = $row['bet_amount'];
          $successDeviceToken[]  = $result['device_token'];
          //echo $result['device_token'];
          $qryBetOption = "SELECT * FROM ".$tabBets." WHERE bet_id=".$bet_id;
          $exeBetOpt = $database->query($qryBetOption);
          $betResult = $database->fetch($exeBetOpt);
          if($answer==$betResult['option1'])
          {
            $bettingPercentage = $betResult['opt1percent'];
          }
          else if($answer==$betResult['option2'])
          {
            $bettingPercentage = $betResult['opt2percent'];
          }
          else if($answer==$betResult['option3'])
          {
            $bettingPercentage = $betResult['opt3percent'];
          }
          else if($answer==$betResult['option4'])
          {
            $bettingPercentage = $betResult['opt4percent'];
          }

          $amount = $betAmount * $bettingPercentage;
          $amount =$amountArray[]= (int) $amount ;

          $prevAmount = $result['coins']; 
          $totalAmount = $prevAmount + $amount ;
          $totalAmountArray[] = $totalAmount;
            $qryUpdateAmt = "UPDATE   ".$tabUser." SET coins = '{$totalAmount}'  WHERE user_id=".$winner;
            $exeUpdateAmt = $database->query($qryUpdateAmt);
          //find ctegory id  

          // updating correct answer in bet table 
          // $qryUpdateAns = "UPDATE ".$tabBets." SET correct_option='".$answer."' WHERE bet_id=$bet_id";
          // $exeUpdateBet = $database->query($qryUpdateAns);
           
            $qrySeleUserExp = "SELECT * FROM ".$tabUserExp." WHERE user_id=$winner AND category_id=$cat_id ";

            $exeUserExp = $database->query($qrySeleUserExp);
            $userExp  = $database->fetch($exeUserExp);
            $exp = $userExp['exp'];
            $totalExp = $exp + $expAmount  ;
            $totalUserExp = $result['user_exp'] + $expAmount ;
            $qryTotalExp = "UPDATE ".$tabUser." SET user_exp={$totalUserExp} WHERE user_id=$winner";
            $exeUpdateTotalexp = $database->query($qryTotalExp);
            $qryUpdateExp =  "UPDATE ".$tabUserExp." SET exp='{$totalExp}' WHERE user_id=$winner AND category_id ={$cat_id}" ; 
            $exeUpdateExp = $database->query($qryUpdateExp);
        }
        else
        {
          $failureDeviceToken[]= $result['device_token'];
        }
      }

     
    // print_r($failureDeviceToken);
    // print_r("</br>");
    // print_r($successDeviceToken);
     

     foreach ($failureDeviceToken as $value) {
      
      $deviceToken = $value ;
      
      $qryGetUser =   "SELECT user_id FROM user WHERE device_token='{$value}'";
      //echo $qryGetUser;
      $exeGetUser = $database->query($qryGetUser);
      $fetchInfo = $database->fetch($exeGetUser);
      $userNotify = $fetchInfo['user_id'];

      $qryNotify = "SELECT notify_results FROM ".$tabSettings."  WHERE  user_id={$userNotify}";
      //echo $qryNotify;
      $exeQryNotify = $database->query($qryNotify);
      $fetchData = $database->fetch($exeQryNotify);
      $notify = $fetchData['notify_results'];
      //echo $notify;
      if($notify)
      {
          $qryGetBetAmount = "SELECT bet_amount FROM user_bets  WHERE bet_id={$bet_id}";
          $exeBetAmount = $database->query($qryGetBetAmount);
          $fetchBetAmount = $database->fetch($exeBetAmount);
          $lostBetAmount = $fetchBetAmount['bet_amount'];

          $action = "lost" ;
          $message = "You have just lost a bet" ;
          $body['aps'] = array(
              'badge' => 1,
            'alert' => $message,
            'sound' => 'chime_bell_timer.wav',
            'bet_id'=>$bet_id,
            'total no of coins'=>(int)$lostBetAmount,
            'bet_details'=>$betDetails,
            'action'=>$action,
            "cat_id" =>$cat_id 
            );

          // Encode the payload as JSON
          $payload = json_encode($body); 
          
          // Build the binary notification
          // $deviceToken = 1 ; //comment  this line before code goes live  
         
          

          // Send it to the server
          if($deviceToken&&($deviceToken!='(null)'))
          {

            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            $result = fwrite($fp, $msg, strlen($msg));
            if (!$result)
              $responseMeassage["message"] = "failure" ;
                // 'Message not delivered' . PHP_EOL;
            else
              // echo 'Message successfully delivered' . PHP_EOL;
              $responseMeassage["message"]="success";

            echo json_encode($responseMeassage);
          }
      }


     }
     
      // for winner

     $counter = 0;
      
    if(!empty($successDeviceToken))
    {
      foreach ($successDeviceToken as $value) {
        $deviceToken = $value ; 
        $qryGetUser =   "SELECT user_id FROM ".$tabUser." WHERE device_token='{$deviceToken}'"; 
        $exeGetUser = $database->query($qryGetUser);
        $fetchInfo = $database->fetch($exeGetUser);
        $userNotify = $fetchInfo['user_id'];
        $qryNotify = "SELECT notify_results FROM ".$tabSettings."  WHERE  user_id={$userNotify}";
        $exeQryNotify = $database->query($qryNotify);
        $fetchData = $database->fetch($exeQryNotify);
        $notify = $fetchData['notify_results'];
        if($notify)
        {
            $action = "win" ;
            $message = "You have just won '{$amountArray[$counter]}' coins in a bet" ;
            $body['aps'] = array(
                'badge' => 1,
              'alert' => $message,
              'sound' => 'chime_bell_timer.wav',
              'bet_id'=>$bet_id,
              'bet_details'=>$betDetails,
              'action'=>$action,
              'total no of coins'=>(int)$totalAmountArray[$counter],
              "cat_id" =>$cat_id 
              );

            $payload = json_encode($body); 
            
             


            if($deviceToken&&($deviceToken!='(null)'))
            {
              $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
              $result = fwrite($fp, $msg, strlen($msg));
              if (!$result)
                $responseMeassage["message"] = "failure" ;
                  // 'Message not delivered' . PHP_EOL;
              else
                // echo 'Message successfully delivered' . PHP_EOL;
                $responseMeassage["message"]="success";
                
                echo json_encode($responseMeassage);

            }
          } 
          $counter++;
      }
        

      
    }

    // echo json_encode($responseMeassage);


    // Close the connection to the server
    fclose($fp);
  }
  $queryBets = "SELECT match_id FROM bets WHERE match_id <>0 AND (correct_option IS NULL or correct_option ='') ";
  $resultBets = mysqli_query($connection, $queryBets);
  echo "<pre>";

  while ($betwinner = mysqli_fetch_assoc($resultBets)) {
    print_r($betwinner);
    $matchIds[] = $betwinner["match_id"];
    
  }
  $winnerList = curl_download_live("http://www.tipgin.net/datav2/accounts/mbulut/soccer/livescore/livescore.xml", $matchIds);

  echo "\nWinners List is as follows: \n";
  print_r($winnerList);

  foreach ($winnerList as $key => $value) {
    declareWinner($key, $value, $database);
  }

echo "</pre>";

?>