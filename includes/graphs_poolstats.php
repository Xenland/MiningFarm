<?php
															//Get credentials
																$getCredientials->getStats();
															
																$getCredientials->getAdminSettings();	
																
															//generate graph information
																//Get this individuals mhash
																	$fifteenMinutesAgo = time();
																	$fifteenMinutesAgo -= 60*15;
																	$userHashHistoryQ = mysql_query("SELECT DISTINCT `timestamp` FROM `stats_userMHashHistory` WHERE `username` LIKE '".$getCredientials->username.".%' AND `timestamp` >= '$fifteenMinutesAgo' AND `mhashes` > 0 ORDER BY `timestamp` ASC");
																	$numRows = mysql_num_rows($userHashHistoryQ);
																	
																	//Go through every time stamp and average out all the workers per timestamp
																		$userHashArray = "";
																		$timeHashArray = "";
																		$poolHashArray = "";
																		$poolTotalHashArray = "";
																	
																	//Show this graph if logged in
																	if($numRows > 0){
																		$i=0;
																		while($time = mysql_fetch_array($userHashHistoryQ)){
																			
																			$tmpHashAverage = 0;
																			$tmpTotalHash = 0;
																			//Get all mhash results with this timestamp and average them up
																				$getAllWorkerHash = mysql_query("SELECT `mhashes` FROM `stats_userMHashHistory` WHERE `username` LIKE '".$getCredientials->username.".%' AND `timestamp` = '".$time["timestamp"]."' AND `mhashes` > 0");
																				$numWorkersThisTime = mysql_num_rows($getAllWorkerHash);
																				while($workerHash = mysql_fetch_array($getAllWorkerHash)){
																					$tmpHashAverage += $workerHash["mhashes"];
																					$tmpTotalHash += $workerHash["mhashes"];
																				}
																				$tmpHashAverage = $tmpHashAverage/$numWorkersThisTime;
																			//Get pool average results
																				$getPoolAverageResult = mysql_query("SELECT `averageMhash`, `totalMhash` FROM `stats_poolMHashHistory` WHERE `timestamp` = '".$time["timestamp"]."' LIMIT 0,1");
																		
																					$poolAverageQ = mysql_fetch_object($getPoolAverageResult);
																					$poolAverage = $poolAverageQ->averageMhash;
																					$tmpTotalHash = $poolAverageQ->totalMhash;
																					//Pool average comes up null sometimes this will prevent a break in the graph
																						if(!isSet($poolAverage)){
																							$poolAverage = 0;
																						}
																				
																			//Add points to graph
																				if($i > 0){
																					$userHashArray .= ",";
																					$timeHashArray .= ",";
																					$poolHashArray .= ",";
																					$poolTotalHashArray .= ",";
																				}
																				$i++;
																				$timeHashArray .= "'".date("G:i", $time["timestamp"])."'";
																				$userHashArray .= round($tmpHashAverage);
																				$poolHashArray .= round($poolAverage);
																				$poolTotalHashArray .= round($tmpTotalHash);
																		}
																		
																	}else if($numRows == 0){
																		//Show this graph when not logged in
																		$i=0;
																		//Go through the pool history and display that
																		$poolHistory = mysql_query("SELECT `averageMhash`, `totalMhash`, `timestamp` FROM `stats_poolMHashHistory` WHERE `timestamp` >= '".$fifteenMinutesAgo."' ORDER BY `timestamp` ASC");
																			while($poolHash = mysql_fetch_array($poolHistory)){
																				if($i > 0){
																					$poolHashArray .=",";
																					$timeHashArray .=",";
																					$poolTotalHashArray .=",";
																				}
																				$i++;
																				$poolHashArray .= round($poolHash["averageMhash"]);
																				$timeHashArray .= "'".date("g:i", $poolHash["timestamp"])."'";
																				$poolTotalHashArray .= round($poolHash["totalMhash"]);
																			}
																	}
																	
																//If theres no data to be displayed even after the above display filler data
																	if($poolHashArray == "" && $timeHashArray  == "" && $poolTotalHashArray  == ""){
																		$timeHashArray = "'".date("G:i", time())."'";
																		$poolHashArray = "0";
																		$poolTotalHashArray = "0";
																	}
																
																if($getCredientials->statsShowAllUsers == 1){
																	//
																}
														?>
														<script type="text/javascript">
															var chart1; // globally available
															$(document).ready(function() {
																chart1 = new Highcharts.Chart({
																	chart: {
																		renderTo: 'graph',
																		defaultSeriesType: 'spline',
																		width:750,
																		height:250
																	},
																	title: {
																		text: 'Overall Network Status - <?php echo date("e", time());?> timezone'
																	},
																	xAxis: {
																		categories: [<?php echo $timeHashArray;?>]
																	},
																	yAxis: {
																		title: {
																			text: 'Mega-Hashes'
																		}
																	},
																	series: [{
																		name: 'Pool Average',
																		data: [<?php echo $poolHashArray; ?>]
																		},
																		{
																		name: 'Pool Total',
																		data: [<?php echo $poolTotalHashArray;?>]
																		}
																		<?php
																			if($userHashArray != ""){
																		?>, 
																		{
																		name: 'Your Average',
																		data: [<?php echo $userHashArray?>]
																		}
																		<?php
																			}
																		?>]
																});
															});
														</script>
														<div id="graph" align="center">
														</div><br/><br/>