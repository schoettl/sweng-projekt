<?php
	session_start();
	
	$action = $_GET["action"];
	if(empty($action))
		$action = "form";
		
	switch($action) {
		case "setLink":
			echo setLink();
			break;
		case "getLink":
			echo getLink();
			break;
	}
	
	
	function setLink() {
		$link = $_GET["link"];
		
		if(!empty($link)) {
			$_SESSION["nextLink"] = $link;
			return "true";
		}
		else
			return "false";
	}
	
	function getLink() {
		if(!empty($_SESSION["nextLink"])) {
			$ret = "http://".$_SERVER['HTTP_HOST']."/".$_SESSION["nextLink"];
			unset($_SESSION["nextLink"]);
			return $ret;
		}
		else
			return "false";
			
		//echo "<pre>".print_r($_SESSION,true)."<pre>";
	}
?>