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
	
//Load PHPMailer Source(http://phpmailer.worxware.com/)
	require_once($req."class.phpmailer-lite.php");	

	//Connect to db
		connectToDb();

	//Figure out which action the user is trying to do
		if(!empty($_POST["act"])){
			$act = $_POST["act"];
				if($act == "resetpassword"){
							//Mysql Injection Protection
								$email = mysql_real_escape_string($_POST["email"]);
								
							if($email){
								//Look up userId
									$userIdQ = mysql_query("SELECT `id` FROM `websiteUsers` WHERE `email` = '".$email."'")or die(mysql_error());
									$userId = mysql_fetch_object($userIdQ);
									$userId = $userId->id;
									
									if($userId > 0){
										//Define the email token
											$emailToken = mysql_real_escape_string(genRandomString(64));
										
										//Update email token if valid id was found
											$updateToken = mysql_query("UPDATE `websiteUsers` SET `emailResetToken` = '".$emailToken."' WHERE `id` = '".$userId."' LIMIT 1")or die(mysql_error());
											
											//Send email with this token
															$serverAddress = $_SERVER['HTTP_HOST'];
															$message .= "\nEmail Reset Token
																	\n".$emailToken."
																	\nhttp://$serverAddress/resetpassword.php?emailToken=".$emailToken."&userId=".$userId;
												
													//Send an email with all the information
														$sendFrom = "no-reply@".$serverAddress;
														
														//Initiate PHPMailer | Source(http://phpmailer.worxware.com/)

														$mail             = new PHPMailerLite(); // defaults to using php "Sendmail" (or Qmail, depending on availability)
														
														$mail->IsMail(); // telling the class to use native PHP mail()
														
														$mail->SetFrom($sendFrom, $sendFrom);
														
														$mail->AddAddress($email, $email);
														
														$mail->Subject    = "Account Password reset from ".$serverAddress;
					
														$mail->MsgHTML($message);
														
														if(!$mail->Send()) {
														  $returnError = "Mailer Error: " . $mail->ErrorInfo;
														} else {
														  $goodMessage =  "Message sent!";
														}
												
									}else if($userId == 0){
										//Tell them we can't find an account with that email
											$returnError = 'We can\'t find an account with that regisered email';
									}
									
							}else if($email == ''){
								//Tell them we have no email to send to
									$returnError = 'Please type in a valid email';
							}
				}
		}
		
		if(!empty($_GET["emailToken"]) || $_GET["emailToken"] != ''){
			//Sanatize variables
				$emailToken = mysql_real_escape_string($_GET["emailToken"]);
				$userId = mysql_real_escape_string($_GET["userId"]);
				
			//Verify if this token matches with the user name
				$findMatchQ = mysql_query("SELECT `username` FROM `websiteUsers` WHERE `emailToken` = '".$emailToken."' AND `id` = '".$userId."' LIMIT 0,1")or die(mysql_error());
				$findMatch  = mysql_fetch_object($findMatchQ);
				
				if($findMatch != ''){
					//Define the email token
						$newPassword = mysql_real_escape_string(genRandomString(64));

						//Hash password
							$newPasswordHashed = hash("sha256", $newPassword);

						//Update password
							mysql_query("UPDATE `websiteUsers` SET `password` = '".$newPasswordHashed."' WHERE `id` = '".$userId."' LIMIT 0,1")or die(mysql_error());
							
					//We have found a valid match, send the user another email with the new password;
					//Send email with this token
						$serverAddress = $_SERVER['HTTP_HOST'];
						$message .= "\nEmail Reset Token
						\n".$emailToken."
						\nhttp://$serverAddress/resetpassword.php?emailToken=".$emailToken."&userId=".$userId;
												
					//Send an email with all the information
						$sendFrom = "no-reply@".$serverAddress;
														
					//Initiate PHPMailer | Source(http://phpmailer.worxware.com/)

						$mail             = new PHPMailerLite(); // defaults to using php "Sendmail" (or Qmail, depending on availability)
														
						$mail->IsMail(); // telling the class to use native PHP mail()
														
						$mail->SetFrom($sendFrom, $sendFrom);
														
						$mail->AddAddress($email, $email);
														
						$mail->Subject    = "Account Password reset from ".$serverAddress;
					
						$mail->MsgHTML($message);
														
						if(!$mail->Send()) {
							$returnError = "Mailer Error: " . $mail->ErrorInfo;
						}else{
							$goodMessage =  "Message sent!";
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
														<?php if($returnError != '' || $goodMessage != ''){?>
														<h3 class="loginMessages">
															<span class="returnError"><?php echo $returnError;?></span>
															<span class="goodMessage"><?php echo $goodMessage;?></span>
														</h3>
														<?php	 } ?>
														<div class="blogContainer">
															<div class="blogHeader">
																<h1 class="blogHeader">
																		<?php echo gettext("Reset Password");?>
																</h1>	
															</div>
															<div class="blogContent">
																<form action="resetpassword.php" method="post">
																	<input type="hidden" name="act" value="resetpassword" />
																	<input type="text" name="email" value="Enter your email here" size="30"/><br/><br/>
																	<input type="submit" value="Send me instructions for reseting my email" />
																</form>
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