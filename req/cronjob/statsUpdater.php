<?php
$startTime = gettimeofday(2);
//Comment the following line when debuging this page.
set_time_limit(500);
ini_set('memory_limit', '512M');

// Load Linkage Variables //
$dir = dirname(__FILE__);
$dir		= str_replace("/req/cronjob", "", $dir);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Load Functions
include($functions);


//This page will generate stats data and plug it into the database
connectToDb();


/////////////////////////////////////////////////////////////////////
/////////////// Generate Mhash/s ////////////////////////////////////
$recordedTime	= time();
$fifteenMinutesAgo = $recordedTime;
$fifteenMinutesAgo -= 60*15;

//Entire pool data
     $entireNumHashRows = 0;
     $entirePoolMhash = 0;
     $entirePoolAverageMhash = 0;
     $entireSharesThisRound = 0;
     
//Get all `pool_workers` and add there current MHash/s to the stats
     //First get the list of workers who has been active for the past 15 minutes
      $activeWorkersQ = mysql_query("SELECT `username`, `our_result` FROM `shares` WHERE `time` >= '$fifteenMinutesAgo'")or die(mysql_error());
      
      //Predefine variables
        $usernameArray   = array();
        $shareCount      = array();
        $validShareCount = array();
      
      while($shareData = mysql_fetch_array($activeWorkersQ)){
         $userArrayLocation = '';
        //Is this user already in the array
          $lengthOfUsernameArray = count($usernameArray);
          
          $foundMatch = 0;
          $foundIndex = 0;
          for($n=0; $n < $lengthOfUsernameArray; $n++){
          
            if($foundMatch == 0){
            
              if($usernameArray[$n] == $shareData["username"]){
              
                $foundMatch = 1;
                $foundIndex = $n;
              }
            }
          }
          
          if($foundMatch == 1){
            //This username is already inside of the array so just hit count it
              $shareCount[$foundIndex] += 1;
              if($shareData["our_result"] == "Y"){
                $validShareCount[$foundIndex] += 1;
              }
              
          }else if ($foundMatch == 0){
            //This username is not in the array yet, make a new hit counter
              $usernameArray[] = $shareData["username"];
              $shareCount[] = 1;
              if($shareData["our_result"] == "Y"){
                $validShareCount [] = 1;
              }
          }
      }
     
      //Loop through every username and generate stats details
        $totalUsernames = count($usernameArray);
        for($i=0; $i < $totalUsernames; $i++){
          //Hashes per second = Number of shares / timedelta * hashspace
            $numShares = $shareCount[$i];
            $numValidShares = $validShareCount[$i];
            $hashesPerSecond =  $numShares / (60*15) * 4294967296;
            
             //Convert to Mhashes, round then upload to server
               $hashesPerSecond /= 1024;
               $hashesPerSecond /= 1024;
               $hashesPerSecond = floor($hashesPerSecond);
                    
          //Efficency = (Valid Shares / Total Shares ) * 100%
             $efficency = ($numValidShares/$numShares)*100;
             
             //Insert into database
                    //Insert "," if neccessary
                      if($i > 0){
                        $insertMhashQ .= ",";
                       }
                    $insertMhashQ .= "('".$usernameArray[$i]."', '".$hashesPerSecond."', '".$efficency."', '".$recordedTime."')";
                    
             //Add to entire pool data
               $entireNumHashRows++;;
               $entirePoolMhash += $hashesPerSecond;
               $entireSharesThisRound += $validShareCount[$i];
          
        }
        
//Calc entire pool average mhash
     if($entirePoolMhash > 0 && $entireNumHashRows > 0){
       $entirePoolAverageMhash = $entirePoolMhash/$entireNumHashRows;
     }
     
//Commit $insertMhashQ query
	if($insertMhashQ != ''){
		mysql_query('INSERT INTO `stats_userMHashHistory` (`username`, `mhashes`, `efficiency`, `timestamp`) VALUES'.$insertMhashQ)or die(mysql_error());
	}
	
