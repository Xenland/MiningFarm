<?php
//Comment the following line when debuging this page.
set_time_limit(120);

// Load Linkage Variables //
$dir = dirname(__FILE__);
$dir		= str_replace("/req/cronjob", "", $dir);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Load Functions
include($functions);


//This page will generate stats data and plug it into the database
connectToDb();


//Update timestamps in `shares`//
$listSharesQ = mysql_query("SELECT `time`, `id` FROM `shares` WHERE `epochTimestamp` = '0' ORDER BY `id` DESC")or die(mysql_error());
while($share = mysql_fetch_array($listSharesQ)){
		//Update epochTimestamp
		//Split the wierd timestamp set by MySql
		$splitInputTimeDate = explode(" ", $share["time"]);
		$splitInputDate = explode("-", $splitInputTimeDate[0]);
		$splitInputTime = explode(":", $splitInputTimeDate[1]);
		
		//Make wierd timestamp into a regular Unixtimestamp
		$unixTime = mktime($splitInputTime[0], $splitInputTime[1], $splitInputTime[2], $splitInputDate[1], $splitInputDate[2], $splitInputDate[0]);
		//Update
		mysql_query("UPDATE `shares` SET `epochTimestamp` = '".$unixTime."' WHERE `id` = '".$share["id"]."' LIMIT 1");
		
}


/////////////////////////////////////////////////////////////////////
/////////////// Generate Mhash/s ////////////////////////////////////
$recordedTime	= time();
$fifteenMinutesAgo = $recordedTime;
$fifteenMinutesAgo -= 60*15;


//Get all `pool_workers` and add there current MHash/s to the stats
	$poolWorkersQ = mysql_query("SELECT `id`, `associatedUserId`, `username` FROM `pool_worker`");
	
while($worker = mysql_fetch_array($poolWorkersQ)){
	//Calculate Mhash/s based on the share information in the last give minutes
		$sharesQ = mysql_query("SELECT `id`, `epochTimestamp` FROM `shares` WHERE `username` = '".$worker["username"]."' AND `epochTimestamp` >= $fifteenMinutesAgo");
		$numShares = mysql_num_rows($sharesQ);
		if($numShares > 0){
			//Get first share timestamp from the last five minutes
				$firstTimestamp = mysql_query("SELECT `epochTimestamp` FROM `shares` WHERE `id` = '".$worker["id"]."' AND `epochTimestamp` >= $fifteenMinutesAgo");
			
			
			//Hashes per second = Number of shares / timedelta * hashspace
				$hashSpace = 4294967296;
				$hashesPerSecond =  $numShares / (60*15) * $hashSpace;
				
			//Convert to Mhashes, round then upload to server
				$hashesPerSecond /= 1024;
				$hashesPerSecond /= 1024;
				$hashesPerSecond = floor($hashesPerSecond);
				
			//Get efficiency
				$efficienctSharesQ = mysql_query("SELECT `id` FROM `shares` WHERE `username` = '".$worker["username"]."' AND `epochTimestamp` >= $fifteenMinutesAgo AND `our_result` = 'Y'");
				$totalSharesQ = mysql_query("SELECT `id` FROM `shares` WHERE `username` = '".$worker["username"]."' AND `epochTimestamp` >= $fifteenMinutesAgo");
				
				$numEfficient = mysql_num_rows($efficienctSharesQ);
				$totalShares = mysql_num_rows($totalSharesQ);
				
				//Efficency = (Valid Shares / Total Shares ) * 100%
				$efficency = ($numEfficient/$totalShares)*100;
				
			//Insert into database
				mysql_query("INSERT INTO `stats_userMHashHistory` (`username`, `mhashes`, `efficiency`, `timestamp`) VALUES('".$worker["username"]."', '".$hashesPerSecond."', '".$efficency."', '".$recordedTime."')")or die(mysql_error());
		}else{
			//Insert into database 0mhash/s
				mysql_query("INSERT INTO `stats_userMHashHistory` (`username`, `mhashes`, `efficiency`, `timestamp`) VALUES('".$worker["username"]."', '0', '0', '".$recordedTime."')")or die(mysql_error());
		}
	
}








