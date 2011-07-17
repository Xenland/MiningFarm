<?php
//Comment the following line when debuging this page.
//error_reporting(0);
set_time_limit(500);
$startTime = time();

// Load Linkage Variables //
	$dir = dirname(__FILE__);
	$dir		= str_replace("/req/cronjob", "", $dir);
	$req 		= $dir."/req/";
	$functions	= $req."functions.php";

//Load Functions
	include($functions);

//Load bitcoind function
	include($bitcoind);

//Connect to database
	connectToDb();
	

//Only run script if the script is being ran from the same server or from an admin


//Set adminFee
	$serverFee = getAdminFee();

//Open a bitcoind connection
	$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Get some variables
	$transactions = $bitcoinController->query("listtransactions");

//Go through all the transactions check if there is 50BTC inside
	$numAccounts = count($transactions);
	for($i = 0; $i < $numAccounts; $i++){
		if($transactions[$i]["category"] == "generate" || $transactions[$i]["category"] == "immature"){
			//At this point we may or may not have found a block,
			//Check to see if this account addres is already added to `networkBlocks`
				$accountExistsQ = mysql_query("SELECT `id` FROM `networkBlocks` WHERE `txid` = '".$transactions[$i]["txid"]."' ORDER BY `blockNumber` DESC LIMIT 0,1")or die(mysql_error());
				$accountExists = mysql_num_rows($accountExistsQ);
	
				//If the account dosen't exist that means we found a block, now add it to the database so we can track the confirms
					if(!$accountExists){
						//Get last empty block so we can input it the address for confirm tracking
							$lastEmptyBlockQ = mysql_query("SELECT `id`, `blockNumber` FROM `networkBlocks` WHERE `txid` = '' ORDER BY `blockNumber` DESC LIMIT 0,1");
							$lastEmptyBlockObj = mysql_fetch_object($lastEmptyBlockQ);
							$lastEmptyBlock = $lastEmptyBlockObj->id;
							$lastEmptyBlockNumber = $lastEmptyBlockObj->blockNumber;

							$insertBlockSuccess = mysql_query("UPDATE `networkBlocks` SET `txid` = '".$transactions[$i]["txid"]."' WHERE `id` = '$lastEmptyBlock'")or die(mysql_error());
							if($insertBlockSuccess){
								//Move all `shares` into `shares_history`
									//Get list of `shares`
										$listOfSharesQ = mysql_query("SELECT `id`, `time`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution` FROM `shares` ORDER BY `id` DESC");
										
										$insertQuery = "INSERT INTO `shares_history` (`time`, `blockNumber`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution`) VALUES ";
										$i = 0;
										while($shares = mysql_fetch_array($listOfSharesQ)){
											$i++;
											if($i==1){
												$deleteId = $shares["id"];
											}else if($i > 1){
												$insertQuery .=",";
											}

											//Split the wierd timestamp set by MySql
												$splitInputTimeDate = explode(" ", $shares["time"]);
												$splitInputDate = explode("-", $splitInputTimeDate[0]);
												$splitInputTime = explode(":", $splitInputTimeDate[1]);
				
											//Make wierd timestamp into a regular Unixtimestamp
												$unixTime = mktime($splitInputTime[0], $splitInputTime[1], $splitInputTime[2], $splitInputDate[1], $splitInputDate[2], $splitInputDate[0]);
										
											$insertQuery .= "('$unixTime', '".$lastEmptyBlockNumber."', '".$shares["rem_host"]."','".$shares["username"]."','".$shares["our_result"]."','".$shares["upstream_result"]."', '".$shares["reason"]."','".$shares["solution"]."')";
										}

										//Commence the $insertQuery
											$moveSharesToHistory = mysql_query($insertQuery)or die(mysql_error());
							}
					}
			}
		
	}



//Go through all the transctions from bitcoind and update their confirms associated with their `networkBlock`
	//Get server fee before continuing....
		$getFeeAddress = mysql_query("SELECT `serverFeeRemoteAddress` FROM `websiteSettings` LIMIT 0,1");
		$feeAddress = mysql_fetch_object($getFeeAddress);
		$feeAddress = $feeAddress->serverFeeLocalAddress;
		
	//Loop through bitcoin wallet addresses and update `networkBlocks` aswell as set the `serverFeeAccountBalnce`
		for($i = 0; $i < $numAccounts; $i++){
			//Check to see if this address was one of the winning addresses from `networkBlocks`
				$txId = $transactions[$i]["txid"];
				$winningAccountQ = mysql_query("SELECT `id`, `serverFeeCollected` FROM `networkBlocks` WHERE `txid` = '$txId' LIMIT 0,1")or die(mysql_error());
				$winningAccount = mysql_num_rows($winningAccountQ);
				
				if($winningAccount > 0){
					//This is a winning account
						$winningAccountObj	= mysql_fetch_object($winningAccountQ);
						$winningId		= $winningAccountObj->id;
						$confirms		= $transactions[$i]["confirmations"];
						$orphan			= $transactions[$i]["category"];
						
						if($orphan == "orphan"){
							$orphan = 1;
						}else if($orphan != "orphan"){
							$orphan = 0;
						}
	
						//Update X amount of confirms
							$updatedConfirms = mysql_query('UPDATE `networkBlocks` SET `confirms` = "'.$confirms.'", `orphan` = "'.$orphan.'" WHERE `id` = "'.$winningId.'"')or die(mysql_error());
							if(!$updatedConfirms){
								echo "failed to update[Confirms]";
							}
						
						//Take out server fee for all valid blocks						
							if($confirms >= 120 && $orphan == 0){
								//Has this block already had its admin fee sucked out of it?
									if($winningAccountObj->serverFeeCollected == 0){
										//Collect Fee
											$totalFee = 50*($serverFee*.01);
											
										//Add fee to the admin account
											mysql_query("UPDATE `websiteSettings` SET `serverFeeAccountBalance` = `serverFeeAccountBalance`+$totalFee")or die(mysql_error());
											
									}
							}
				}
		}

