<?php
//Define the undefined
$returnError = "";
$goodMessage = "";
$loginSuccess = 0;

//Set page starter variables//
$dir 		= dirname(__FILE__);
$req 		= $dir."/req/";
$functions	= $req."functions.php";

//Include hashing functions
include($functions);

//Load bitcoind function
include($bitcoind);

//Perform login
$loginSuccess = loginUser($_POST["username"], $_POST["password"]);

//Set user details for userInfo box
$rawCookie = "";
if(isSet($_COOKIE[$cookieName])){
	$rawCookie = $_COOKIE[$cookieName];
	$returnError = "";
	$goodMessage = "";
	$loginSuccess = 0;
}

//Preset refreshtime
$refreshTime = 1;

//Generate message
	if($loginSuccess == 1){
		$goodMessage = gettext("Welcome back, ").$_POST["username"];		
	
	}else if($loginSuccess == 0){
		$returnError = gettext("Database Failed to Query<br/>Please Contact the admin ASAP");
			$refreshTime = 10;
	
	}else if($loginSuccess == 3){
		$returnError = gettext("Login failed <br/> You haven't authorised your email account yet");
			$refreshTime = 3;
	
	}else  if($loginSuccess == 4){
		$returnError = gettext("Login failed <br/> Wrong Username Or Password");
			$refreshTime = 3;
	}else if ($loginSuccess == 5){
		$returnError = gettext("Login failed <br/> Your account has been suspended!");
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