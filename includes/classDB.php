<?php
class DB {

	 public function __construct() {
		$mysqli=new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
        
        if ($mysqli->connect_errno) {
            echo ('Could not connect to mysql'.$mysqli->connect_error);
        }
		$this->link=$mysqli;
		$this->lastID="NULL";
    }
    
    public function close() {
        if ($this->link != null) {
           $this->link->close();
            $this->link = null;
        }
    }
	# runs mysqli query #query
    private function query($sql) {
		global $settings;
		$mysqli=$this->link;
        $result=mysqli_query($mysqli,$sql);
        if ($mysqli->error) {
			if($settings['Debug']){
				echo "QUERY: '$sql'\n\n<br><br>" . $mysqli->error."<br><br>\n\nBacktrace:\n<br>";
				debug_print_backtrace();
				return false;
			}else
				return "Mysql Error";
        }
        
        if (is_bool($result)) {
            return $result;
        }
        
        if ($result->num_rows===NULL) {
            return null;
        }
        return $result;
    }
	# addes a new ticket to characterID $id  with refID $refID
	# accepts $id as characterID of ticket reciever
	# accepts $refID as reference id for wallet.
	public function insertTicket($id,$refID){
		$sql="INSERT INTO ".TICKET_TABLE." (charID,refID) VALUES (".$this->link->real_escape_string($id).",".$this->link->real_escape_string($refID).")";
		return $this->query($sql);
	}
	# removed ticket with ticketID $id
	public function removeTicket($id){
		$sql="DELETE FROM ".TICKET_TABLE." WHERE ticketID=".$this->link->real_escape_string($id);
		return $this->query($sql);
	}
	# gets all tickets for characterID $id
	# accepted $id as a characterID to search
	# if $id is 0 returns all tickets
	public function getTickets($id){
		global $settings;
		if($settings['finished'])
			$old="old_";
		else
			$old=null;
		if($id==0)
			$sql="SELECT * FROM {$old}".TICKET_TABLE;
		else
			$sql="SELECT * FROM {$old}".TICKET_TABLE." WHERE charID=".$this->link->real_escape_string($id);
		$result=$this->query($sql);
		$tickets=array();
		if($result)
			$i=1;
			while($row=$result->fetch_assoc()){
				$tickets[$i]['id']=$row['ticketID'];
				$tickets[$i]['cID']=$row['charID'];
				$tickets[$i]['refID']=$row['refID'];
				$i++;
			}
		return $tickets;
	}
	#returns all settings as $settings[name]=value
	public function getSettings(){
		$sql="SELECT * FROM ".SETTING_TABLE;
		$result=$this->query($sql);
		if($result)
			while($row=$result->fetch_assoc())
				$settings[$row['Setting']]=$row['Value'];
		return $settings;
	}
	# changes $setting to $value in settings table
	# accepts $setting as name of setting
	# accepts $value as value to set $setting to
	public function changeSetting($setting,$value){
		$sql="UPDATE ".SETTING_TABLE." SET Value=\"".$this->link->real_escape_string($value)."\" WHERE Setting=\"".$this->link->real_escape_string($setting)."\"";
		$result=$this->query($sql);
		if(!$result){
			$sql="INSERT INTO ".SETTING_TABLE." VALUES('".$this->link->real_escape_string($setting)."','".$this->link->real_escape_string($value)."')";
			$result=$this->query($sql);
		}		
		if($result)
			return $result;
	}
	# removed a user with given characterID
	# accepts $cID as characterID
	public function removeUser($cID){
		$sql="DELETE FROM ".LOGIN_TABLE." WHERE charID=\"".$this->link->real_escape_string($cID)."\"";
		$result=$this->query($sql);
		if($result)
			return $result;
	}
	# registers a user with  given pass username and character id
	# only registers if they are on accepted list
	# accepts $pass  as password plaintext
	# accepts $user  as username plaintext
	# accepts $cID as characterID
	public function regUser($pass,$user,$cID){
		global $settings;
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE cID='".$this->link->real_escape_string($cID)."'";
		$result=$this->query($sql);
		if($result)
			return "Character Already Registered";
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE username='".$this->link->real_escape_string($user)."'";
		$result=$this->query($sql);
		if($result)
			return "username Already In Use";
		$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
		if(in_array($cID,$allowedUsers)){
			$sql="INSERT INTO ".LOGIN_TABLE." VALUE ('".$this->link->real_escape_string($user)."','".$this->link->real_escape_string(md5($pass))."','".$this->link->real_escape_string($cID)."')";
			$result=$this->query($sql);
			if($result)
				return "Registered Successfully";
			else
				return "Error Registering";
				
		}
		return $string;
	}
	# checks if user exist and has correct password
	# if so fetch name and login
	# accepts $pass as password plaintext
	# accepts $user as username plaintext
	public function loginUser($pass,$user){
		global $settings;
		echo md5($pass);
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE username='".$this->link->real_escape_string($user)."' AND password='".$this->link->real_escape_string(md5($pass))."'";
		$result=$this->query($sql);
		if($result->num_rows){
			$char=$result->fetch_assoc();
			$cID=new charID($char['charID']);
			$cID->parse();
			$_SESSION['LOGGED_IN']=true;
			$_SESSION['cID']=$char['charID'];
			$_SESSION['cName']=$cID->name;
			return true;
		}else
			return false;
	}
	#checks if a user is still in the database, if not log out
	public function checkUser(){
		global $settings;
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE charID='".$this->link->real_escape_string($_SESSION['cID'])."'";
		$result=$this->query($sql);
		if($result->num_rows){
			return true;
		}else{
			session_destroy();
			header("location : login.php");
		}
	}
	private function generateWinner(){
		$rand=mt_rand(1,count($this->getTickets(0)));
		$sql="SELECT * FROM ".TICKET_TABLE." WHERE ticketID=".$rand;
		$result=$this->query($sql);
		if($result->num_rows){
			$this->changeSetting("winner",$rand);
			$this->changeSetting("finished",true);
		}else
			$this->generateWinner();
	}
	#ends a lottery by backing up tickets, cleaning tickets table, and picking a winning number
	public function endLottery(){
		$this->generateWinner();
		$sql="SELECT 1 FROM old_".TICKET_TABLE." LIMIT 0";
		$result=$this->query($sql);
		if($result->field_count)
			$sql="TRUNCATE TABLE old_".TICKET_TABLE;
		else
			$sql="CREATE TABLE old_".TICKET_TABLE." LIKE ".TICKET_TABLE;
		$result=$this->query($sql);
		if($result){
			$sql="INSERT INTO old_".TICKET_TABLE." SELECT * FROM ".TICKET_TABLE;
			$result=$this->query($sql);
		}
		$sql="TRUNCATE TABLE ".TICKET_TABLE;
		$result=$this->query($sql);
		if($result)
			return true;
		else
			return false;
	
	}
}
?>