//Go through every user in `websiteUsers`, collect all their users and award them indiviually
	$userList = mysql_query("SELECT `id`, `username` FROM `websiteUsers`")or die(mysql_error());
	
		//Looping through every user
			while($user = mysql_fetch_array($userList)){
				//Find out if there is any uncounted shares this user has that needs to be counted
					$uncountedSharesQ = mysql_query("SELECT `id` FROM `shares_history` WHERE `shareCounted` = '0' AND `username` LIKE '".$user["username"].".%' LIMIT 1");
					$uncountedShares = mysql_num_rows($uncountedSharesQ);
					
					if($uncountedShares >= 1){
						//Get list of blocks that this user has shares in
							$blocksQ = mysql_query("SELECT DISTINCT `blockNumber` FROM `shares_history` WHERE `shareCounted` = '0' AND `username` LIKE '".$user["username"].".%'");
							while($block = mysql_fetch_array($blocksQ)){
							
								//Check if the selected block has enough confirms//
									$enoughConfirmsQ = mysql_query("SELECT `confirms` FROM `networkBlocks` WHERE `blockNumber` = '".$block["blockNumber"]."' AND `orphan` = '0'");
									$enoughConfirmsObj = mysql_fetch_object($enoughConfirmsQ);
									$enoughConfirms = $enoughConfirmsObj->confirms;
									
									//Enough confirms?
										if($enoughConfirms >= 120){
											echo "enough Confiremds";
											//Count all the shares this username has, reward them in there account balance, then mark all the shares as counted
												$numTotalUsersSharesQ = mysql_query("UPDATE `shares_history` SET `shareCounted` = '1' WHERE `shareCounted` = '0' AND `blockNumber` = '".$block["blockNumber"]."' AND `username` LIKE '".$user["username"].".%' AND `our_result` = 'Y'")or die(mysql_error());
												$numTotalUsersShares = mysql_affected_rows();
									
											//Get the total amount of valid shares subbmited this round
												$numTotalPoolSharesQ = mysql_query("SELECT `id` FROM `shares_history` WHERE `blockNumber` = '".$block["blockNumber"]."' AND `our_result` = 'Y'")or die(mysql_error());
												$numTotalPoolShares = mysql_num_rows($numTotalPoolSharesQ);
												
												
											//Calculate total reward for this round if this worker subbmitted any work/shares
												if($numTotalPoolShares > 0 && $numTotalUsersShares > 0){
												
													//E for Effort
														$E = $numTotalUsersShares/$numTotalPoolShares;
														
													//P for Pretotal
														$P = $E*50;
														
													//A for admin fee
														$A = $P-($P*($serverFee*0.1));
														
													//total reward
														$totalReward = $A;
														
												//update the owner of all the worker(S) that subbmited work to there balance 
														mysql_query("UPDATE `accountBalance` SET `balance` = `balance`+$totalReward WHERE `userId` = '".$user["id"]."'");
										
												}
										}
							
							}
					}
			}
			
//Check all blocks that are orphans then update them in the stats that all shares in that round are considered counted
	$getOrphandBlocks = mysql_query("SELECT `blockNumber` FROM `networkBlocks` WHERE `orphan` = 1");
	while($orphanBlock = mysql_fetch_array($getOrphandBlocks)){
		//Update all shares as counted for this orphand block
			mysql_query("UPDATE `shares_history` SET `shareCounted` = 1 WHERE `blockNumber` = ".$orphanBlock["blockNumber"]);
	}
	
/*All counted shares should go to the `shares_dead`,
	"Why not delete them save resources yeah!?", 
	"No, it will save space thats about it, and this will provide a 'backup' sort of speak just in case something bad happens the pool operator can execute
	a few commands to recalculate shares and rewards"
*/

//Get all counted shares
	$countedShares = mysql_query("SELECT `id`, `time`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution` FROM `shares_history` WHERE `shareCounted` = '1'");
	$numCountedShares = mysql_num_rows($countedShares);
	
	//Prefix queries
		$insertSharesList = "INSERT INTO `shares_dead` (`time`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution`) VALUES";
		$deleteSharesList = "DELETE FROM `shares_history` WHERE ";
		
		//post fix queries
			$i=0;
			while($share = mysql_fetch_array($countedShares)){
				$i++;
				$insertSharesList .= "('".$share["time"]."', '".$share["rem_host"]."', '".$share["username"]."', '".$share["our_result"]."', '".$share["upstream_result"]."', '".$share["reason"]."', '".$share["solution"]."')";
				$deleteSharesList .= "`id` = '".$share["id"]."'";
				if($i < $numCountedShares){
					$insertSharesList .= ",";
					$deleteSharesList .= " OR ";
				}
			}
			
	//Execute Clean up
		mysql_query($insertSharesList);
		
		if(mysql_affected_rows() > 0){
			mysql_query($deleteSharesList);
		}
		


//Output length of time it takes to run script
$endTime = time();
//echo ($endTime-$startTime)." seconds to run script!";  //Uncomment this line to show time it takes to execute Efficentcy is key!
?>
