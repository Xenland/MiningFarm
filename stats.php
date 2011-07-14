<?php
// Load Linkage Variables //
$dir = dirname(__FILE__);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Include hashing functions
include($functions);

//Include bitcoind functions
include($bitcoind);


//Open a bitcoind connection
	$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);


//Set user details for userInfo box
$rawCookie		= "";
if(isSet($_COOKIE[$cookieName])){
	$rawCookie	= $_COOKIE[$cookieName];
}
$getCredientials	= new getCredientials;
$loginValid	= $getCredientials->checkLogin($rawCookie);
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
														<div class="blogContainer">
																<div class="blogHeader">
																	<h1 class="blogHeader">
																		Block's Awaiting Confirmation
																	</h1>	
																</div>
																<div class="blogContent">
																		
																	<table width="100%" cellpadding="0" cellspacing="0">
																		<tdbody>
																			<tr>
																				<td>
																					<b>Block #</b>
																				</td>
																				<td>
																					<b>Total Confirmations</b>
																				</td>
																				<td>
																					<b>Time found</b>
																				</td>
																				<td>
																					<b>Total Amount of your reward</b>
																				</td>
																			</tr>
																				<?php 
																					$getCredientials->getStats();
																					$currentAdminFee = getAdminFee();
																					//Retireve all blocks awaiting confirmation
																						$getBlocksQ = mysql_query("SELECT `blockNumber`, `timestamp`, `confirms`, `orphan` FROM `networkBlocks` WHERE `txid` != '' ORDER BY `timestamp` DESC LIMIT 0,120");
																						$numBlocksFound = mysql_num_rows($getBlocksQ);
																						
																						//If their are blocks found, display the Blocks found/ETA next block graph
																							if($numBlocksFound > 0){
																								//List blocks information
																									while($block = mysql_fetch_array($getBlocksQ)){
																										if($loginValid){
																											//Calculate the amount of the reward this user will get this block/round if logged in
																												//Get pool round total
																													$roundTotalQ  = mysql_query("SELECT `id` FROM `shares_history` WHERE `blockNumber` = ".$block["blockNumber"]." AND `our_result` = 'Y'")or die(mysql_error());
																													$roundTotal = mysql_num_rows($roundTotalQ);
																													
																												//Get user round total
																													$roundUserTotalQ  = mysql_query("SELECT `id` FROM `shares_history` WHERE `blockNumber` = ".$block["blockNumber"]." AND `our_result` = 'Y' AND `username` LIKE '".$getCredientials->username.".%'")or die(mysql_error());
																													$roundUserTotal = mysql_num_rows($roundUserTotalQ);
																													
																												//Get percentage of reward
																													$reward = 0;
																													if($roundUserTotal > 0 && $roundTotal > 0){
																														$reward = $roundUserTotal/$roundTotal;
																													}else{
																														
																													}
																													
																												//Subtract Admin percentage fee
																													$reward = round($reward*(50-(50*($currentAdminFee*0.01))), 8);
																												
																										}else{
																											//Tell the user they are not logged in and must be logged into to view potential reward
																												$reward = "Login to see your reward this round";
																										}
																									
																				?>
																				<!--Start block information-->
																			<tr<?php if($block["orphan"] == 1){ echo ' class="statsOrphanRow"';}else{ echo ' class="statsGoodRow"';}?>>
																				<td>
																					<?php echo $block["blockNumber"];?>
																				</td>
																				<td>
																					<?php echo $block["confirms"];?>
																				</td>
																				<td>
																					<?php echo date("m/d g:i a", $block["timestamp"]);?>
																				</td>
																				<td>
																					<?php 
																					if($block["orphan"] == 1){
																						echo "Invalid/Orphan (".$reward.")";
																					}else if($block["orphan"] == 0){
																						echo $reward;
																					}
																					?>
																				</td>
																			</tr>
																				<!--End Block Information-->
																				<?php
																						
																									}
																							}
																						
																						if($numBlocksFound == 0){
																				?>
																			<tr>
																				<td>
																					N/A
																				</td>
																				<td>
																					Zero
																				</td>
																				<td>
																					0.00
																				</td>
																				<td>
																					Start Your Miners!
																				</td>
																			</tr>
																				<?php
																						}
																				?>
																		</tdbody>
																	</table>
																</div>
																	
															</div>
															
															<br/><br/>
													<!--Blocks confirmeation table END-->
													
															<div class="blogContainer">
																<div class="blogHeader">
																	<h1 class="blogHeader">
																		Graph of Blocks found
																	</h1>	
																</div>
																<div class="blogContent">
																	<div>
																	<?php
																		//Open a bitcoind connection

																					//Get current block number
																						try{
																							$currentBlockNumber = $bitcoinController->getblocknumber();
																						}catch(Exception $e){
																							$currentBlockNumber = -1;
																						}
																					//Get blocks found
																						$blocksFoundQ = mysql_query("SELECT `blockNumber`, `timestamp` FROM `networkBlocks` WHERE `txid` != '' ORDER BY `blockNumber` ASC");
																						$numBlocksFound = mysql_num_rows($blocksFoundQ);
																						
																						//If any blocks have been found
																							if($numBlocksFound > 0){
																								//Display blocks that have been found with the corrisponding timestamp
																									$blockArray = "";
																									$timeArray = "";
																									$i=0;
																									
																									//Tmp vars
																									$totalDifference = 0;
																									$lastDifference = 0;
																									$lastBlockNumber = 0;
																									while($block = mysql_fetch_array($blocksFoundQ)){
																										//Loop through every block found and add it to the array of data to output on the stats graph
																											if($i > 0){
																												$blockArray .=",";
																												$timeArray .=",";
																											}
																											$i++;
																											$blockArray .="{y: ".$block["blockNumber"].",
																														marker: {
																															symbol: 'url(/images/graphIcons/blockFound.png)'
																														}
																													}";
																											$timeArray .= "'".date("m/d/Y g:i:s a", $block["timestamp"])."'";
																											
																											if(!$lastDifference){
																												$lastDifference = $block["timestamp"];
																											}
																											
																											if($lastDifference){
																												$totalDifference += ($block["timestamp"]-$lastDifference);
																												$lastDifference = $block["timestamp"];
																											}
																											
																											$lastBlockNumber = $block["blockNumber"];
																									}
																									
																									//Prevent division by zero && calculate eta till next block
																										if($i > 0 && $totalDifference > 0){
																											$averageDifference = ($totalDifference/$i);
																											$estimatedFindTimestamp = $lastDifference+$averageDifference;
																											//Get esimated block number by
																												//averaging out the amount of time between blocks found, and then adding the estimated time till that block will be found
																											$estimatedBlockNumber = round($lastBlockNumber+(($averageDifference/60)/10));
															
																										}
																									
																									//Add current time here if its before estimated block finding
																										if($estimatedFindTimestamp > time()){
																											//Add currentime time
																												$timeArray .=",'".date("m/d/y g:i:s a", time())."'";
																												$blockArray .=",{
																															y: ".$currentBlockNumber.",
																															marker: {
																															symbol: 'url(/images/graphIcons/currentTime.png)'
																															}
																														}";
																										}
																									
																									
																									//Add expected block found ( only if there is an expected block calculation )
																										if($estimatedBlockNumber > 0){
																										$timeArray .= ",'".date("m/d/Y g:i:s a", $estimatedFindTimestamp)."'";
																										$blockArray .=",{
																													y: ".$estimatedBlockNumber.",
																													marker: {
																													symbol: 'url(/images/graphIcons/estimatedBlockFound.png)'
																													}
																												}";
																										}
																												
																									//Add current time here if it is after the estimated block finding
																										if($estimatedFindTimestamp < time() ){
																											//Add currentime time
																												$timeArray .=",'".date("m/d/y g:i:s a", time())."'";
																												$blockArray .=",{
																															y: ".$currentBlockNumber.",
																															marker: {
																															symbol: 'url(/images/graphIcons/currentTime.png)'
																															}
																														}";
																										}
																							}else{
																								//No blocks have been found yet, show this in the graph
																									$blocksFoundTitle = '(No Blocks have been found yet)';
																									
																									$timeArray = "'".date("m/d g:i", time())."'";
																									$blockArray = "{
																												y: ".$currentBlockNumber.",
																												marker: {
																													symbol: 'url(/images/graphIcons/currentTime.png)'
																												}
																											}";
																							
																							}
																				
																			
											
																	?>
																	<script type="text/javascript">
																		var chart1; // globally available
																		$(document).ready(function() {
																			chart1 = new Highcharts.Chart({
																				chart: {
																					renderTo: 'blocksFound',
																					defaultSeriesType: 'spline',
																					width:750,
																					height:250
																				},
																				title: {
																					text: '<?php if(!isSet($blocksFoundTitle)){
																								echo 'Overall Blocks Found';
																							}else if(isSet($blocksFoundTitle)){
																								echo $blocksFoundTitle;
																							}
																						?>'
																				},
																				xAxis: {
																					categories: [<?php echo $timeArray;?>]
																				},
																				yAxis: {
																					title: {
																						text: 'Block Number'
																					}
																				},
																				series: [{
																					name: 'Longest Block Chain',
																					data: [
																						<?php echo $blockArray; ?>
																							
																						]
																					}]
																			});
																		});
																	</script>
																	<div id="blocksFound" align="center">
																		<h3>No blocks found yet</h3>
																	</div><br/><br/>
																</div>
									
															</div>
																<img src="/images/graphIcons/blockFound.png"/> = Block Found<br/>
														
																<img src="/images/graphIcons/currentTime.png"/> = Current network block<br/>
															
																<img src="/images/graphIcons/estimatedBlockFound.png"/> = ETA till next block is found
															<br/><br/>
															
																
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
?>