//Add pool average & pool total to table
     mysql_query("INSERT INTO `stats_poolMHashHistory` (`timestamp`, `averageMhash`, `totalMhash`)
							               VALUES('$recordedTime', '$entirePoolAverageMhash', '$entirePoolMhash')")or die(mysql_error());
//update total shares this round
     mysql_query("UPDATE `websiteSettings` SET `shares_this_round` = '".$entireSharesThisRound."'");
		
			
		
		
				
//Get top sharers for the entire pool (per username)
	//Get list of users
		$getUsersList = mysql_query("SELECT `id`, `username` FROM `websiteUsers`")or die(mysql_error());
		while($user = mysql_fetch_array($getUsersList)){
			//Get total of shares for this user
				$totalSharesQ = mysql_query("SELECT `id` FROM `shares` WHERE `username` LIKE '".$user["username"].".%'")or die(mysql_error());
				$totalShares = mysql_num_rows($totalSharesQ);
				
			//Insert into stats_topSharers
				//Check if this user is already inside of the stats
					$alreadyTopSharerQ = mysql_query("SELECT `id` FROM `stats_topSharers` WHERE `userId` = '".$user["id"]."' LIMIT 0,1")or die(mysql_error());
					$areadyTopSharer = mysql_num_rows($alreadyTopSharerQ);
					
					if($areadyTopSharer == 0){
						mysql_query("INSERT INTO `stats_topSharers` (`userId`, `shares`) VALUES('".$user["id"]."', '".$totalShares."')")or die(mysql_error());
					}else if($areadyTopSharer == 1){
						mysql_query("UPDATE `stats_topSharers` SET `shares` = '".$totalShares."' WHERE `userId`= '".$user["id"]."'")or die(mysql_error());
					}

		}









//Get top hasher for the entire pool (per username)
		//Delete everything in the stats_topHashers table before we continue
			
		//First get the last timestamp to use with all hashers
			$getLastTimestampQ = mysql_query("SELECT `timestamp` FROM `stats_userMHashHistory` ORDER BY `timestamp` DESC LIMIT 0,1");
			$lastTimestamp = mysql_fetch_object($getLastTimestampQ);

			//Get list of users
				$getUsersList = mysql_query("SELECT `id`, `username` FROM `websiteUsers`")or die(mysql_error());
				
				while($user = mysql_fetch_array($getUsersList)){
					//Check if user has any Mhashes, if YES then add up every worker for this user and add to stats_topHashers
						$getWorkersQ = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `username` LIKE '".$user["username"].".%' AND `timestamp` = '".$lastTimestamp->timestamp."'")or die(mysql_error());
						$totalHashingPower = 0;
						while($worker = mysql_fetch_array($getWorkersQ)){
							$totalHashingPower += $worker["mhashes"];
						}
		
						
						//If hashing power is greater then zero then insert into the stats_topHashers table
							if($totalHashingPower > 0){
								//Check if this user is already in the list, if not INSERT if they are UPDATE
									$inTopHashersListQ = mysql_query("SELECT `id` FROM `stats_topHashers` WHERE `userId` = '".$user["id"]."'")or die(mysql_error());
									$inTopHashersList = mysql_num_rows($inTopHashersListQ);
									
									if($inTopHashersList == 0){
										mysql_query("INSERT INTO `stats_topHashers` (`userId`, `totalHashes`) VALUES('".$user["id"]."', '".$totalHashingPower."')")or die(mysql_error());
									}else if($inTopHashersList == 1){
										mysql_query("UPDATE `stats_topHashers` SET `totalHashes` = '".$totalHashingPower."' WHERE `userId` = '".$user["id"]."' LIMIT 1")or die(mysql_error());
									}
							}
				}
				
				
				
//Purge stats longer then one hour ago
	$thirtyMinsAgo = time();
	$thirtyMinsAgo -= 60*30;

mysql_query("DELETE FROM `stats_poolMHashHistory` WHERE `timestamp` <= '$thirtyMinsAgo'");
mysql_query("DELETE FROM `stats_userMHashHistory` WHERE `timestamp` <= '$thirtyMinsAgo'");

$lengthOfScript = gettimeofday(2);
$lengthOfScript -= $startTime;

echo '('.($lengthOfScript).")";
?>