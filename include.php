<?
include(".././Krumo/class.krumo.php");
session_start();

date_default_timezone_set ("UTC");
//define needed information
define("PREFIX","");
define("TICKET_TABLE",PREFIX."");
define("LOGIN_TABLE",PREFIX."");
define("SETTING_TABLE",PREFIX."");
define("BALANCE_TABLE",PREFIX."");
define("MYSQL_SERVER","");
define("MYSQL_USER","");
define("MYSQL_PASS","");
define("MYSQL_DB","");
define("DEBUG",false);
define("ROOT",dirname(__FILE__));
set_include_path(ROOT."/includes/");

//include basic classes
include("classDB.php");
include("classOutput.php");
$out= new output();

//include api classes
include("api/classApi.php");
include("api/classApiInfo.php");
include("api/classWalletJournal.php");
include("api/classCharacterInfo.php");
include("api/classCharacterName.php");
include("api/classCharacterID.php");

//set Settings
$db=new DB();
$settings=$db->getSettings();
define("REFID",@$settings['lastID']);
define("apiK",@$settings['apiK']);
define("vCode",@$settings['vCode']);
define("characterID",@$settings['characterID']);

//makesure you are still in the database
if(isset($_SESSION['LOGGED_IN'])&&$_SESSION['LOGGED_IN'])
	$db->checkUser();
	
$lottoNum=@$settings['lottoNum'];


?>