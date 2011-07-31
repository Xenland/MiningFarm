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
														<div class="blogContainer">
															<div class="blogHeader">
																<h1 class="blogHeader">
																	<?php echo gettext("Add a worker"); ?>
																</h1>	
															</div>
															<div class="blogContent">
																<span class="blogTimeReported">
																	<form action="workers.php" method="post">
																		<input type="text" name="username" value="username"> &middot; <input type="text" name="password" value="password"><input type="submit" name="act" value="Add Worker"><br/>
																	</form><br/>
<!-- 																			 -->
																</span><br/>
															</div>
																
														</div>
														<div class="blogContainer">
															<div class="blogHeader">
																<h1 class="blogHeader">
																		<?php echo gettext("Manage Workers");?>
																</h1>	
															</div>
															<div class="blogContent">
																<style type="text/css">
																	.guiMiner{
																		width:27.65em;
																		height:17.6em;
																		background-image:url('/images/workersHelp/guiminer.png');
																		background-repeat:no-repeat;
																	}
																	.guiMinerForm{
																		position:relative;
																		top:5.5em;
																		left:1em;
																	}
																	.guiServerTitle{
																		position:relative;
																		top:0;
																		left:4em;
																	}
																	.guiMinerUser{
																		position:relative;
																		top:.7em;
																		left:4.4em;
																	}
																	.guiMinerPass{
																		position:relative;
																		top:.7em;
																		left:9.4em;
																	}
																	.guiMinerHash{
																		position:relative;
																		top:6em;
																		left:19em;
																	}
																	.guiMinerEff{
																		position:relative;
																		top:.5em;
																		left:1em;
																	}
																	.guiMinerShares{
																		position:relative;
																		top:3.7em;
																	}
																	.guiMinerUpdate{
																		position:relative;
																		top:6em;
																		left:5em;
																	}
																	.guiMinerDelete{
																		position:relative;
																		top:6em;
																		left:6.5em;
																	}
																</style>
																<script type="text/javascript">
																	//Just delete everything except the username it self
																	function updateWorker(fieldId){
																		//Split username
																			var workerName = document.getElementById('worker-'+fieldId).value;
																			
																			var workerArray =  workerName.split('.');
																			//Only remove the [0] index if "." was found
																			if(workerArray.length > 1){
																				//Remove the first index of workerArray and output JUST the worker name
																					var outputWorkername = '';
																					for(var i=0; i<workerArray.length; i++){
																						if(i > 0){
																							outputWorkername += workerArray[i];
																						}
																					}
																			}else{
																				//Scince there was no "." dot found just display workername
																					outputWorkername = workerArray[0];
																			}
																					
																		//Output new username
																			document.getElementById('worker-'+fieldId).value = outputWorkername;
																			
																	}
																</script>
																<p>
																This is your worker managment area here you can Update, and Delete your miners.
																Please select your mining application below and we'll generate what the login details should look like per application to help further assist you in mining.
																</p>
																Choose your Mining Application: <select id="minerApp">
																	<option value="guiMiner">GUI Miner</option>
																	<option value="Phoenix">Phoenix</option>
																</select><br/><br/>
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
																	<div class="guiMiner">
																		<div class="guiMinerForm">
																				<div class="guiServerTitle">
																					MiningFarm.com
																				</div>
																				<form action="workers.php" method="post">
																					<input type="hidden" name="workerId" value="<?=$worker["id"]?>">
																					<input type="text" name="username" value="<?php echo $splitUser[0].".".$splitUser[1]; ?>" size="18" class="guiMinerUser" id="worker-<?php echo $worker["id"];?>" onKeyUp="javascript:updateWorker(<?php echo $worker["id"];?>)"> <input type="text" name="password" size="11" class="guiMinerPass" value="<?php echo $worker["password"];?>" size="10">
																					<br/>
																					<input type="submit" name="act" value="<?php echo gettext("Update");?>" class="guiMinerUpdate"/><input type="submit" name="act" value="<?php echo gettext("Delete");?>" class="guiMinerDelete"/><br/>
																				
																				</form>
																				<div class="guiMinerHash">
																						<span>
																							<?php echo $averageHashes; ?> MHash/s
																						</span> 
																				</div>
																				<div class="guiMinerEff">
																						<span class="efficiency">
																							<?php echo $eff;?>% efficient
																						</span>
																				</div>
																				<div class="guiMinerShares">
																						<span>
																							Shares:<?php echo $totalValidShares;?> Accepted | <?php echo ($totalShares-$totalValidShares);?> Invalid
																						</span>
																				</div>
																		</div>
																	</div>
																	<hr size="1" width="100%"/>
																	<?php
																	}

																	?>
															</div>
																
														</div>
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