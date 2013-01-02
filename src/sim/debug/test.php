<?php

require_once '../../domain/System.php';
session_start();
$sys = System::getInstance();
$as = $sys->getAccessSystem();
if (! $as instanceof PseudoAccessSystem) echo 'kein pseudo access system!';

$arr = array();
$arr[] = 'a';
$arr[] = 'b';
if (!array_key_exists(0, $arr)) echo 'arr key dont exists!';
if (!array_key_exists(1, $arr)) echo 'arr key dont exists!';

//var_dump($_SESSION['accessEntryList']);

var_dump($as->getAccessEntry(0));
var_dump($as->getAccessEntry(1));
var_dump($as->getAccessEntry(2));
var_dump($as->getAccessEntry(3));

