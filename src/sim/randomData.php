<?php
	require '../lib/DBAccess.php';		
	
	$dbh = new DBAccess();

	$namelist = array(
	"Manfred Gerstner",
	"Dummy Doedel",
	"Jan Steinig",
	"Michael Schnellhammer",
	"Tobias Bauer",
	"Christian Serafin",
	"Lukas Pusch",
	"Raffael Lustig",
	"Christoph Messerschmidt",
	"Simon Obermeir",
	"Florian Pitzl",
	"Roland Freymann",
	"Maximilian Ziegler",
	"Jakob Schoettl",
	"Michael Kaeser",
	"Emanuel Harter",
	"Tobias Meyer",
	"Marc Herfurt",
	"Christoph Alt",
	"Mary-Lee Steffan",
	"Thomas Brazdrum",
	"Ilias Sarafides",
	"Michael Bjeski");
	
	$time = time();
	$beginOffset = rand(0, 100000);
	$endOffset = rand(86400, 400000);
	
	$dateBegin = date("Y-m-d H:i",($time - $beginOffset));
	$dateEnd = date("Y-m-d H:i",($time + $endOffset));
	
	$result = $dbh->query("SELECT LockId FROM `lock` ORDER BY Location");
	$Lockids = $result->fetchAll();
	$randLock = rand(0, count($Lockids) - 1);

	echo $namelist[rand(0,count($namelist)-1)]."|".$dateBegin."|".$dateEnd."|".$Lockids[$randLock]["LockId"];
?>