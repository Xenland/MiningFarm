<?php
//Define the undefined
	$goodMessage = "";
	$returnError = "";
	$tmpUsername = "";
	$tmpPassword = "";
	$tmpPassword2	= "";
	$tmpEmail	= "";
	$tmpEmail2	= "";
	$tmpAuthPin	= "";
	$getCredientials = 0;
	

// Load Linkage Variables //
	$dir = dirname(__FILE__);
	$req 		= $dir."/req/";
	$functions	= $req."functions.php";

//Load Functions
	include($functions);

//Include bitcoind functions
	include($bitcoind);

//Load PHPMailer Source(http://phpmailer.worxware.com/)
	require_once($req."class.phpmailer-lite.php");

//Perform login
	if(!empty($_COOKIE[$cookieName])){
		$getCredientials	= new getCredientials;
		$loginSuccess		= $getCredientials->checkLogin($_COOKIE[$cookieName]);
	}
//Connect to Db
	connectToDb();

//Perform register if the user isn't already logged in
	if(!empty($_POST["act"])){
		$act =  $_POST["act"];
	}else if(empty($_POST["act"])){
		$act = "";
	}
	
	if($act == "signup"){
		if($loginSuccess == 0){
			//Check inputted details make sure they are okay
				$validCredentials = 1;
				if($_POST["password"] != $_POST["password2"]){
					$validCredentials = 0;
				}
				if($_POST["email"] != $_POST["email2"]){
					$validCredentials = 0;
				}

				if(strlen($_POST["authPin"]) < 4){
					$validCredentials = 0;
				}

				//If $validCredientials is still true, then send the user an email
				if($validCredentials == 1){
					//Mysql Injection Protection Agency
						$email = mysql_real_escape_string($_POST["email"]);
						$username = mysql_real_escape_string($_POST["username"]);
						$password = mysql_real_escape_string($_POST["password"]);
						$authPin = mysql_real_escape_string($_POST["authPin"]);
						
					//Make sure the username isn't already in the databse
						$usernameExistsQ = mysql_query("SELECT `id` FROM `websiteUsers` WHERE `username` = '".$username."'");
						$usernameExists = mysql_num_rows($usernameExistsQ);

						if($usernameExists == 0){
								//Hash password
									
									$hashedPassword = hash("sha256", $password);

								//Generate an authoriseEmailPin
									$authoriseEmailPin = genRandomString(64);

								//Generate an API Token
									$apiToken = genRandomString(64);
									//Check if anyone else has this token (doubtfull but on a long enough timeline anything can happen)
										for($i=0; $i < 999; $i++){
											$tokenTaken = mysql_query("SELECT `id` FROM `websiteUsers` WHERE `apiToken` = '".$apiToken."' LIMIT 0,1");
											$tokenIsTaken = mysql_num_rows($tokenTaken);

											if($tokenIsTaken){
												//Generate another token
													$apiToken = genRandomString(64);
											}else if(!$tokenIsTaken){
												//Stop the loop, we've found good api token
													$i=1000;
											}
										}	
									
								//Hash auth pin
									$authPin = hash("sha256", $authPin);
								//Insert user into the `websiteUsers` database and retireve the `id`
									$insertSuccess = mysql_query("INSERT INTO `websiteUsers`
														(`username`, `password` , `emailAuthorisePin`, `email`, `authPin`, `apiToken`)
													VALUES('$username', '$hashedPassword', '$authoriseEmailPin', '$email', '$authPin', '$apiToken')") or die(mysql_error());
									
									//Get userId
									$insertId = mysql_insert_id();

									//If user was successfully added to database
									if(isSet($insertSuccess) && $insertId > 0){
										$authorizeEmail = mysql_query("SELECT `enableRequiredEmail` FROM `websiteSettings` LIMIT 1");
										$authorizeEmail = mysql_fetch_object($authorizeEmail);
										
										if($authorizeEmail->enableRequiredEmail == 1){
											//Send confirmation email if neccesarry
													//Get prefix message to write the user
														$emailMessageQ = mysql_query("SELECT `noreplyEmail`, `confirmEmailPrefix` FROM `websiteSettings` LIMIT 0,1");
														$emailMessageObj = mysql_fetch_object($emailMessageQ);
														$noreplyEmail = $emailMessageObj->noreplyEmail;
														$message = $emailMessageObj->confirmEmailPrefix;
													
														//Add suffix to the activation link
															$serverAddress = $_SERVER['HTTP_HOST'];
															$message .= "\nAuthorization #
																	\n".$authoriseEmailPin."
																	\nhttp://$serverAddress/activateAccount.php?authNumber=".$authoriseEmailPin."&username=".$username;
												
													//Send an email with all the information
														if($noreplyEmail != ''){
															$sendFrom = $noreplyEmail;
														}else{
															$sendFrom = "no-reply@".$serverAddress;
														}
														
	
														$mailSent = mail($to, $subject, $message, $headers);
														//Initiate PHPMailer | Source(http://phpmailer.worxware.com/)

														$mail             = new PHPMailerLite(); // defaults to using php "Sendmail" (or Qmail, depending on availability)
														
														$mail->IsMail(); // telling the class to use native PHP mail()
														
														
														$mail->SetFrom($sendFrom, $sendFrom);
														
														$mail->AddAddress($email, $email);
														
														$mail->Subject    = "Account Activation For ".$username;
					
														$mail->MsgHTML($message);
														
														if(!$mail->Send()) {
														  echo "Mailer Error: " . $mail->ErrorInfo;
														} else {
														  echo "Message sent!";
														}

														if($mailSent){
															$goodMessage = gettext("Registration was a success! | Login to your email, ").$_POST["email"].gettext(" and click on link to activate your account!");
														}else if($mailSent == false){
															$returnError = gettext("Registration was Qusi-Successfull | Your account information has been added to the database how ever there seems to be a problem with sending emails on our end, Try contacting the Pool Operator");
												
														}
										}else if($authorizeEmail->enableRequiredEmail == 0){
											$goodMessage = "Your account $username is now active you may now loging and start mining!";
										}

										//Add a zero balance to `accountBalance`
											mysql_query("INSERT INTO `accountBalance` (`userId`, `balance`)
																VALUES('".$insertId."', '0.00')");
									}else{
										$returnError = gettext("Database error | User was not added to database, Please contact the admin.");
									}
							}else if($usernameExists > 0){
								$returnError = gettext("That username is already registered with us");
							}
				}else if($validCredentials == 0){
					$returnError = gettext("Please check that you passwords match as well as your email; Auth Pin must be numbers only and 4 digits long no more, no less.");
				}
		}else{
			$returnError = gettext("You are already have an account with us.");
		}
	}


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
																	Apply for a Mining slot
																</h1>	
															</div>
															<div class="blogContent">
														
																<?php if($goodMessage || $returnError){?>
																<span class="goodMessage"><?php echo $goodMessage; ?></span><br/>
																<span class="returnError"><?php echo $returnError; ?></span><br/><br/>
																<?php }

																	if($goodMessage == ""){
																?>
																		<h2 id="registerHeader"><?php echo gettext("We just need a few details");?></h2><br/>
																		
																		<form action="/register.php" method="post">
																			<input type="hidden" name="act" value="signup"/>
																			<?php
																				if(isSet($_POST["username"])){
																					$tmpUsername	= $_POST["username"];
																				}else{
																					$tmpUsername 	= "";
																				}
																				if(isSet($_POST["password"])){
																					$tmpPassword	= $_POST["password"];
																				}else{
																					$tmpPassword	= NULL;
																				}
																				if(isSet($_POST["password2"])){
																					$tmpPassword2	= $_POST["password2"];
																				}else{
																					$tmpPassword2	= "";
																				}
																				if(isSet($_POST["email"])){
																					$tmpEmail	= $_POST["email"];
																				}else{
																					$tmpEmail	= "";
																				}
																				if(isSet($_POST["email2"])){
																					$tmpEmail2	= $_POST["email2"];
																				}else{
																					$tmpEmail2	= "";
																				}
																				if(isSet($_POST["authPin"])){
																					$tmpAuthPin	= $_POST["authPin"];
																				}else{
																					$tmpAuthPin	= "";
																				}
																			?>
																			<?php echo gettext("Username");?>:<input type="text" name="username" value="<?php echo $tmpUsername; ?>" maxlength="20" size="10"/><br/>
																			<?php echo gettext("Password");?>:<input type="password" name="password" value="<?php echo $tmpPassword;?>" maxlength="45" size="10"/><br/>
																			<?php echo gettext("Retype Password");?>:<input type="password" name="password2" value="<?php echo $tmpPassword2;?>" maxlength="45" size="10"/><br/>
																			<hr size="1" width="100%"><br/>
																			<?php echo gettext("Real Email");?>: <input type="text" name="email" value="<?php echo $tmpEmail;?>" size="15"/><br/>
																			<?php echo gettext("Retype Email");?>l: <input type="text" name="email2" value="<?php echo $tmpEmail2;?>" size="15"/><br/>
																			<hr size="1" width="100%"><br/>
																			<?php echo gettext("Authorization Pin");?>: <input type="password" name="authPin" value="<?php echo $tmpAuthPin;?>" size="4" maxlength="4"><?php echo gettext("(Memorize this 4 digit pin #)");?><br/>
																			<input type="submit" value="<?php echo gettext("Sign Me Up!");?>" />
																		</form>
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
?>