<?php
	require '../../domain/AccessEntry.php';
	//require '../../domain/AccessListItem.php';
	//require '../../domain/AccessSystem.php';
	//require '../../domain/ActiveKeyConfig.php';
	//require '../../domain/ActiveKeySynchronizer.php';
	// require '../../domain/Key.php';
	// require '../../domain/KeyProgrammer.php';
	// require '../../domain/Lock.php';
	// require '../../domain/LockConfig.php';
	// require '../../domain/LockProgrammer.php';
	// require '../../domain/LockProgrammerConfig.php';
	// require '../../domain/LockProgrammerSynchronizer.php';
	// require '../../domain/PassiveKey.php';
	// require '../../domain/PseudoAccessSystem.php';
	require '../../domain/System.php';

	echo "<h2>check session data</h2>";
	session_start();
	echo "<pre>".print_r($_SESSION, true)."</pre>";
?>