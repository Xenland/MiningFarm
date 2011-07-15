<?php
//Initiate Mysql connection
connectToDb();

//Specify variables to kill error_notices
	if(!empty($getCredientials->isAdmin)){
		$isAdmin = $getCredientials->isAdmin;
	}

	if(empty($pageTitle) && $pageTitle = ""){
		$pageTitle = "MiningFarm v5";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
		<title><?php echo $pageTitle;?></title>
		<link rel="stylesheet" href="/css/style.css" type="text/css" media="screen" />
		<!--[if IE 6]><link rel="stylesheet" href="/css/style.ie6.css" type="text/css" media="screen" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" href="/css/style.ie7.css" type="text/css" media="screen" /><![endif]-->
		<script type="text/javascript" src="/js/jquery.js"></script>
		<script type="text/javascript" src="/js/script.js"></script>
		
		<!--Javascript Area that will be included in all pages-->
		<!--Login Dissapear Script-->
		<script src="/js/login.js"></script>
		
<?php
	//Include refresh?
		if(!empty($refreshTime)){			
?>
		<meta http-equiv="refresh" content="<?php echo $refreshTime;?>;url=/">
<?php
		}
?>

		<!--High Charts Javascript area-->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript"></script>
		<script src="/js/highcharts/highcharts.js" type="text/javascript"></script>
		<script type="text/javascript" src="/js/highcharts/themes/greenlikemoney.js"></script>
	</head>
	<body>
		<div id="art-page-background-glare">
			<div id="art-page-background-glare-image">
				<div id="art-main">
					<div class="art-header">
						<div class="art-header-png"></div>
						<div class="art-header-jpeg"></div>
						<div class="art-header-wrapper">
							<div class="art-header-inner">
								<div class="art-logo">
									<div class="logoHeader">
									
									</div>
								</div>
								
								<!-- Begin Login Form Stuff -->
									<?php
									//If a valid login show balance if not logged in show login/register interface
									if(empty($loginValid)){
									?>
										<div class="login" align="right">
											<form method="POST" action="/login.php" name="loginForm">
												<input name="username" value="username" id="userForm" onMouseDown="clearUsername();" size="15" type="text"><br>
												<input type="password" name="password" value="password" id="passForm" onMouseDown="clearPassword();" onKeyDown="pressedEnter(event);" onKeyUp="pressedEnter(event);" onKeyPress="pressedEnter(event);" size="15">
												<br>
											</form>
												<input src="/images/register.gif" value="Register" type="image" class="regsiterBtn"  onMouseUp="void(document.location='/register.php');"> &nbsp; <input src="/images/login.gif" value="Login" type="image"  onMouseUp="void(document.forms['loginForm'].submit())">

										</div>
									</div>
									<?php
									}else if(!empty($loginValid)){
									?>
										<div class="userInfo" align="right">
												<?php 
													//get user id stuff....
														$getCredientials->getStats();
													
													//Prevariables
														$totalBalance = "0.00";
													
													//Get Tradehill worth
															$tradeHillQ = mysql_query("SELECT `tradeHillWorth` FROM `websiteSettings`");
															$tradeHillWorth = mysql_fetch_object($tradeHillQ);
															
													//Get default currency settings
														$dbcurrencySetting = outputCurrency();
														
													//Output the users funds according to admin currency settings
														if($dbcurrencySetting == "tradehill-USD"){
														
															//Set total balance in USD with trade hill data	
																$totalBalance = "$".($tradeHillWorth->tradeHillWorth*$getCredientials->accountBalance);
														
														}
														
														if($dbcurrencySetting == "btc"){
															//Set estimated balance
														
															//Set total balance in BTC
																$totalBalance = $getCredientials->accountBalance.' BTC';
														}
																	
												?>
												<span class="userText">Bitcoin Value: <a href="http://www.tradehill.com/TradeData?r=TH-R13231" target="tradeHillPage"><span class="bitcoinWorth">$<?php echo $tradeHillWorth->tradeHillWorth;?></span></a></span><br/>
												<span class="userText">Estimated: <span class="estimated"><?php echo $getCredientials->estimatedReward;?> BTC</span></span><br/>
												<span class="userText">Total Balance: <span class="confirmedBalance"><?php echo $totalBalance;?></span></span><br/>
												<a href="/logout.php">Logout</a>
										</div>
									</div>
									<?php
									}
									?>
							</div>
						</div>
					</div>
					<div class="art-nav">
						<div class="art-nav-l"></div>
						<div class="art-nav-r"></div>
						<div class="art-nav-wrapper">
							<div class="art-nav-inner">
								<ul class="art-menu">
<?php
									//The following will configure which link is selected 
										$currentfile =  $_SERVER['REQUEST_URI'];
											
										//Display menu and set class="active" to the filename that matches the currently viewed page
											$retireveMenu = mysql_query("SELECT `displayTitle`, `url`, `matches`, `requireLogin`, `requireAdmin` FROM `menuAddition` ORDER BY `order` ASC")or die(mysql_error());
											
											
											//Display Users menu (Logged in or Not)
												while($menu = mysql_fetch_array($retireveMenu)){
													$showItem = 0;
													/*Before we output this menu item, 
														1]Find out if we have to display it in the first place
														2]we will find out if this needs to be set to class="active"
													*/
													
													//Does this menu item require a valid login?
														if($menu["requireLogin"] == 1){
															//Check if user is logged in
																if(!empty($loginValid)){
																	$showItem = 1;
																}
															
														}else if($menu["requireLogin"] == 0 && $menu["requireAdmin"] == 0){
															$showItem = 1;
														}
														
														//If this item is for admins just skip it until we get to the admin menu
															if($menu["requireAdmin"] == 1){
																$showItem = 0;
															}
														
														
													//Should we output this item?
													if($showItem == 1){
														/*
															Go through every $menu["match"](after turned into an array) 
															and if one matches with $currentfile then output this menu
															as selected
														*/
															$matchFound = 0;
															$loopMatch = explode(",", $menu["matches"]);
															$numMatches = count($loopMatch);
															for($i = 0; $i < $numMatches; $i++){
																//If match has not been found, continue... (This should save milliseconds of cpu time)
																	if($currentfile == $loopMatch[$i] && $matchFound == 0){
																		$matchFound = 'class="active"';
																	}
																	
															}
												
?>
									<li>
										<a href="<?php echo $menu["url"];?>"<?php if($matchFound){ echo $matchFound; }?>>
											<span class="l">
											</span>
											<span class="r">
											</span>
											<span class="t">
												<?php echo $menu["displayTitle"];?>
											</span>
										</a>
									</li>
<?php
													}
												}
?>
								</ul>																				
							</div>
<?php
							//The following will calculate weather or not we need another menu bar becuase we ran out of space
						
												//Display admin panels if needed
													if(!empty($isAdmin) && $isAdmin == 1){
?>
							<div class="art-nav-inner">
								<ul class="art-menu">
<?php
															//Re-Retrieve again, menu
															$retireveMenu = mysql_query("SELECT `displayTitle`, `url`, `matches`, `requireLogin`, `requireAdmin` FROM `menuAddition` ORDER BY `order` ASC")or die(mysql_error());
											
															//Display admin menu (Logged in and validated)
																while($menu = mysql_fetch_array($retireveMenu)){
																	$showItem = 0;
																	/*Before we output this menu item, 
																		1]Find out if we have to display it in the first place
																		2]we will find out if this needs to be set to class="active"
																	*/
																		
																		//If this item is for admins just skip it until we get to the admin menu
																			if($menu["requireAdmin"] == 1){
																				$showItem = 1;
																			}
																		
																		
																	//Should we output this item?
																		if($showItem == 1){
																
?>
													<li>
														<a href="<?php echo $menu["url"];?>"<?php if($matchFound){ echo $matchFound; }?>>
															<span class="l">
															</span>
															<span class="r">
															</span>
															<span class="t">
																<?php echo $menu["displayTitle"];?>
															</span>
														</a>
													</li>
<?php
																	}
																}
?>
								</ul>
							</div>
<?php
													}
?>							
<?php

?>						</div>
					</div>
					<!-- End of Line for class="art-nav" or navigation to be precise -->
					<div style="width:99%;overflow-x:hidden; margin:0 auto;text-align:center;padding:1em;">
						<div style="">
							<!-- Begin: adBrite, Generated: 2011-07-14 23:18:11  -->
							<script type="text/javascript">
							var AdBrite_Title_Color = '0000FF';
							var AdBrite_Text_Color = '000000';
							var AdBrite_Background_Color = 'FFFFFF';
							var AdBrite_Border_Color = 'CCCCCC';
							var AdBrite_URL_Color = '008000';
							try{var AdBrite_Iframe=window.top!=window.self?2:1;var AdBrite_Referrer=document.referrer==''?document.location:document.referrer;AdBrite_Referrer=encodeURIComponent(AdBrite_Referrer);}catch(e){var AdBrite_Iframe='';var AdBrite_Referrer='';}
							</script>
							<span style="white-space:nowrap;"><script type="text/javascript">document.write(String.fromCharCode(60,83,67,82,73,80,84));document.write(' src="http://ads.adbrite.com/mb/text_group.php?sid=1977178&zs=3732385f3930&ifr='+AdBrite_Iframe+'&ref='+AdBrite_Referrer+'" type="text/javascript">');document.write(String.fromCharCode(60,47,83,67,82,73,80,84,62));</script>
							<a target="_top" href="http://www.adbrite.com/mb/commerce/purchase_form.php?opid=1977178&afsid=1"><img src="http://files.adbrite.com/mb/images/adbrite-your-ad-here-leaderboard.gif" style="background-color:#CCCCCC;border:none;padding:0;margin:0;" alt="Your Ad Here" width="14" height="90" border="0" /></a></span>
							<!-- End: adBrite -->
						</div>
						
						<!--Google Analytics-->
						<script type="text/javascript">
						var _gaq = _gaq || [];
						_gaq.push(['_setAccount', 'UA-24560939-1']);
						_gaq.push(['_trackPageview']);

						(function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
						})();

						</script>
						<!-- ENd google analytics-->
						<div style="display:none;">
							<!--Set some tags so google can know WTF this site is about when first installed -->
							<b>Bitcoin</b>,<b>Bitcoins</b><b>Mining</b>, <b>Farm</b>, <b>Mining Farm</b>, <b>Collect bitcoins</b>, <b>Freedom</b>, <b>small government</b>
						</div>
					</div>
