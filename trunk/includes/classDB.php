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
			if(@$settings['Debug']){
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
		global $settings,$nextTicket;
		$sql="INSERT INTO ".TICKET_TABLE."  VALUES ('".$nextTicket."',".$this->link->real_escape_string($id).",".$this->link->real_escape_string($refID).",'".$this->link->real_escape_string($settings['lottoName'])."','".$this->link->real_escape_string($settings['lottoNum'])."')";
		$nextTicket++;
		return $this->query($sql);
	}
	# removed ticket with ticketID $id
	public function removeTicket($id){
		global $settings;
		$sql="DELETE FROM ".TICKET_TABLE." WHERE ticketID=".$this->link->real_escape_string($id)." AND lottoNum='".$this->link->real_escape_string($settings['lottoNum'])."'";
		return $this->query($sql);
	}
	# gets all tickets for characterID $id
	# accepted $id as a characterID to search
	# if $id is 0 returns all tickets
	public function getTickets($id,$all=false){
		global $lottoNum;
		if($all){
			if($id==0)
				$sql="SELECT * FROM ".TICKET_TABLE;
			else
				$sql="SELECT * FROM ".TICKET_TABLE." WHERE charID='".$this->link->real_escape_string($id)."'";
		}else{
			if($id==0)
				$sql="SELECT * FROM ".TICKET_TABLE." WHERE lottoNum='".$this->link->real_escape_string($lottoNum)."'";
			else
				$sql="SELECT * FROM ".TICKET_TABLE." WHERE charID='".$this->link->real_escape_string($id)."' AND lottoNum='".$this->link->real_escape_string($lottoNum)."'";
		}
		$result=$this->query($sql);
		$tickets=array();
		if($result){
			$i=1;
			while($row=$result->fetch_assoc()){
				$tickets[$i]['id']=$row['ticketID'];
				$tickets[$i]['cID']=$row['charID'];
				$tickets[$i]['refID']=$row['refID'];
				$tickets[$i]['lottoName']=$row['lottoName'];
				$tickets[$i]['lottoNum']=$row['lottoNum'];
				$i++;
			}
		}
		return $tickets;
	}
	public function getTicketById($id){
		global $lottoNum;
		$sql="SELECT * FROM ".TICKET_TABLE." WHERE ticketID='".$this->link->real_escape_string($id)."' AND lottoNum='".$this->link->real_escape_string($lottoNum)."'";
		$result=$this->query($sql);
		$tickets=array();
		if($result){
			$row=$result->fetch_assoc();
			$ticket['id']=$row['ticketID'];
			$ticket['cID']=$row['charID'];
			$ticket['refID']=$row['refID'];
			$ticket['lottoName']=$row['lottoName'];
			$ticket['lottoNum']=$row['lottoNum'];
		}
		return $ticket;
	}
	#returns all settings as $settings[name]=value
	public function getSettings(){
		$sql="SELECT * FROM ".SETTING_TABLE;
		$result=$this->query($sql);
		$settings=array();
		if($result)
			while($row=$result->fetch_assoc())
				$settings[$row['Setting']]=$row['Value'];
		return $settings;
	}
	public function getBalance($id,$all=false){
		global $lottoNum;
		if($all)
			if($id==0)
				$sql="SELECT * FROM ".BALANCE_TABLE;
			else
				$sql="SELECT * FROM ".BALANCE_TABLE." WHERE charID='".$this->link->real_escape_string($id)."'";
		else
			if($id==0)
				$sql="SELECT * FROM ".BALANCE_TABLE." WHERE lottoNum='".$this->link->real_escape_string($lottoNum)."'";
			else
				$sql="SELECT * FROM ".BALANCE_TABLE." WHERE charID='".$this->link->real_escape_string($id)."' AND lottoNum='".$this->link->real_escape_string($lottoNum)."'";
		$result=$this->query($sql);
		if($result){
			$i=1;
			while($row=$result->fetch_assoc()){
				$balance[$i]=$row['balance'];
				$i++;
			}
			if($i==2){
				$balance=$balance[1];
			}
		}else{
			$balance=0;
		}
		return $balance;
	}
	public function updateBalance($id,$balance){
		global $settings;
		$sql="UPDATE ".BALANCE_TABLE." SET balance=\"".$this->link->real_escape_string($balance)."\" WHERE charID=\"".$this->link->real_escape_string($id)."\" AND lottoNum='".$this->link->real_escape_string($settings['lottoNum'])."'";
		$result=$this->query($sql);
		if(!$this->link->affected_rows){
			$sql="INSERT INTO ".BALANCE_TABLE." VALUES('".$this->link->real_escape_string($balance)."','".$this->link->real_escape_string($id)."','".$this->link->real_escape_string($settings['lottoName'])."','".$this->link->real_escape_string($settings['lottoNum'])."')";
			$result=$this->query($sql);
		}		
		
		if($this->link->affected_rows){
			return true;
		}else{
			return false;
		}
	}
	# changes $setting to $value in settings table
	# accepts $setting as name of setting
	# accepts $value as value to set $setting to
	public function changeSetting($setting,$value){
		global $settings;
		if(@$settings[$setting]!=$value){
			$sql="UPDATE ".SETTING_TABLE." SET Value=\"".$this->link->real_escape_string($value)."\" WHERE Setting=\"".$this->link->real_escape_string($setting)."\"";
			$result=$this->query($sql);
			if(!$this->link->affected_rows){
				$sql="INSERT INTO ".SETTING_TABLE." VALUES('".$this->link->real_escape_string($setting)."','".$this->link->real_escape_string($value)."')";
				$result=$this->query($sql);
			}		
			if($result)
				return $result;
		}else
			return true;
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
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE charID='".$this->link->real_escape_string($cID)."'";
		$result=$this->query($sql);
		if($result->num_rows)
			return "Character Already Registered";
		$sql="SELECT * FROM ".LOGIN_TABLE." WHERE username='".$this->link->real_escape_string($user)."'";
		$result=$this->query($sql);
		if($result->num_rows)
			return "username Already In Use";
		$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
		if(in_array($cID,$allowedUsers)){
			$sql="INSERT INTO ".LOGIN_TABLE." VALUE ('".$this->link->real_escape_string($user)."','".$this->link->real_escape_string(md5($pass))."','".$this->link->real_escape_string($cID)."')";
			$result=$this->query($sql);
			if($result)
				return true;
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
		global $settings;
		$rand=mt_rand(1,count($this->getTickets(0)));
		$sql="SELECT * FROM ".TICKET_TABLE." WHERE ticketID=".$rand." AND lottoNum='".$this->link->real_escape_string($settings['lottoNum'])."'";
		$result=$this->query($sql);
		if($result->num_rows){
			$this->changeSetting("winner",$rand);
			$this->changeSetting("finished",true);
			$settings=$this->getSettings();
		}else
			$this->generateWinner();
	}
	#ends a lottery by backing up tickets, cleaning tickets table, and picking a winning number
	public function endLottery(){
		global $settings,$nextTicket;
		if(count($this->getTickets(0))){
			$this->generateWinner();
			$nextTicket=strtotime($settings['lottoEnd'])*-1;
			$result=$this->insertTicket(-1,$settings['winner']);
			if($result)
				return true;
			else
				return false;
		}else{
			$this->changeSetting("finished",true);
			return true;
		}
				
	
	}
}
////////6202678886
?>