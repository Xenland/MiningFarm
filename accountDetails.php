<?php
//Predefine variables to kill error_notices 
$goodMessage = "";
$returnError = "";

// Load Linkage Variables //
	$dir = dirname(__FILE__);
	$req 		= $dir."/req/";
	$functions	= $req."functions.php";

//Include hashing functions
	include($functions);

//Include bitcoind functions
	include($bitcoind);

//Set user details for userInfo box
	$rawCookie		= "";
	if(!empty($_COOKIE[$cookieName])){
		$rawCookie	= $_COOKIE[$cookieName];
	}
	$getCredientials	= new getCredientials;
	$loginValid		= $getCredientials->checkLogin($rawCookie);

if($loginValid){
	$getCredientials->getStats();
	
	//Check which action this user is trying to commence
		if(!empty($_POST["act"])){
			$act = $_POST["act"];
		}else if(empty($_POST["act"])){
			$act = '';
		}

		if($act != ""){
			//Open a bitcoind connection
				$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

			//Check to see if there authorisation pin matches the one in database
				$hashedInputPin = hash("sha256", $_POST["authPin"]);
				$hashedDbPin = $getCredientials->hashedAuthPin;
				if($hashedDbPin == $hashedInputPin){
					if($act == "editIdentity"){
						//Update identity information
							$payoutAddress	= mysql_real_escape_string($_POST["payoutAddress"]);
							$threshHold	= mysql_real_escape_string($_POST["payoutThreashHold"]);
								
								$updateSuccess = mysql_query("UPDATE `accountBalance` SET `payoutAddress` = '".$payoutAddress."', `threshhold` = '".$threshHold."' WHERE `userId` = '".$getCredientials->userId."'")or die(mysql_error());
								
								if($updateSuccess){
									$goodMessage = gettext("Information was successfully updated!");
								}else if(!$updateSuccess){
									$returnError = gettext("Database Error | Contact the admin");
								}
					}

					if($act == "manualCashout"){
						//Manually cashing out
					
							//Does this accountbalance meet the `cashoutMinimum`
								$accountBalance = $getCredientials->accountBalance;
								$cashOutAddress = $getCredientials->sendAddress;
								$userId		= $getCredientials->userId;
								$cashOutMin	= getCashoutMin();
								
								if($accountBalance >= $cashOutMin){
									//Subtract $accountBalance by 0.01 for the hardwired transaction fee
										$accountBalance -= 0.01;


									//Check if there was a donation address to send to
										if(!empty($_POST["donate1"])){
											//Subtract another .01
												$accountBalance -= 0.01;
												
											//MySql injection protection
												$donationAmount = mysql_real_escape_string($_POST["donate1"]);
												$donationId	= mysql_real_escape_string($_POST["donation1"]);
											
											//Get donation address
												$donationAddressQ = mysql_query("SELECT `bitcoinAddress` FROM `donationList` WHERE `id` = '".$donationId."' LIMIT 0,1")or die(mysql_error());
												$donationAddressObj = mysql_fetch_object($donationAddressQ);
												$doantionAddress = $donationAddressObj->bitcoinAddress;
												
											//Send donation
												$sendDonate = $bitcoinController->sendtoaddress($donationAddress, $donationAmount);
												
											//Subtract amount
												$accountBalance -= $donationAmount;
										}
										
										//Send payment
										try{
											$successSend = $bitcoinController->sendtoaddress($cashOutAddress, $accountBalance);
											if($successSend){
												mysql_query("UPDATE `accountBalance` SET `balance` = '0' WHERE `userId` = '".$userId."'");
											}
										}catch(Exception $e){
											$returnError = gettext("No bitcoins were sent | Contact Pool operator");
										}

								

									//Reset account balance to zero
										if($successSend){
											$goodMessage = gettext("Successfully sent the amount of ").$accountBalance.gettext(" including the 0.01 transaction fee to the bitcoin address of ").$cashOutAddress;
										}else{
											$returnError = gettext("Bitcoind Query Error | Contact admin!");
										}

								}else if($accountBalance < $cashOutMin){
									//No enough funds
									$returnError =  gettext("The operator thinks it is best to have atleaset <b>".$cashOutMin."BTC</b> to cashout.");
								}
								
					}
				}else{
					$returnError = gettext("Authorisation Pin number you entered didn't match our records");
				}

				
		}
		
		$adminFee = getAdminFee();
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
																			Identity Details
																		</h1>	
																	</div>
																	<div class="blogContent">
																		<!--Identity Details Begin Content-->
																		<?php echo gettext("Username");?>: <?php echo $getCredientials->username;?><br/>
																		<?php echo gettext("Confirmed Email");?>: <?php echo $getCredientials->email;?><br/>
																		<hr size="1" width="100%"/>
																		<?php echo gettext("Confirmed Balance");?>: <?php setlocale(LC_MONETARY, 'en_US'); echo $getCredientials->accountBalance;?> BTC<br/>
																		<?php echo gettext("Unconfirmed Balance");?>: <?php echo $getCredientials->pendingBalance;?> BTC<br/>
																		<?php echo gettext("Estimated Reward this Round");?>: <?php echo $getCredientials->estimatedReward; ?> BTC
																		<!--Identity Details End Content-->
																		
																		<!--JSON Data-->
																		<h3 class="accountHeader"><?php echo gettext("JSON Mining &amp; Worker Data");?></h3>
																		<input type="text" name="nothing" value="<?php echo $getCredientials->apiToken;?>" size="40" onMouseOver="showTooltip('<?php echo gettext("API token to give you <i>private</i> access to your worker status");?>');" onMouseOut="hideTooltip();"/><br/>
																		<a class="accountLinks" href="/json/workerstatus.php?apiToken=<?php echo $getCredientials->apiToken;?>"><?php echo gettext("Worker Status");?></a><br/>
																	</div>
														</div>
														<br/><br/>
														<div class="blogContainer">
																<div class="blogHeader">
																	<h1 class="blogHeader">
																		Edit your payout
																	</h1>	
																</div>
																	<div class="blogContent">
																		<!--Identity Details Begin Content-->
																		<form action="accountDetails.php" method="post">
																		<input type="hidden" name="act" value="editIdentity">
																		<?php echo gettext("Payout Address");?>:<input type="text" size="32" name="payoutAddress" value="<?php echo $getCredientials->sendAddress;?>"><br/>
																		<?php echo gettext("Automatic Payout at");?>:<input type="text" size="5" name="payoutThreashHold" value="<?php if(!isSet($getCredientials->threashhold)){ echo "0.5";}else{ echo $getCredientials->threashhold;}?>"><b>BTC</b> (0 = <?php echo gettext("Disabled");?>)<br/>
																		<i><?php echo gettext("Authorisation Pin");?>:</i> <input type="password" name="authPin" value="" size="4" maxlength="4"><br/>
																		<hr size="1" width="100%">
																		<input type="submit" value="<?php echo gettext("Update Payout Address");?>">
																		</form>
																		<!--Identity Details End Content-->
																	</div>
																</div>
														<br/><br/>
														<div class="blogContainer">
																	<div class="blogHeader">
																		<h1 class="blogHeader">
																			Instant Payout
																		</h1>	
																	</div>
																	<div class="blogContent">
																		<!--Manual Payout Begin Content-->
																			<form action="accountDetails.php" method="post">
																				<input type="hidden" name="act" value="manualCashout">
																				<i>Authorisation Pin:</i> <input type="password" name="authPin" value="" size="4" maxlength="4"><br/>
																				You will be sending the amount of <b><?php echo $getCredientials->accountBalance;?>BTC</b> to the bitcoin address of <?php
																						if(isSet($getCredientials->sendAddress)){
																							echo "<b>".$getCredientials->sendAddress."</b>";
																						}else{
																							echo "<b>".gettext("None")."</b>";
																						}?><br/><br/>
																						
																						<?php 
																							//get donation list
																								$donationList = mysql_query("SELECT `display`, `id` FROM `donationList`");
																						?>
																						<b>I would also like to..</b><br/>
																						Donate:<input type="text" name="donate1" size="4" value="0" disabled="disabled"/>BTC to <select name="donation1">
																														<option>None</option>
																														<?php
																															//Output donation options
																																while($donation = mysql_fetch_array($donationList)){
																														?>
																														<option value="<?php echo $donation["id"];?>"><?php echo $donation["display"];?></option>
																														
																														<?php
																																}
																														?>
																													</select>
																						<br/><hr size="1" width="100%">
																				<input type="submit" value="<?php echo gettext("Execute Payout");?>">
																			</form>
																		<!--Manual Payout End Content-->
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
