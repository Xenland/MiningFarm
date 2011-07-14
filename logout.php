<?php
//Define the undefined
$returnError = "";
$goodMessage = "";
$loginSuccess = 0;


//Set vital file path
$dir = dirname(__FILE__);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Load Functions
	include($functions);


//Load bitcoind function
	include($bitcoind);


//Log out by deleting the cookie-session
	setcookie($cookieName, "", time()-9999);

//Include the header & slogan

	//Set page title
		$pageTitle = outputPageTitle()." - ";
		$pageTitle .= gettext("Main Page");

	include($header);

//Set a confirmation message
$goodMessage = "You're now logged out, Come back soon!";

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