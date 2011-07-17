<?php
//Comment the following line when debuging this page.
//error_reporting(0);
set_time_limit(120);
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

//This watches the blocks the bitcoin network is solving and inserts the newly found block number into the `networkBlocks`
	//Open a bitcoind connection
		$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

	//Get some variables
		$currentBlockNumber = $bitcoinController->getblocknumber();
	
	//Is this block number in the database already
		$inDatabaseQ = mysql_query("SELECT `id` FROM `networkBlocks` WHERE `blockNumber` = '$currentBlockNumber' LIMIT 0,1");
		$inDatabase = mysql_num_rows($inDatabaseQ);

		if(!$inDatabase){
			//Add this block into the `networkBlocks` log
				$currentTime = time();
				mysql_query("INSERT INTO `networkBlocks` (`blockNumber`, `timestamp`)
									VALUE('$currentBlockNumber', '$currentTime')")or die(mysql_error());
		}



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




//If shares_dead gets over 3 million shares of data(roughly 1GB) delete the round recorded to start saving space
	$totalDeadShares = mysql_query("SELECT `id` FROM `shares_dead`");
	$totalDeadShares = mysql_num_rows($totalDeadShares);
	
	if($totalDeadShares >= 3000000){
		//delete shares (in the future this will delete by the last block but for this release it deletes all)
			$lastRound = mysql_query("DELETE FROM `shares_dead`");
	}

	
//Retireve JSON data from trade hill update it to database for quick retireval
try{
	$file = fopen("https://api.tradehill.com/APIv1/USD/Ticker", "rb");
	$tradedata = fread($file, 8192);
	fclose($file);

	//get trade hill json data
		$jsonTradedata = json_decode($tradedata, true);
		
	//calculate average with the provided data (Buy, sell, last sale)
		$tradeHillWorth = round((($jsonTradedata[ticker][last]+$jsonTradedata[ticker][sell]+$jsonTradedata[ticker][buy])/3), 2);
	
	mysql_query("UPDATE `websiteSettings` SET `tradeHillWorth` = '".$tradeHillWorth."'");
}catch (Exception $e) {
	echo "Failed to get TradeHill bitcoioin worth<br/>".$e;
}



if(!empty($tradeHillWorth)){
	//Check if this is the same rade data as the last inputted row of `stats_bitocinConversionHistory`
		$lastConversion = mysql_query("SELECT `tradehill` FROM `stats_bitcoinConversionHistory` ORDER BY `timestamp` DESC LIMIT 0,1");
		$lastConversion = mysql_fetch_object($lastConversion);
		$lastConversionTradehill = $lastConversion->tradehill;
		
		//Only insert new data if this is new data ;)
			if($lastConversionTradehill != $tradeHillWorth){
				mysql_query("INSERT INTO `stats_bitcoinConversionHistory` (`tradehill`, `timestamp`) VALUES('$tradeHillWorth', '".time()."')")or die(mysql_error());
			}
}


	/*
//Retireve JSON data from trade hill update it to database for quick retireval
try{
	$file = fopen("https://mtgox.com/code/data/ticker.php", "r");
	$tradedata = fread($file, filesize($file));
	fclose($file);

	//get trade hill json data
		$jsonTradedata = json_decode($tradedata, true);
		
	//calculate average with the provided data (Buy, sell, last sale)
		$tradeHillWorth = round((($jsonTradedata[ticker][last]+$jsonTradedata[ticker][sell]+$jsonTradedata[ticker][buy])/3), 2);
	
	mysql_query("UPDATE `websiteSettings` SET `tradeHillWorth` = '".$tradeHillWorth."'");
}catch (Exception $e) {
	echo "Failed to get TradeHill bitcoioin worth<br/>".$e;
}
*/



              
	$url = 'https://mtgox.com/code/data/ticker.php';

        //open connection
        $ch = curl_init();
                        
        //set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        
        //add POST fields
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        //MUST BE REMOVED BEFORE PRODUCTION (USE for SSL)
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Xenland');
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        
        //execute CURL connection
        $returnData = curl_exec($ch);
                
        //$code = $this->returnCode($returnData);        
        
        if( $returnData === false)
        {
            die('<br />Connection error:' . curl_error($ch));
        }
        else
        {
            //Log successful CURL connection
        }
        
        //close CURL connection
        curl_close($ch);
        
        echo '<pre>';
        print_r(json_decode($returnData, true));
        echo '</pre>';   


?>



