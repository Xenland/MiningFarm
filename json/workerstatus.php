{
<?php
// Load Linkage Variables //
$dir = dirname(__FILE__);
$dir		= str_replace("/json", "", $dir);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Include hashing functions
include($functions);

connectToDb();
//Get worker status and output them in JSON format
	//Find out which workers belong to this API token
		$apiToken = $_GET["apiToken"];
		$userApiTokenQ = mysql_query("SELECT `id` FROM `websiteUsers` WHERE `apiToken` = '".$apiToken."' LIMIT 0,1");
		$userApiToken = mysql_fetch_object($userApiTokenQ);
		
	//List workers
		//Get time 5 minutes ago
			$timeFiveMinutesAgo = time();
			$timeFiveMinutesAgo -= 60*5;
			
		$workers = mysql_query("SELECT `username` FROM `pool_worker` WHERE `associatedUserId` = '".$userApiToken->id."'");
		$numWorkers = mysql_num_rows($workers);
		
		$i=0;
		while($worker = mysql_fetch_array($workers)){
			$i++;
			//Get this workers infomation
				//Retireve Average Mhash/s
					$getMhashes = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `username` = '".$worker["username"]."' AND `timestamp` >= '$timeFiveMinutesAgo' ORDER BY `timestamp` DESC");
					$numHashes = mysql_num_rows($getMhashes);
					$totalMhashes = 0;
					while($mhashes = mysql_fetch_array($getMhashes)){
						$totalMhashes += $mhashes["mhashes"];
					}
					
					//Prevent division by zero
					if($totalMhashes > 0 && $numHashes > 0){
						$averageHashes = $totalMhashes/$numHashes;
					}else if($totalMhashes == 0 && $numHashes == 0){
						$averageHashes = 0;
					}
					
				//Active
					if($averageHashes >= 1){
						$workerActive = "Connected";
					}else if($averageHashes < 1){
						$workerActive = "Disconnected";
					}
?>
		"User":{
			"username":"<?php echo $worker["username"];?>",
			"currSpeed":"<?php echo $averageHashes;?>",
			"status":"<?php echo $workerActive?>"			
		}
<?php
			//Echo a "," to delimit the data(only if there is more data to be displayed)
				if($i < $numWorkers){
					echo ",";
				}
		}
?>
}