<?
include("includes/api/classAPI.php");
include("includes/api/classWalletJournal.php");
define("ROOT","./");
$wallet= new walletJournal(1192528,"LwVIJ5k1eBiyMBJatz5HesO44EZBZNKXkLRu8ykhQIl8SbBkdLVTdLA0CktDs9nO",553478565);
$wallet->parse();
echo "<pre>";
print_r($wallet->transactions);
echo "</pre>";
?>