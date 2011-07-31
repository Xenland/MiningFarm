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
	
//Set message depending on wheather the user is logged in or not
	if($loginValid == 1){
		$contentTitle = "Redirecting you to the donations page....";
		$contentMessage = "Just wait a couple of seconds, I guarantee that before you even done reading this you'll see the page that will assist you in donationg your coins to a good cause, such as the support of this free web-software that you are interacting right now, or even Red Cross";
	}else if($loginValid == 0){
		$contentTitle = "You must be logged into donate your coins";
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
															<div class="blogContainer">
																	<div class="blogHeader">
																		<h1 class="blogHeader">
																			<?php echo $contentTitle;?>
																		</h1>	
																	</div>
																	<div class="blogContent">
																		<?php echo $contentMessage; ?>
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