//Get average Mhash for the entire pool
	$poolAverageHashQ = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `mhashes` > 0 AND `timestamp` = '".$recordedTime."'")or die(mysql_error());
	$numPoolHashRows = mysql_num_rows($poolAverageHashQ);
	$averagePoolHash = 0;

	while($poolHash = mysql_fetch_array($poolAverageHashQ)){
		$averagePoolHash += $poolHash["mhashes"];
	}
	if($averagePoolHash > 0 && $numPoolHashRows > 0){
		$averagePoolHash = $averagePoolHash/$numPoolHashRows;
	}
	
	
	
	
	
	
	
	

//Get total Mhash for entire pool
	$poolTotalHashQ = mysql_query("SELECT DISTINCT `username` FROM `stats_userMHashHistory` WHERE `mhashes` > 0 AND `timestamp` = '".$recordedTime."'")or die(mysql_error());
	$poolTotalRows = mysql_num_rows($poolTotalHashQ);
	
	//Loop through every username get the hash and add it up to the total
		$totalPoolHash = 0;
		while($user = mysql_fetch_array($poolTotalHashQ)){
			//Get this users total hash
				$totalHashQ = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `username` = '".$user["username"]."' AND `timestamp` = '".$recordedTime."'")or die(mysql_error());
				$totalHash = mysql_fetch_object($totalHashQ);
				$totalPoolHash += $totalHash->mhashes;
		}
		
	//Add pool average & pool total to table
		mysql_query("INSERT INTO `stats_poolMHashHistory` (`timestamp`, `averageMhash`, `totalMhash`)
								VALUES('$recordedTime', '$averagePoolHash', '$totalPoolHash')")or die(mysql_error());
		
		
		
		
		
		
		
		
		
		
						
//Get top sharers for the entire pool (per username)
	//Delete everything in the stats_topSharers table before we continue
		mysql_query("DELETE FROM `stats_topSharers`")or die(mysql_error());
	//Get list of users
		$getUsersList = mysql_query("SELECT `id`, `username` FROM `websiteUsers`")or die(mysql_error());
		while($user = mysql_fetch_array($getUsersList)){
			//Get total of shares for this user
				$totalSharesQ = mysql_query("SELECT `id` FROM `shares_history` WHERE `username` LIKE '".$user["username"].".%'")or die(mysql_error());
				$totalShares = mysql_num_rows($totalSharesQ);
				
				$totalCurrentQ = mysql_query("SELECT `id` FROM `shares` WHERE `username` LIKE '".$user["username"].".%'")or die(mysql_error());
				$totalCurrent = mysql_num_rows($totalCurrentQ);
				
				$totalShares += $totalCurrent;
				
			//Insert into stats_topSharers
				mysql_query("INSERT INTO `stats_topSharers` (`userId`, `shares`) VALUES('".$user["id"]."', '".$totalShares."')")or die(mysql_error());
		}









//Get top hasher for the entire pool (per username)
		//Delete everything in the stats_topHashers table before we continue
			mysql_query("DELETE FROM `stats_topHashers`")or die(mysql_error());
			
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
								mysql_query("INSERT INTO `stats_topHashers` (`userId`, `totalHashes`) VALUES('".$user["id"]."', '".$totalHashingPower."')")or die(mysql_error());
							}
				}

//Purge stats longer then one hour ago
	$thirtyMinsAgo = time();
	$thirtyMinsAgo -= 60*30;

mysql_query("DELETE FROM `stats_poolMHashHistory` WHERE `timestamp` <= '$thirtyMinsAgo'");
mysql_query("DELETE FROM `stats_userMHashHistory` WHERE `timestamp` <= '$thirtyMinsAgo'");
?>