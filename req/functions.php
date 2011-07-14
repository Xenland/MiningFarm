<?php
//Include config.php file to ensure that updates are easier becuase of the sperate file
include("config.php");

//Locali
$language = 'en';
putenv("LANG=$language.utf8");
setlocale(LC_ALL, $language);

//Set the text domain as 'messages'
$domain = 'messages';
bindtextdomain($domain, $dir."/req/language/");
textdomain($domain);


//////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////   START   ////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

//Random string Generator
function genRandomString($length=10){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
	$str = "";
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
}

///////////////Internal functions ////////////////////////////////////////////////////////
function connectToDb(){
	//Make global
		global $mysqlHost, $mysqlUsername, $mysqlPassword, $mysqlDatabase;

	//Connect
		mysql_connect($mysqlHost, $mysqlUsername, $mysqlPassword);
		mysql_select_db($mysqlDatabase);
}


function loginUser($username, $password){
	//Predefine variables to kill notice_errors
		$loginSuccess = 0;
		$emailNeedsAuth = 0;
		$emailNeedsAuthQ = 0;
		
	//Make global
		global $cookieName, $cookiePath, $cookieDomain;

	//Connect to db
		connectToDb();

	//mysql injection protection
		$username = mysql_real_escape_string($username);

	//Hash password for password checking
		$password = hash("sha256", $password);
	//Check if this login an username is correct

		$loginCheckQ	= mysql_query("SELECT `id`, `emailAuthorised`, `disabled` FROM `websiteUsers` WHERE `username` = '".$username."' AND `password` = '".$password."' LIMIT 0,1")or die(mysql_error());
		$loginExists	= mysql_num_rows($loginCheckQ);
		$loginObj	= mysql_fetch_object($loginCheckQ);
		$userId 	= $loginObj->id;
		$emailAuthorised = $loginObj->emailAuthorised;
		$disabled	= $loginObj->disabled;

	//Set cookie; If validlogin is true
		if($disabled == 0){
			if($loginExists == 1){
				//Check if email needs to be authorised
					$emailNeedsAuthQ = mysql_query("SELECT `enableRequiredEmail` FROM `websiteSettings` LIMIT 1");
					$emailNeedsAuth = mysql_fetch_object($emailNeedsAuthQ);
					$emailNeedsAuth = $emailNeedsAuth->enableRequiredEmail;
					$emailSuccess = 0;
					
					if($emailNeedsAuth == 1){
						if($emailAuthorised == 1){
							$emailSuccess = 1;
						}
					}else{
						$emailSuccess = 1;
					}
			
					if($emailSuccess == 1){
						//Get ip address so we can hash with the cookie
							$ip = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
							$timeoutStamp = time()+60*30; //30 minute session

						//Update logged in ip address so no one can steal this cookie hash unless
							mysql_query("UPDATE `websiteUsers` SET `sessTimestamp` = ".$timeoutStamp.", `loggedIp` = '".$ip."' WHERE `id` = '".$userId."'");

						//Generate random secret
							$randomSecret = genRandomString(10);
							//Update random string to database so we can hash it into the cookie
								mysql_query("UPDATE `websiteUsers` SET `randomSecret` = '".$randomSecret."' WHERE `id` = '".$userId."'");

						//Set cookie in browser for session
							//Get hashed password for hashing the cookie
								$getPassQ = mysql_query("SELECT `password` FROM `websiteUsers` WHERE `id` = '".$userId."'");
								$getPassObj = mysql_fetch_object($getPassQ);

							//Make cookie :)
								$hash	= $randomSecret.$password.$ip.$timeoutStamp;
								$hash = hash("sha256", $hash);
								setcookie($cookieName, $userId."-".$hash, $timeoutStamp, $cookiePath, $cookieDomain);

						//Successfull login code
							$loginSuccess = 1;

					}else if($emailSuccess == 0){
						//Email was not authorised	
							$loginSuccess = 3;
					}
			}else if($loginExists <= 0){
				//Wrong user name and or password
					$loginSuccess = 4;
			}
		}else if($disabled == 1){
			$loginSuccess = 5;
		}
	//Return Boolean;
		return $loginSuccess;
}

class getCredientials{
	//Validation variables (Don't change)
		public $validCookie = 0;
		public $userId = 0;
		public $hashedAuthPin = "";
		public $isAdmin = 0;

	//Stats variables
		public $username = "";
		public $totalShares = 0;
		public $totalPoolShares = 0;
		public $estimatedReward = 0;
		public $accountBalance = "0";
		public $pendingBalance = "0";
		public $email = "";
		public $sendAddress = "";
		public $threshHold = 0;
		public $apiToken = "";
		public $statsShowAllUsers = 0;
		
	//Admin variables
		public $adminHeader = "";
		public $adminSlogan = "";
		public $adminBrowserTitle = "";
		public $adminCashoutMin = 0;
		public $adminEmail = "";
		public $emailAuthorisationRequired = 1; //Default is 1

