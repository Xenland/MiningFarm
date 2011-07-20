<?php
// Load Linkage Variables //
$dir = dirname(__FILE__);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Include hashing functions
include($functions);

//Include bitcoind functions
include($bitcoind);

$pageTitle = outputPageTitle()." - ";
$pageTitle .= gettext("Main Page");

//Set user details for userInfo box
	$rawCookie		= "";
	$rawCookie		= $_COOKIE[$cookieName];
	$getCredientials	= new getCredientials;
	$loginValid		= $getCredientials->checkLogin($rawCookie);
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
														<div class="socialBar">
															<div>
															<!-- AddThis Button BEGIN -->
															<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
															<a class="addthis_button_preferred_1"></a>
															<a class="addthis_button_preferred_2"></a>
															<a class="addthis_button_preferred_3"></a>
															<a class="addthis_button_preferred_4"></a>
															<a class="addthis_button_compact"></a>
															<a class="addthis_counter addthis_bubble_style"></a>
															</div>
															<script type="text/javascript">var addthis_config = {"data_track_clickback":true,"data_track_addressbar":true};</script>
															<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e22713a770589c9"></script>
															<!-- AddThis Button END -->
															</div>
														</div>
														<hr size="1" width="100%"/>
														<div class="serverStatus">
														<?php
														//Get total shares
															$totalSharesQ = mysql_query("SELECT `id` FROM `shares` WHERE `our_result` = 'Y'");
															$totalShares = mysql_num_rows($totalSharesQ);
															
														//GET SERVER LOADS
															$loadresult = @exec('uptime');
															preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$loadresult,$avgs);

															//GET SERVER UPTIME
															$uptime = explode(' up ', $loadresult);
															$uptime = explode(',', $uptime[1]);
															$uptime = $uptime[0].', '.$uptime[1];
															
															//Set variables for serverload average and Updtime
																$serverLoadAverage = round((($avgs[1]+$avgs[2]+$avgs[3])/3), 2)*100;
																$uptimeData = $uptime;
																
																if($serverLoadAverage < 100){
																	$type = "highServerLoad";
																	$loadText = "High";
																}
																if($serverLoadAverage < 50){
																	$type = "medServerLoad";
																	$loadText = "Medium";
																}
																if($serverLoadAverage < 20){
																	$type = "lowServerLoad";
																	$loadText = "Low";
																}
															echo "IP:".$ip." Port:".$port."<br/>";
															echo "Shares this round:".$totalShares."<br/>";
															echo "Server Load: <span class='".$type."'>".$loadText."(".$serverLoadAverage."%)</span><br/>";
															echo "Server Uptime: ".$uptimeData;
														?>
														</div>
													</div>
													<div class="art-postcontent">
															<!-- Show the last blog post reported this way the graphs don't get in the way of the blogs -->
															<?php
																//Get last blog post
																	$lastBlogPost = mysql_query("SELECT `title`, `timestamp`, `message` FROM `blogPosts` ORDER BY `timestamp` DESC LIMIT 0,1");
																	$lastBlogPost = mysql_fetch_object($lastBlogPost);
																	
															?>
																	
																<div class="blogContainer">
																	<div class="blogHeader">
																		<h1 class="blogHeader">
																			<?php echo $lastBlogPost->title;?>
																		</h1>	
																	</div>
																	<div class="blogContent">
																		<span class="blogTimeReported">
																			<?php echo date("m/d/Y G:s", $lastBlogPost->timestamp); ?>
																		</span><br/>
																		<?php echo $lastBlogPost->message; ?>
																	</div>
																		
																</div><br/>
																	
															<?php
																//Include graphs
																
																	include($dir."/includes/graphs_poolstats.php");
																	
																	
																//Include stats users graph if nessecary
																	//Display the all users graph?
																		if(!empty($getCredientials->statsShowAllUsers)){
																		
																			if($getCredientials->statsShowAllUsers == 1){
																				include($dir."/includes/graphs_bitcoinHistory.php");
																			}
																		}
															?>
														<br/>
														
															<!-- quick bloging -->
																<!-- start blogging -->
<?php
																	//retireve blogs
																	$blogs = mysql_query("SELECT `title`, `timestamp`, `message` FROM `blogPosts` ORDER BY `timestamp` DESC LIMIT 0,5");
																	
																	$i=0;
																	while($blog = mysql_fetch_array($blogs)){
																		//Don't include the first blog, that one is reserved for the top
																			if($i > 0){
?>
																	<div class="blogContainer">
																		<div class="blogHeader">
																			<h1 class="blogHeader">
																				<?php echo $blog["title"];?>
																			</h1>	
																		</div>
																		<div class="blogContent">
																			<span class="blogTimeReported">
																				<?php echo date("m/d/Y G:s", $blog["timestamp"]); ?>
																			</span><br/>
																			<?php echo $blog["message"]; ?>
																		</div>
																			
																	</div>
<?php
																			}
																			$i++;
																	}
?>
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