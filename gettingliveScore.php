<?php
	
	// get all the undeclared bets match_id.
	// Hit the live feed
	//    search all that match_ids in xml
	//    IF(match_id's status is found to be FT) declare the results
	
	include("connect.php");

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
							echo $matchId . ": " . "match is still not over". "<br/>";
						}
					}
				}
			}
		}
		return $winners;
	}


	$matchIds = array();

	$queryBets = "SELECT match_id FROM bets WHERE match_id <>0 AND correct_option = ''";
	$resultBets = mysqli_query($connection, $queryBets);
	echo "<pre>";

	while ($row = mysqli_fetch_assoc($resultBets)) {
		print_r($row);
		$matchIds[] = $row["match_id"];
		
	}

	foreach ($matchIds as $key => $value) {
		echo $key . "=>" . $value . "<br>";
	}

	$winnerList = curl_download_live("http://www.tipgin.net/datav2/accounts/mbulut/soccer/livescore/livescore.xml", $matchIds);

	echo "\nWinners List is as follows: \n";
	print_r($winnerList);

	
	
	echo "</pre>";




	
	
		
?>