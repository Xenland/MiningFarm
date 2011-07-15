<?php
//Pre define //
	$show = $_GET['show'];
	$searchUsername = $_POST['searchUsername'];
		

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
	if(isSet($_COOKIE[$cookieName])){
		$rawCookie	= $_COOKIE[$cookieName];
	}
	$getCredientials	= new getCredientials;
	$loginValid		= $getCredientials->checkLogin($rawCookie);

	$isAdmin = $getCredientials->isAdmin;
	if($loginValid && $isAdmin){
		//Check if there was an action
		$act = $_POST["act"];
		$hashedAuthInput = hash("sha256", $_POST["authPin"]);
		if($act &&  $hashedAuthInput == $getCredientials->hashedAuthPin){
			if($act == "websiteSettings"){
				//Update websiteSettings
					//Mysql Injection prevention :D
					$postHeader		= mysql_real_escape_string($_POST["header"]);
					$postEmail		= mysql_real_escape_string($_POST["confirmEmail"]);
					$postSlogan		= mysql_real_escape_string($_POST["slogan"]);
					$postBrowserTitle	= mysql_real_escape_string($_POST["browserTitle"]);
					$postCashOut		= mysql_real_escape_string($_POST["cashoutMin"]);
					$postFee			= mysql_real_escape_string($_POST["serverFee"]);
					$currencyData		= mysql_real_escape_string($_POST["currencyData"]);
					$postEmailRequired	= mysql_real_escape_string($_POST["emailAuthRequired"]);
					
					if($postEmailRequired == "on"){
						$postEmailRequired = 1;
					}else if($postEmailRequired == "off"){
						$postEmailRequired = 0;
					}
					
					mysql_query("UPDATE `websiteSettings` 
							SET 	`noreplyEmail` = '".$postEmail."',
								`browserTitle` = '".$postBrowserTitle."',
								`cashoutMinimum` = '".$postCashOut."',
								`serverFeePercentage` = '".$postFee."',
								`currencyData` = '".$currencyData."',
								`enableRequiredEmail` = '".$postEmailRequired."'")or die(mysql_error());
			
			}else if($act == "addBlog"){
				//Add blog to database
					//Mysql injection prevention
						$blogTitle = mysql_real_escape_string($_POST["blogTitle"]);
						$blogContent = mysql_real_escape_string($_POST["blogContent"]);
					mysql_query("INSERT INTO `blogPosts` (`timestamp`, `title`, `message`)
									VALUES('".time()."', '".$blogTitle."', '".$blogContent."')");
			}else if($act == "Update"){
				//Update blog
					//Mysql Injection prevention
						$blogTitle = mysql_real_escape_string($_POST["blogTitle"]);
						$blogContent = mysql_real_escape_string($_POST["blogContent"]);
						$blogId = mysql_real_escape_string($_POST["blogId"]);
					//Do query
						mysql_query("UPDATE `blogPosts` SET `title` = '".$blogTitle."', `message` = '".$blogContent."' WHERE `id`= '".$blogId."'");
			}else if($act == "Delete"){
				echo "YES!";
				//Delete blog
					//MySql injection prevention
						$blogId = mysql_real_escape_string($_POST["blogId"]);
						
					//Do query
						$deleted = mysql_query("DELETE FROM `blogPosts` WHERE `id` = '".$blogId."'");
						
					//Return message
						if($deleted != false){
							$goodMessage = "Blog Deleted!";
						}else{
							$returnError = "Blog wasn't deleted, may be a database query problem";
						}
			}
		}else if($act && $hashAuthInput != $getCredientials->hashedAuthPin){
			$returnError = gettext("Auth pin was not valid!");
		}

		//Check if $show was set, and figure out what to show
		if($show == "updateSearchedUsers"){
			//Go through array of users and update the list of users to disable
				$postUserIds = $_POST["userIdArray"];
				$postUserIds = explode(",", $postUserIds);
				$numIds = count($postUserIds);

			//Update output variables
				$updatedOutput = "";

				for($i=0; $i < $numIds; $i++){
					//Find the selection the admin specified, and set to enable/disabled based on the selction
							$tmpUserId		= $postUserIds[$i];
							$selectedInput		=  $_POST["user".$postUserIds[$i]];
							$selectInput 		= mysql_real_escape_string($selectedInput);
							$tmpUserId		= mysql_real_escape_string($tmpUserId);
							if($selectedInput == "on"){
								//We want to update it to disabled
									mysql_query("UPDATE `websiteUsers` SET `disabled` = 1 WHERE `id` = '".$tmpUserId."' LIMIT 1")or die(mysql_error());
									$updateOutput .='<span class="goodMessage">'.gettext("Set userId ").$tmpUserId.gettext(' to Disabled').'</span><br/>';
						
							}else if($selectedInput == ""){
								//We want to update it to enabled
									mysql_query("UPDATE `websiteUsers` SET `disabled` = 0 WHERE `id` = '".$tmpUserId."' LIMIT 1")or die(mysql_error());
									$updateOutput .='<span class="goodMessage">'.gettext('Set userId ').$tmpUserId.gettext(' to Enable').'</span><br/>';
							}
				}

			//Simulate search
				$show = "searchUsers";
				$searchUsername = $_GET["searchUsername"];
		}
		
		//Decide $panelTitle
			if($show == "editUsers"){
				$panelTitle = "Edit Users";
			}else if($show == "blogEditor"){
				$panelTitle = "Add or Edit Blogs";
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
														<?php	if(!empty($returnError) || !empty($goodMessage)){ ?>
														<h3 class="loginMessages">
															<span class="returnError"><?php echo $returnError;?></span>
															<span class="goodMessage"><?php echo $goodMessage;?></span>
														</h3>
														<?php	} ?>
															<!--Start Administration Panel-->
																
																<div class="blogContainer">
																	<div class="blogHeader">
																		<h1 class="blogHeader">
																			<?php
																				if($panelTitle == ""){
																					echo "Administration Panel";
																				}else if($panelTitle != ""){
																					echo $panelTitle;
																				}
																			?>
																		</h1>	
																	</div>
																	<div class="blogContent">
																			<?php
																				
																				//Decide what we want to display based on what $act says
																				////////////////////////////////////////////////////////
																				$getCredientials->getAdminSettings();
																				if($show == ""){
																					
																			?>
																			
																				<h2 style="text-decoration:underline;"><?php echo gettext("Website Settings");?></h2>
																					<form action="?" method="post">
																						<input type="hidden" name="act" value="websiteSettings">
																						<?php echo gettext("Browser Title");?>:<input type="text" id="browserTitleInput" name="browserTitle" value="<?php echo $getCredientials->adminBrowserTitle;?>" onKeyPress="updateBrowserTitle();" onKeyUp="updateBrowserTitle();" onKeyDown="updateBrowserTitle();"><br/>
																						<?php echo gettext("Confirm Email");?>: <input type="text" name="confirmEmail" value="<?php echo $getCredientials->adminEmail;?>"><br/>
																						<?php echo gettext("Email Authorisation Required for Signup:");?><input type="checkbox" name="emailAuthRequired"<?php if($getCredientials->emailAuthorisationRequired == "1"){ echo ' checked';}?>/><br/>
																						<?php echo gettext("Server Fee");?>: <input type="text" size="5" name="serverFee" value="<?php echo getAdminFee();?>"/>%<br/>
																						<?php echo gettext("Cashout Minimum");?>:<input type="text" name="cashoutMin" value="<?php echo $getCredientials->adminCashoutMin;?>" size="4" maxlength="10">BTC<br/>
																						<br/><h2 style="text-decoration:underline;">Currency Display</h2>
																						<?php echo gettext("Currency Display");?>: <select name="currencyData">
																												<option value="btc">Bitcoins (BTC)</option>
																												<option value="tradehill-USD">United $tates Dollar (USD)</option>
																											</select><br/>
																						<?php echo gettext("Currency Source");?>:<select name="currentSource">
																													<option value="tradehill">TradeHill</option>
																													<option value="mtgox">(Unavailable)Mt. Gox(Unavailable)</option>
																												</select>
																						<hr size="1" width="100%"/><br/><br/>
																						<?php echo gettext("Auth Pin");?>:<input type="password" name="authPin" value="" size="4" maxlength="4"><br/>
																						<input type="submit" value="<?php echo gettext("Update Website Settings");?>" />
																					</form>
																				<hr size="1" width="100%"><br/><br/>
																			<?php
																				}else if($show == "editUsers"){
																			?>
																				<h2 style="text-decoration:underline;">Search for a user (% = Wildcard)</h2>
																					<form action="?show=searchUsers" method="post">
																						By username:<input type="text" name="searchUsername" value=""/><br/>
																						<input type="submit" value="Search For user">
																					</form>
																			<?php
																				}else if($show == "searchUsers"){
																			?>
																				<div class=<?php echo $updateOutput;?>
																				<h2 style="text-decoration:underline;">Search for a user (% = Wildcard)</h2>
																					<form action="?show=searchUsers" method="post">
																						By username:<input type="text" name="searchUsername" value=""/><br/>
																						<input type="submit" value="Search For user">
																					</form><br/><br/>
																			<?php
																						$searchUsername = mysql_real_escape_string($searchUsername);
																					//Query for a list of users that match this username
																						$searchQ = mysql_query("SELECT `disabled`, `email`, `username`, `id`, `loggedIp` FROM `websiteUsers` WHERE `username` LIKE '".$searchUsername."'");
																			?>
																				<form action="?show=updateSearchedUsers&searchUsername=<?php echo $searchUsername;?>" method="post">
																				<h2 style="text-decoration:underline;">Results for <i><?php echo $searchUsername; ?></i></h2>
																				<input type="submit" value="Execute Changes"><br/>
																					<?php
																						$userIdArray = "";
																						//List output from $searchQ;
																						while($user = mysql_fetch_array($searchQ)){
																					?>
																						<?php echo $user["username"]." &middot; ".$user["email"];?> 
																						<input type="checkbox" name="user<?php echo $user["id"];?>" <?php if($user["disabled"]){ echo "checked";}?> onMouseOver="showTooltip('<span style=\'color:red;\'>Disable</span> this user');" onMouseOut="hideTooltip();"/><br/> 
																					<?php
																							//Make array of userId's to post
																								if($userIdArray != ""){
																									$userIdArray .= ",";
																								}
																								$userIdArray .= $user["id"];
																						}
																					?>
																						<input type="hidden" name="userIdArray" value="<?php echo $userIdArray;?>"/>
																				</div>
																			<?php
																				}
																			?>
																	</div>
																	<?php
																	if($show == "blogEditor"){
																	?>
																		<!--Add a blog entry-->
																		<div class="blogContainer">
																			<form action="/adminPanel.php?show=blogEditor" method="post">
																				<input type="hidden" name="act" value="addBlog"/>
																				<div class="blogHeader" style="height:16em;margin-bottom:5em;">
																					<h1 class="blogHeader">
																						<input type="text" name="blogTitle" value="Blog Header"/>
																					</h1>
																					<textarea cols="90" rows="10" name="blogContent">Type your blog entry here :)</textarea>
																					Authorisation Pin:<input type="password" name="authPin" value="" size="4" maxlength="4"/><br/>
																					<input type="submit" name="" value="Add Blog Entry"/>
																					
																					<br/><br/>
																				</div>
																			</form>
																		</div>
																	<?php
																					//Get list of blogs
																						$blogList = mysql_query("SELECT `id`,`timestamp`, `title`, `message` FROM `blogPosts` ORDER BY `timestamp` DESC");
																						while($blog = mysql_fetch_array($blogList)){
																	?>
																				
																		<div class="blogContainer">
																			<form action="/adminPanel.php?show=blogEditor" method="post">
																				<input type="hidden" name="blogId" value="<?php echo $blog["id"];?>"/>
																			<div class="blogHeader" style="height:17em;">
																				<h1 class="blogHeader">
																					<input type="text" name="blogTitle" value="<?php echo $blog["title"]; ?>" />
																				</h1>
																				<textarea cols="90" rows="10" name="blogContent"><?php echo $blog["message"];?></textarea><br/>
																				Authorization Pin:<input type="password" name="authPin" value="" size="4" maxlength="4"/><br/>
																				<input type="submit" name="act" value="Update"/> &middot; <input type="submit" name="act" value="Delete"/>
																				
																				<br/><br/>
																			</div>
																			</form>
																		</div>
																	<?php
																				}
																		}
																	?>
																		
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
																		