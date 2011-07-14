<?php

//Retireve bitcoin worth history 
	$bitcoinHistory = mysql_query("SELECT `tradehill`, `timestamp` FROM `stats_bitcoinConversionHistory` ORDER BY `timestamp` ASC LIMIT 0,20");
	
	$tradeHillArray = "";
	$tradeHillArrayTime = "";
	$i = 0;
	while($bitcoin = mysql_fetch_array($bitcoinHistory)){
		
		//Add delementir
			if($i > 0){
				$tradeHillArray .= ",";
				$tradeHillArrayTime .= ",";
			}
			
		//add next price conversion
			$tradeHillArray .= $bitcoin["tradehill"];
			
		//add timestamp 
			$tradeHillArrayTime .= "'".date("g:s", $bitcoin["timestamp"])."'";
			
		//Add I
			$i++;
		
			
	}
?>
<script type="text/javascript">
	var chart2; // globally available
	$(document).ready(function() {
		chart2 = new Highcharts.Chart({
			chart: {
				renderTo: 'graph2',
				defaultSeriesType: 'spline',
				width:750,
				height:250
			},
			title: {
				text: 'Bitcoin Value in fiat - <?php echo date("e", time());?> timezone'
			},
			xAxis: {
				categories: [<?php echo $tradeHillArrayTime;?>]
			},
			yAxis: {
				title: {
					text: 'Dollars Per Bitcoin'
				}
			},
			series: [{
				name: 'Trade Hill(USD)',
				data: [<?php echo $tradeHillArray; ?>]
				}]
		});
	});
</script>
<div id="graph2" align="center">
</div><br/><br/>