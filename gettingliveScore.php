<?php

    function curl_download($Url){
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
           /*if($league->attributes()->country == "europe")
            {
                echo "europe";
            }*/
            foreach ($league->match as $match) {
               if($Url == "http://www.tipgin.net/datav2/accounts/mbulut/soccer/fixtures/france.xml" ){
                if($match->attributes()->id == "4102818"){
                    $date=$match->attributes()->date;
                    $dateInRequiredFormat=str_replace(".","-",$date);
                    $time=$match->attributes()->time;
                    $dateAndTimeArray = array($dateInRequiredFormat,'',$time.':00');
                    $dateAndTime=join(" ",$dateAndTimeArray);
                    echo "date and time:  ".$dateAndTime."<br>";
                    echo "home:  ".$match->home->attributes()->name."<br>";
                    echo "away:  ".$match->away->attributes()->name."<br>";
                }
               }
               elseif($Url =="http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/france.xml")
               {
                 if($match->attributes()->id == "4102818"){
                    if($match->odds->type->attributes()->name == "1x2"){

                        foreach($match->odds->type->bookmaker as $bookmaker)
                        {
                            if($bookmaker->attributes()->name == "Bet365"){
                                foreach($bookmaker->odd as $odd){
                                    echo "name  ".$odd->attributes()->name."  value  ".$odd->attributes()->value."<br>";
                                    
                                 }
                            }
                        }
                      
                    }
                }
               }
              elseif($Url =="http://www.tipgin.net/datav2/accounts/mbulut/soccer/livescore/livescore.xml")
              {
                
               if($match->attributes()->id == "4212304"){
                    if($match->attributes()->status == "FT"){
                    $score_home=$match->home->attributes()->goals;
                    $score_away=$match->away->attributes()->goals;
                   if((int)$score_home > (int)$score_away )
                    {
          $winner = $match->home->attributes()->name;
                     echo  $winner;
                     }else
                     {
                      $winner = $match->home->attributes()->away;
                      echo  $winner;
                      }
                    }else{
                        echo "match is still not over";
                    }
                }

              }

                
            }
        }
        



























      
    }

      //curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/fixtures/france.xml");
      curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/livescore/livescore.xml");

      //curl_download("http://www.tipgin.net/datav2/accounts/mbulut/soccer/odds/france.xml")
    
?>