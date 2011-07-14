<?php
//Preset variables
	$act = 0;
	$returnError = "";
	$goodMessage = "";
	$usernameWorker = "";
	$passwordWorker = "";

// Load Linkage Variables //
	$dir = dirname(__FILE__);
	$req 		= $dir."/req/";
	$functions	= $req."functions.php";

//Load Functions
	include($functions);

//Include bitcoind functions
	include($bitcoind);

//Perform login
	$getCredientials	= new getCredientials;
	$loginValid		= $getCredientials->checkLogin($_COOKIE[$cookieName]);
if($loginValid){

	//Connect to db
		connectToDb();
	
	//Get user information
		$getCredientials->getStats();
		
	//Figure out which action the user is trying to do
		if(!empty($_POST["act"])){
			$act = $_POST["act"];
		
				if($act == "Add Worker"){
						
		
					//Mysql Injection Protection
						if(!empty($_POST["username"]) && !empty($_POST["password"])){
							$usernameWorker = mysql_real_escape_string($_POST["username"]);
							$passwordWorker = mysql_real_escape_string($_POST["password"]);
						
							//add workers
								$insertQ = mysql_query("INSERT INTO `pool_worker` (`associatedUserId`, `username`, `password`)
												VALUES('".$getCredientials->userId."', '".$getCredientials->username.".".$usernameWorker."', '".$passwordWorker."')");
								
								if($insertQ == 0){
										$returnError = gettext("You already have a worker named that");
								}else if($insertQ != false){
										$goodMessage = gettext("Worker")." ".$usernameWorker." ".gettext("was successfully added to your workers list, you may now connect to our server with that worker name");
								}
						}else{
							$returnError = gettext("You must supply a username and password");
						}
				}
		
				if($act == "Update"){
					
					
						if(!empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["workerId"])){
							//Mysql Injection Protection
								$usernameWorker = mysql_real_escape_string($_POST["username"]);
								$passwordWorker = mysql_real_escape_string($_POST["password"]);
								$workerId	= mysql_real_escape_string($_POST["workerId"]);
				
								$usernameWorker = $getCredientials->username.".".$usernameWorker;
				
							//update worker
									$updateSuccess = mysql_query("UPDATE `pool_worker` SET `username` = '".$usernameWorker."', `password` = '".$passwordWorker."' WHERE `id` = '".$workerId."' AND `associatedUserId` = '".$getCredientials->userId."'")or die(mysql_error());
									
							//Out put message accordingly
								if($updateSuccess != false){
									$goodMessage = "Worker details, updated successfully on <br/>".date("g:i:s A e", time())." time";
								}else if($updateSuccess == false){
									$returnError = "Worker details, was not updated successfully on <br/>".date("g:i:s A e", time())." time";
								}
						}else{
							//Username, password or workerId wasn't supplied
								$returnError = "You have forgotten your";
								if(!empty($_POST["username"])){
									$returnError .= ", username";
								}
								
								if(!empty($_POST["password"])){
									$returnError .= ", password";
								}
								
								if(!empty($_POST["workerId"])){
									$returnError .= ", workerid";
								}
						}
				}
		
				if($act == "Delete"){
		
						if($_POST["workerId"] > 0){
							//Mysql Injection Protection
								$workerId = mysql_real_escape_string($_POST["workerId"]);
				
							//Delete worker OH NOES!
								mysql_query("DELETE FROM `pool_worker` WHERE `id` = '".$workerId."' AND `associatedUserId` = '".$getCredientials->userId."'");
								
							//Output message accordingly
								$goodMessage = "Worker deleted from pool, In case you were wondering your shares subbmitted for this worker will still be counted towards your rewards.";
						}
				}
		}
?>
<?php
//Include the header & slogan
include($header);
////////////////////////////

?>
					<div class="art-sheet">
						<div class="art-sheet-tl"></div>
						<div class="art-sheet-tr"></div>
						<div class="art-sheet-bl"></div>
						<div class="art-sheet-br"></div>
						<div class="art-sheet-tc"></div>
						<div class="art-sheet-bc"></div>
						<div class="art-sheet-cl"></div>
						<div class="art-sheet-cr"></div>
						<div class="art-sheet-cc"></div>
						<div class="art-sheet-body">
							<div class="art-content-layout">
								<div class="art-content-layout-row">
									<div class="art-layout-cell art-content">
										<div class="art-post">
											<div class="art-post-body">
												<div class="art-post-inner art-article">
													<div class="art-postcontent">
														<h3 class="loginMessages">
															<span class="returnError"><?php echo $returnError;?></span>
															<span class="goodMessage"><?php echo $goodMessage;?></span>
														</h3>
														<table align="center" cellpadding="0" cellspacing="0" class="bigContent">
															<tbody>
																<tr>
																	<td class="contTC">
																		<?php echo gettext("Add a worker");?>
																	</td>
																</tr>
																<tr>
																	<td colspan="3" class="contContent">
																		<span class="workersMessages"><?php echo $returnError;?></span>
																		<form action="workers.php" method="post">
																		<input type="text" name="username" value="username"> &middot; <input type="text" name="password" value="password"><input type="submit" name="act" value="Add Worker"><br/>
																		</form><br/>
																	</td>
																</td>
															</tbody>
														</table><br/><br/>
														<table align="center" cellpadding="0" cellspacing="0" class="bigContent">
															<tbody>
																<tr>
																	<td class="contTC">
																		<?php echo gettext("Manage Workers");?>
																	</td>
																</tr>
																<tr>
																	<td class="contContent">
																		<p>You can <i>create</i>, <i>delete</i>, and <i>update</i> each worker that you have specified below, In order to connect to our server with your Mining Application such as GUI Miner, or Phoniex Miner, you can point it towards your log in name(<?php echo $getCredientials->username;?>) then putting a peroid(.) and then the worker name you specified below along with the password. For instance if you wanted to connect to your worker named <b><i>username</i></b> with the password set to <i><b>password</b></i> you would connect with your miner with the following details:<br/>
																			<b>Username: <?php echo $getCredientials->username;?>.username<br/>
																				Password: password
																			</b>
																		</p>
																		<hr size="1" width="100%"/>
																		<?php
																		//Get and show list of workers along with a <form> to add more workers
																			//Get time 5 minutes ago
																				$timeFiveMinutesAgo = time();
																				$timeFiveMinutesAgo -= 60*5;
																				
																			$listWorkersQ = mysql_query("SELECT `id`, `username`, `password` FROM `pool_worker` WHERE `associatedUserId` = '".$getCredientials->userId."' ORDER BY `id` DESC")or die(mysql_error());
																			while($worker = mysql_fetch_array($listWorkersQ)){
																				//Get this workers recent average Mhashes (If any recently)
																					$getMhashes = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `username` = '".$worker["username"]."' AND `timestamp` >= '$timeFiveMinutesAgo' ORDER BY `timestamp` DESC");
																					$numHashes = mysql_num_rows($getMhashes);
																					$totalMhashes = 0;
																					while($mhashes = mysql_fetch_array($getMhashes)){
																						$totalMhashes += $mhashes["mhashes"];
																					}
																					
																					//Prevent division by zero
																						if($totalMhashes > 0 && $totalMhashes > 0){
																							$averageHashes = $totalMhashes/$numHashes;
																						}else if($totalMhashes == 0 || $totalMhashes == 0){
																							$averageHashes = "<span class=\"notConnected\">".gettext("Not connected")."</span>";
																						}
																						
																						$averageHashes = round($averageHashes, 2);
																				
																					//Get this workers efficency (if working)
																						$eff = "N/A"; 
																						if($averageHashes > 0){
																							$totalShares = mysql_query("SELECT `id` FROM `shares` WHERE `username` = '".$worker["username"]."'");
																							$totalShares = mysql_num_rows($totalShares);
																							
																							$totalValidShares = mysql_query("SELECT `id` FROM `shares` WHERE `username` = '".$worker["username"]."' AND `our_result` = 'Y'");
																							$totalValidShares = mysql_num_rows($totalValidShares);
																							$eff = 100;
																							if($totalShares > 0 && $totalValidShares > 0){
																								$eff = round(($totalValidShares/$totalShares)*100, 2);
																							}
																						}
																				//Split username for user input
																					$splitUser = explode(".", $worker["username"]);
																			?>
																			<form action="workers.php" method="post">
																				<input type="hidden" name="workerId" value="<?=$worker["id"]?>">
																				<span class="workerName"><?php echo $splitUser[0]; ?></span>.<input type="text" name="username" value="<?php echo $splitUser[1]; ?>" size="10"> <input type="text" name="password" value="<?php echo $worker["password"];?>" size="10"><input type="submit" name="act" value="<?php echo gettext("Update");?>"><input type="submit" name="act" value="<?php echo gettext("Delete");?>"/><br/>
																				<span class="workerMhash"><?php echo $averageHashes; ?> MHash/s</span> &middot; <span class="efficiency"><?php echo $eff;?>% efficient</span>
																				</form><br/><Br/>
																			<hr size="1" width="100%"/>
																			<?php
																			}

																			?>
																	</td>
																</td>
															</tbody>
														</table><br/><br/>
													</div>
													<div class="cleared"></div>
												</div>
												<div class="cleared"></div>
											</div>
										</div>
										<div class="cleared"></div>
									</div>
								</div>
							</div>

<?php
//Include Footer
////////////////////
include($footer);

}else{
	header("Location: /");
}
?>