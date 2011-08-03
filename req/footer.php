							<div class="cleared"></div>
							<div class="art-footer">
								<div class="art-footer-t"></div>
								<div class="art-footer-l"></div>
								<div class="art-footer-b"></div>
								<div class="art-footer-r"></div>
								<div class="art-footer-body"><a href="./#" class="art-rss-tag-icon" title="RSS"></a>
									<div class="art-footer-text">
										<p class="footerText"><a href="/softwaredonations.php">1Fc2ScswXAHPUgj3qzmbRmwWJSLL2yv8Q</a></p><br/>							
										<p class="footerText">Programming/Development: Shane B.<br/> Design/SEO: Brian S.</p><br/>
										<p>Running <b>Mining Farm</b> an Open Source Front-End to Pushpool</p>
										<p class="footerText">Copyright <?php echo date("Y");?> © <a href="http://miningfarm.com" target="_BLANK">Mining Farm.com</a></p>
									</div>
									<div class="cleared"></div>
								</div>
							</div>
							<div class="cleared"></div>
						</div>
					</div>
					<div class="cleared"></div>
				</div>
			</div>
		</div><br/><br/><br/>
		<div id="footerInfo">
			<?php
			try{
				//Open a bitcoind connection
					$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
			}catch(Exception $e){
				echo "[WALLET FAIL]";
			}
				
			//Show some quick stats
				
				//Get total workers working
					$fifteenMinutesAgo = time();
					$fifteenMinutesAgo -= 60*15;
					$totalWorkersQ = mysql_query("SELECT DISTINCT `username` FROM `stats_userMHashHistory` WHERE `timestamp` >= $fifteenMinutesAgo AND `mhashes` > 0");
					$totalWorkers = mysql_num_rows($totalWorkersQ);
			?>		
			<?php echo $totalWorkers;?> Workers Online | Difficulty of <i><?php echo round($bitcoinController->getDifficulty());?></i> | Block# <?php echo $bitcoinController->getblocknumber();?>
		</div>
	</body>
</html>