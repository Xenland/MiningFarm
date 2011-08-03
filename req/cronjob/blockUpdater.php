<?php
$startTime = gettimeofday(2);

//Comment the following line when debuging this page.
//error_reporting(0);

// Load Linkage Variables //
	$dir = dirname(__FILE__);
	$dir		= str_replace("/req/cronjob", "", $dir);
	$req 		= $dir."/req/";
	$functions	= $req."functions.php";

//Load Functions
	include($functions);

//Load bitcoind function
	include($bitcoind);

//Connect to database
	connectToDb();

		
//The following has nothing to do with updating the blocks but it DOES execute the auto instant payment for every users that has there threshold set
	//Get minimum cashout
		$minimumCashoutQ = mysql_query("SELECT `cashoutMinimum` FROM `websiteSettings`");
		$minimumCashoutObj = mysql_fetch_object($minimumCashoutQ);
		$minimumCashout = $minimumCashoutObj->cashoutMinimum;

		//Get list of `balances` FROM `accountBalance` that are greater then the cashoutMinumum
			$getListOfAccountsQ = mysql_query("SELECT `id`, `balance`, `userId`, `threshhold`, `payoutAddress` FROM `accountBalance` WHERE `balance` > '$minimumCashout' AND `payoutAddress` != ''");
			while($accounts = mysql_fetch_array($getListOfAccountsQ)){
				//Only send balance if there balance exceeds their threshold
					if($accounts["threshhold"] <= $accounts["balance"]){
						//Send `balance` to `payoutAddress`
							$sentSuccessfull = $bitcoinController->sendtoaddress($accounts["payoutAddress"], $accounts["balance"]);
					
						if($sentSuccessfull != false){
							//Reset balance to zero
								mysql_query("UPDATE `accountBalance` SET `balance` = '0' WHERE `id` = '".$accounts["id"]."'");
						}
					}
			}

	
//Retireve JSON data from trade hill update it to database for quick retireval
try{
	$file = fopen("https://api.tradehill.com/APIv1/USD/Ticker", "rb");
	$tradedata = fread($file, 8192);
	fclose($file);

	//get trade hill json data
		$jsonTradedata = json_decode($tradedata, true);
		
	//calculate average with the provided data (Buy, sell, last sale)
		//$tradeHillWorth = round((($jsonTradedata[ticker][last]+$jsonTradedata[ticker][sell]+$jsonTradedata[ticker][buy])/3), 2);
		$tradeHillWorth = round($jsonTradedata[ticker][last], 2);
	//Insert data
		mysql_query("INSERT INTO `stats_bitcoinConversionHistory` (`tradehill`, `timestamp`) VALUES('$tradeHillWorth', '".time()."')")or die(mysql_error());

	
		mysql_query("UPDATE `websiteSettings` SET `tradeHillWorth` = '".$tradeHillWorth."'")or die(mysql_error());
		
}catch (Exception $e) {
	echo "Failed to get TradeHill bitcoioin worth<br/>".$e;
}


$lengthOfScript = gettimeofday(2);
$lengthOfScript -= $startTime;

echo '('.($lengthOfScript).")";


?>



