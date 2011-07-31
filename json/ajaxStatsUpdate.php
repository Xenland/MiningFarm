<?php
//This page will output the latest updates for stats

$act = $_GET["act"];
$timestamp = $_GET["timestamp"];
if($act == "overviewOfPool"){
	//Last time stamp
	$lastTimestamprequested = $timestamp; //This will give a thresh-hold to look through all the stats that are past this timestamp so we can update properly
	
	//Get
}

// Set the JSON header
header("Content-type: text/json");

// The x value is the current JavaScript time, which is the Unix time multiplied by 1000.
$x = time() * 1000;
// The y value is a random number
$y = rand(0, 100);

// Create a PHP array and echo it as JSON
$ret = array($x, $y);
echo json_encode($ret);
?>