	function checkLogin($rawCookie){
		
			
		//Make defined
			$explodeCookie = "";
			$validCookie = 0;
			$cookieHash = "";
			$cookieUserid = 0;
			$dbTimestamp = "";
			$dbrandomSecret = "";
			$dbIp	= "";
			$dbPassword = "";
			$dbAuth	= "";
			$dbisAdmin = "";

		//Make global
			global $cookieName, $cookiePath, $cookieDomain;


		if(isSet($rawCookie)){
			//Preset vars
				$ip = $_SERVER['REMOTE_ADDR'];

			//Connect to db
				connectToDb();
			
			//Split cookie into 2
				$explodeCookie = explode("-",$rawCookie);
				$cookieUserid = mysql_real_escape_string($explodeCookie[0]);
				$cookieHash = mysql_real_escape_string($explodeCookie[1]);

			//Get database information to match the cookie hash with
				$dbHashInfoQ = mysql_query("SELECT `sessTimestamp`, `randomSecret`, `loggedIp`, `password`, `authPin`, `isAdmin`, `apiToken` FROM `websiteUsers` WHERE `id` = '".$cookieUserid."' LIMIT 0,1")or die(mysql_error());
				while($dbHashInfoObj =  mysql_fetch_array($dbHashInfoQ, MYSQL_ASSOC)){
				
					//Define variables
						$dbTimestamp	= $dbHashInfoObj["sessTimestamp"];
						$dbrandomSecret	= $dbHashInfoObj["randomSecret"];
						$dbIp		= $dbHashInfoObj["loggedIp"];
						$dbPassword	= $dbHashInfoObj["password"];
						$dbAuth		= $dbHashInfoObj["authPin"];
						$dbisAdmin	= $dbHashInfoObj["isAdmin"];
						$dbApiToken	= $dbHashInfoObj["apiToken"];
				}

			//Hash database information to check against the already hash cookie.
				$dbHash = $dbrandomSecret.$dbPassword.$dbIp.$dbTimestamp;
				$dbHash = hash("sha256", $dbHash);

			//Make sure the supplied $ip == $dbIp;
				if($ip == $dbIp){
					//Ip address matches now check forthe cookie and database hashes *Cross your fingers :)
						if($dbHash == $cookieHash){
							$validCookie = 1;
							$this->validCookie = 1;
							$this->userId = $cookieUserid;
							$this->hashedAuthPin = $dbAuth;
							$this->isAdmin	= $dbisAdmin;
							$this->apiToken = $dbApiToken;
						}
				}
		}
		return $validCookie;
	}


	function getStats(){
		//Define the undefined
			$username = "";
			$email	= "";
			$balance = "";
			$threashhold = 0;
			$sendAddress = "";
			$totalShares = 0;
		//Connect to db
			connectToDb();

		//Get username for prefix searching
			$getUsernameQ = mysql_query("SELECT `username`, `email` FROM `websiteUsers` WHERE `id` = '".$this->userId."' LIMIT 0,1");
			
			while($getUsernameObj = mysql_fetch_object($getUsernameQ)){
				$username	= $getUsernameObj->username;
				$email		= $getUsernameObj->email;
			}
			
		//Get number of shares this user has inputted
			$shareCountQ = mysql_query("SELECT `id` FROM `shares` WHERE `username` LIKE '$username.%' AND `our_result` = 'Y'")or die(mysql_error());
			$totalShares = mysql_num_rows($shareCountQ);

		//Get number of total pool shares
			$totalPoolSharesQ = mysql_query("SELECT `id` FROM `shares` WHERE `our_result` = 'Y'");
			$totalPoolShares = mysql_num_rows($totalPoolSharesQ);

		//Get estimated earnings
			$estReward = 0;
			if($totalPoolShares > 0 && $totalShares > 0){
				//Get share to poolshares ratio
					$estReward = $totalShares/$totalPoolShares;
				
				//Get estimated shares after fee
					$adminFee = getAdminFee();
					$estReward = round((50*$estReward)-(50*($adminFee*.01)), 2);
			}
			
			
			

		//Get account balance
			$balanceQ = mysql_query("SELECT `payoutAddress`, `balance`, `threshhold` FROM `accountBalance` WHERE `userId` = '".$this->userId."' LIMIT 0,1")or die(mysql_error());
			$balance = 0;
			while($balanceObj = mysql_fetch_object($balanceQ)){
				$balance = $balanceObj->balance;
				$threshhold = $balanceObj->threshhold;
				$sendAddress = $balanceObj->payoutAddress;
			}
			
		   
		//Get unconfirmed balance
		        //Go through all the `shares_history` Blocks numbers that have been found
		            $unconfirmedBalance = 0;
		            $blockHistoryQ = mysql_query("SELECT DISTINCT `blockNumber` FROM `shares_history` WHERE `username` LIKE '$username.%'");
		            while($block = mysql_fetch_array($blockHistoryQ)){
		                //With the selected $block, check estimated balance from that round
		                    $getRoundSharesQ = mysql_query("SELECT `id` FROM `shares_history` WHERE `blockNumber` = '".$block["blockNumber"]."' AND `username` LIKE '$username.%' AND `our_result` = 'Y'");
		                    $numRoundShares = mysql_num_rows($getRoundSharesQ);
		
		
		                    $getTotalRoundSharesQ = mysql_query("SELECT `id` FROM `shares_history` WHERE `blockNumber` = '".$block["blockNumber"]."' AND `our_result` = 'Y'");
		                    $numTotalRoundShares = mysql_num_rows($getTotalRoundSharesQ);
		
		                //Calculate balance & prvent division by zero
		                	if($numRoundShares > 0 && $numTotalRoundShares > 0){
		               		     $unconfirmedBalance += round((50*($numTotalRoundShares/$numRoundShares))-(50*($adminFee*.01)), 8);
		               		}
		            }
				
		//Set stats variables
			$this->username		= $username;
			$this->totalShares	= $totalShares;
			$this->totalPoolShares	= $totalPoolShares;
			$this->estimatedReward	= $estReward;
			$this->pendingBalance	= $unconfirmedBalance;
			$this->accountBalance 	= round($balance, 0);
			$this->email		= $email;
			$this->sendAddress	= $sendAddress;

		return true;
	}

