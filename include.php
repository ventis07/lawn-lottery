<?
session_start();

//define needed information
define("PREFIX","");
define("TICKET_TABLE",PREFIX."tickets");
define("LOGIN_TABLE",PREFIX."users");
define("SETTING_TABLE",PREFIX."settings");
define("MYSQL_SERVER","");
define("MYSQL_USER","");
define("MYSQL_PASS","");
define("MYSQL_DB","lottery");
define("ROOT","./");
set_include_path("includes/");

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
define("REFID",$settings['lastID']);
define("apiK",$settings['apiK']);
define("vCode",$settings['vCode']);
define("characterID",$settings['characterID']);
define("DEBUG",$settings['debug']);

//makesure you are still in the database
if(isset($_SESSION['LOGGED_IN'])&&$_SESSION['LOGGED_IN'])
	$db->checkUser();



?>