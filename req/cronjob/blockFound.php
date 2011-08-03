<?php
//Comment the following line when debuging this page.
//error_reporting(0);
set_time_limit(500);
$startTime = gettimeofday(2);

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
	$resetRoundStats = 0; //Bolean
//Open a bitcoind connection
	$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Get some variables
	$transactions = $bitcoinController->query("listtransactions");

	
//Go through all the transactions check if there is 50BTC inside
     $numAccounts = count($transactions);
     for($i = 0; $i < $numAccounts; $i++){
          //Looping through each transaction found in the wallet.......
          
          
          if($transactions[$i]["category"] == "generate" || $transactions[$i]["category"] == "immature"){
               //We have found a block related transaction
               
               //At this point we may or may not have found a block,
               //Check to see if this account addres is already in the table called `networkBlocks`
                    $accountExistsQ = mysql_query("SELECT `id`, `blockNumber` FROM `networkBlocks` WHERE `txid` = '".$transactions[$i]["txid"]."' ORDER BY `blockNumber` DESC LIMIT 0,1")or die(mysql_error());
                    $accountExists = mysql_num_rows($accountExistsQ);
                    
                         //If the account/block dosen't exist that means we found a block or we are looking through a new database, now add it to the database so we can track the confirms
                         if(!$accountExists){
                         		$blockNumber = 0;
                         		
                         	//Does this generated block's creation timestamp reflect the timestamp of past block findings
                         		$searchForBlockNumber	= mysql_query("SELECT `blockNumber` FROM `shares_history` WHERE `time` = '".$transactions[$i]["time"]."' LIMIT 0,1")or die(mysql_error());
                         		$blockNumberFound		= mysql_fetch_object($searchForBlockNumber);
                         		if($blockNumberFound->blockNumber >= 0){
                         			//Set block Number
                         				$blockNumber = $blockNumberFound->blockNumber;
                         		}else{
                         			//This may or may not be a new generated block. It may just be that it is a new databse, but we shall assume that this is a new block found
                             			 //Get last empty block so we can input it the address for confirm tracking
									$blockNumber = $bitcoinController->getblocknumber();
							}
							
                                   $insertBlockSuccess = mysql_query("INSERT INTO `networkBlocks` (`timestamp`, `blockNumber`, `txid`, `confirms`, `amount`)
                                   												VALUES('".mysql_real_escape_string($transactions[$i]["time"])."', '".$blockNumber."', '".mysql_real_escape_string($transactions[$i]["txid"])."', '".mysql_real_escape_string($transactions[$i]["confirmations"])."', '".mysql_real_escape_string($transactions[$i]["amount"])."')")or die(mysql_error());
                                   if($insertBlockSuccess){
                                   	//Set flag for resetting current round stats
                                   		$resetRoundStats = 1;
                                   		
                                        //Move all `shares` into `shares_history`
                                             //Get list of `shares`
                                                  $listOfSharesQ = mysql_query("SELECT `id`, `time`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution` FROM `shares` ORDER BY `id` DESC");
                                                  
                                                  $insertQuery = "INSERT INTO `shares_history` (`time`, `blockNumber`, `rem_host`, `username`, `our_result`, `upstream_result`, `reason`, `solution`) VALUES ";
                                                	$deleteFromSharesPreQ = "DELETE FROM `shares` WHERE ";
										
                                                	$i = 0;
                                                  while($shares = mysql_fetch_array($listOfSharesQ)){
                                                       $i++;
                                                       if($i==1){
                                                            $deleteId = $shares["id"];
                                                       }else if($i > 1){
                                                            $insertQuery .=",";
												$deleteFromSharesPreQ .= " OR ";
                                                       }
                                                       
                                                       $insertQuery .= "('".$shares["time"]."', '".$blockNumber."', '".$shares["rem_host"]."','".$shares["username"]."','".$shares["our_result"]."','".$shares["upstream_result"]."', '".$shares["reason"]."','".$shares["solution"]."')";
											$deleteFromSharesPreQ .= '`id` ='.$shares["id"];
                                              
                                                	}
										
                                                  //Commence the $insertQuery
                                                       $moveSharesToHistory = mysql_query($insertQuery)or die(mysql_error());
                                                       echo $deleteFromSharesPreQ;
                                                  //Delete shares from round
                                          	        $deleteSharesFromRound = mysql_query($deleteFromSharesPreQ)or die(mysql_error());
                                                  
                                                  	 
                                                  //Tell the rest of the page that the account does exist
                                                  	$accountExists = 1;
                                   }
                         }
                        
                         
                         
                         
                         
                         //If the bock does exist then how about we update the information on it, and if it has 120 confirms or more then we can award the rewards to the users
                         	if($accountExists >= 1){
                         		
                         		//Update information
                         			mysql_query("UPDATE `networkBlocks` SET `confirms` = '".$transactions[$i]["confirmations"]."', `amount` = '".$transactions[$i]["amount"]."' WHERE `txid` = '".$shares["txid"]."'")or die(mysql_error());
                         	
                         		//Have we hit a confirmed 120 confirms?
                         			if($transactions[$i]["confirmations"] >= 120 && $transactions[$i]["category"] == "generate"){
                         				//This is a valid block that has been found check and see if we have already 
                         					$alreadyRewardedQ = mysql_query("SELECT `id` FROM `networkBlocks` WHERE `txid` = '".$transactions[$i]["txid"]."' AND `rewardsCollected` = '1' LIMIT 0,1")or die(mysql_error());
                         					$alreadyRewarded = mysql_num_rows($alreadyRewardedQ);
                         					
                         					if($alreadyRewarded == 0){
                         									
                         						//We must reward those who have earned it ;)
                         								//Get blockNumber
                         									$blockNumber = mysql_fetch_object($accountExistsQ);
                         									$blockNumber = $blockNumber->blockNumber;
                         								//Get list of users that worked for this block
                         									$userList = mysql_query("SELECT DISTINCT `username` FROM `shares_history` WHERE `blockNumber` = '".$blockNumber."'")or die(mysql_error());
                         									$activateCollectedFlag = mysql_num_rows($userList);
                         									
                         									if($activateCollectedFlag > 0){
                         										//Update the block information saying that we have collected the rewards
																mysql_query("UPDATE `networkBlocks` SET `rewardsCollected` = '1' WHERE `blockNumber` = '".$blockNumber."' LIMIT 1")or die(mysql_error());
                         									}
                         									while($user = mysql_fetch_array($userList)){
                         									echo "rewarding....<br/>";
                         								
                         										$numTotalUserShares = mysql_query("SELECT COUNT(*) FROM `shares_history` WHERE `username` = '".$user["username"]."' AND `blockNumber` = '".$blockNumber."' AND `our_result` = 'Y'")or die(mysql_error());
                         										$numTotalUserShares = mysql_fetch_array($numTotalUserShares);
                         										
                         										$numTotalPoolShares = mysql_query("SELECT COUNT(*) FROM `shares_history` WHERE `blockNumber` = '".$blockNumber."' AND `our_result` = 'Y'")or die(mysql_error());
                         										$numTotalPoolShares = mysql_fetch_array($numTotalPoolShares);
                         										//E for Effort
																$E = $numTotalUserShares[0]/$numTotalPoolShares[0];
															
															//P for Pretotal
																$P = $E*50;
														
															//A for get the admin fee
																$A = $P-($P*($serverFee*0.1));
														
															//total reward
																$totalReward = $A;
																
															//Increment Server rewards
																$serverRewards += ($P-$A);
														
															//update the owner of this worker that subbmited work to there balance 
																//Get username id
																	$explodeUsername = explode(".", $user["username"]);
																	
																	$userIdQ = mysql_query("SELECT `id` FROM `websiteUsers` WHERE `username` = '".$explodeUsername[0]."' LIMIT 0,1")or die(mysql_error());
																	$userId = mysql_fetch_object($userIdQ);
																	
																	mysql_query("UPDATE `accountBalance` SET `balance` = `balance`+$totalReward WHERE `userId` = '".$userId->id."'")or die(mysql_error());
																	
															//Set all counted shares
																mysql_query("UPDATE `shares_history` SET `shareCounted` = 1 WHERE `username` = '".$user["username"]."' AND `blockNumber` = '".$blockNumber."' AND `our_result` = 'Y'")or die(mysql_error());
																
											
														}	
										
                         					}
                         			}
                         			
                         			
                         			
                         		//Update orphan flag if neccesary
                         			if($transactions[$i]["category"] == "orphan"){
                         			
                         			}
                         	}
           }
          

     }
     
     //Update server account balance (if neccessary)
     	if($serverRewards > 0){
     		//Increent rewards
     			mysql_query("UPDATE `websiteSettings` SET `serverAccountBalance` = `serverAccountBalance`+".$serverRewards." LIMIT 1")or die(mysql_error());
     	}
     	
     //Reset current round stats
     	if($resetRoundStats > 0){
     		//Reset
     			mysql_query("DELETE FROM `stats_topHashers`")or die(mysql_error());
     			mysql_query("DELETE FROM `stats_topSharers`") or die(mysql_error());
     	}
	
	
	
	
	
	/*
			

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
		
$lengthOfScript = gettimeofday(2);
$lengthOfScript -= $startTime;

echo '('.($lengthOfScript).")";

?>