	function getAdminSettings(){
			//Connect to db
				connectToDb();

			//Get website settings
				$websiteSettingsQ = mysql_query("SELECT `noreplyEmail`, `browserTitle`, `cashoutMinimum`, `stats_showallusers`, `enableRequiredEmail` FROM `websiteSettings`");
				$websiteSettings = mysql_fetch_object($websiteSettingsQ);

				$this->adminBrowserTitle	= $websiteSettings->browserTitle;
				$this->adminCashoutMin		= $websiteSettings->cashoutMinimum;
				$this->adminEmail		= $websiteSettings->noreplyEmail;
				$this->statsShowAllUsers	= $websiteSettings->stats_showallusers;
				$this->emailAuthorisationRequired = $websiteSettings->enableRequiredEmail;
		}
}

function getCashoutMin(){
	//Connect to db
		connectToDb();

	//Get cash out minimum
		$cashOutMinQ = mysql_query("SELECT `cashoutMinimum` FROM `websiteSettings`");
		$cashOutMinObj = mysql_fetch_object($cashOutMinQ);
	
		return (int)$cashOutMinObj->cashoutMinimum;
}



function activateAccount($username, $authPin){
		$detailsMatch = 0;

	//Connect to database
		connectToDb();

	//Mysql injection protection
		$email = mysql_real_escape_string($email);
		$emailAuthPin = mysql_real_escape_string($authPin);


	//Check if details match
		$detailsMatchQ = mysql_query("SELECT `email`, `id` FROM `websiteUsers` WHERE `username` = '$username' AND `emailAuthorisePin` = '$emailAuthPin'")or die(mysql_error());
		$detailsMatch = mysql_num_rows($detailsMatchQ);
		$detailsObj = mysql_fetch_object($detailsMatchQ);


		if($detailsMatch > 0){
			//Activate this account
				$userId = $detailsObj->id;
				mysql_query("UPDATE `websiteUsers` SET `emailAuthorised` = '1' WHERE `id` = '$userId'");
		}
	return $detailsMatch;
}


class stats{
	function outputPoolTotal($timestamp_threshhold=0){
		//This function will output the pools total mhashes/sec
		
		//Connect to DB
			connectToDb();
			
			
					
	}
}


////////////// Content Output ////////////////////////////////////////////////////////////
//Output the admin fee
function getAdminFee(){
	//Connect to database
		connectToDb();

	//Get adminfee
		$adminFeeQ = mysql_query("SELECT `serverFeePercentage` FROM `websiteSettings`");
		$adminFee = mysql_fetch_object($adminFeeQ);
		$adminFee = $adminFee->serverFeePercentage;
		
	//Output admin fee
		return $adminFee;		
}

//Output the browser title bar

function outputPageTitle(){
	//Connect to database
		connectToDb();

	//Get browser title
		$browserTitleQ = mysql_query("SELECT `browserTitle` FROM `websiteSettings`");
		$browserTitle = mysql_fetch_object($browserTitleQ);

	//Output browser title
		return $browserTitle->browserTitle;
}

function outputHeaderTitle(){
	//Connect to database
		connectToDb();
	
	//Get header
		$headerQ = mysql_query("SELECT `header` FROM `websiteSettings`");
		$headerObj = mysql_fetch_object($headerQ);

	//Output header
		return $headerObj->header;
}

function outputHeaderSlogan(){
	//Connect to database
		connectToDb();
	
	//Get header
		$sloganQ = mysql_query("SELECT `slogan` FROM `websiteSettings`");
		$sloganObj = mysql_fetch_object($sloganQ);

	//Output header
		return $sloganObj->slogan;
}

function outputCurrency(){
	//Connect to database
		connectToDb();
		
	//get currency setting
		$getcurrency = mysql_query("SELECT `currencyData` FROM `websiteSettings`");
		$currency = mysql_fetch_object($getcurrency);
		
	//Output header
		return $currency->currencyData;
}
?>
