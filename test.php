<?php  

	$date1 = "15-03-2015 19:30:00";
	$date=date_create($date1);
	date_add($date,date_interval_create_from_date_string("110 minutes"));
	echo date_format($date,"Y-m-d H:i:s");
	
	echo "<br>";

	$selectedTime = "09:30";
	$endTime = strtotime("+15 minutes", strtotime($selectedTime));
	echo gmdate('h:i:s', $endTime);
	echo "<br>";


	$current_time = strtotime(date("H:i")); // or strtotime(now);
	$match_start  = strtotime("14:30");
	$match_end    = strtotime("+110 minutes", $match_start);
	echo gmdate('h:i:s', $match_end);
?>