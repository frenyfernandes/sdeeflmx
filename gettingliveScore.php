<?php
	
	// get all the undeclared bets match_id.
	// Hit the live feed
	//    search all that match_ids in xml
	//    IF(match_id's status is found to be FT) declare the results
	
	// include("connect.php");
	// 
	require_once('./lib/connection.php');
	require_once('./lib/database.php');

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

	  $answer	=	$winner;



	   $tabUserBets 	= "user_bets" ;
	   $tabBets 		= "bets";
	   $tabUser 		= "user";
	   $tabUserExp 	= "user_exp" ;
	   $action 		= "lost";
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
	  	
	  	$qryGetUser =  	"SELECT user_id FROM user WHERE device_token='{$value}'";
	  	//echo $qryGetUser;
	  	$exeGetUser = $database->query($qryGetUser);
	  	$fetchInfo = $database->fetch($exeGetUser);
	  	$userNotify = $fetchInfo['user_id'];

	  	$qryNotify = "SELECT notify_results FROM ".$tabSettings."  WHERE 	user_id={$userNotify}";
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
	  	 	$qryGetUser =  	"SELECT user_id FROM ".$tabUser." WHERE device_token='{$deviceToken}'"; 
	  	 	$exeGetUser = $database->query($qryGetUser);
	  	 	$fetchInfo = $database->fetch($exeGetUser);
	  	 	$userNotify = $fetchInfo['user_id'];
	  	 	$qryNotify = "SELECT notify_results FROM ".$tabSettings."  WHERE 	user_id={$userNotify}";
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

	$matchIds = array();
  $betIds = array();


	$queryBets = "SELECT match_id FROM bets WHERE match_id <>0 AND (correct_option IS NULL or correct_option ='') ";
	$resultBets = mysqli_query($connection, $queryBets);
	echo "<pre>";

	while ($row = mysqli_fetch_assoc($resultBets)) {
		print_r($row);
		$matchIds[] = $row["match_id"];
		
	}


	


	$winnerList = curl_download_live("http://www.tipgin.net/datav2/accounts/mbulut/soccer/livescore/livescore.xml", $matchIds);

	echo "\nWinners List is as follows: \n";
	print_r($winnerList);

	foreach ($winnerList as $key => $value) {
		declareWinner($key, $value, $database);
	}

	
	
	echo "</pre>";




	
	
		
?>