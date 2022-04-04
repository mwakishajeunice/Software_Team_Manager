<?php
session_start();
ini_set('display_errors', 1);

include_once './mail.php';

Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		extract($_POST);
			$qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where email = '".$email."' and password = '".md5($password)."'  ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 2;
		}
	}
	function forgot_pwd(){
		$user_email = $_POST['email'];
		$user_email;
		$qry = $this->db->query("SELECT * FROM users where email = '".$user_email."' ");
		if($qry->num_rows > 0){
			$user = mysqli_fetch_row($qry);
			// print_r($user);
			//generate random password
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$pass_length = 8;
     		$pass = substr(str_shuffle($chars),0, $pass_length);
			//update user password
			$update = $this->db->query("UPDATE users SET password = md5('" .$pass . "') WHERE email = '".$user_email."' ");
			if($update){
				//send email to user with a password
				$email = new Email();
				$email->to = $user_email;
				$email->subject = "Forgot Password Request";
				$email->message = "<p> Hello " .$user_email. ", </br>Your Password is: " . $pass ."</p>";	
				$email->message .= "<p>Change password after login</p>";
				$result = $email->sendEmail();
				
				if($result)
					return 1;
				return 2;
					
			}else{
				return 3;
			}
		} else {
			return 4;
		}
	}
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function login2(){
		extract($_POST);
			$qry = $this->db->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as name FROM students where student_code = '".$student_code."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['rs_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function save_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(!empty($password)){
					$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save && empty($id)){
			$role = $type == 1 ? 'Admin' : ($type == 2 ? 'Project Manager' : 'Developer');
			//send email to user
			$_email = new Email();
			$_email->to = $email;
			$_email->subject = "Your have been registered";
			$_email->message = "<p> Hello " .$email. ", </br>Your $role account has been created <br />" 
				. "Login Details: <br /> Email: " .$email . " <br />Password: " .$password ."</p>";	
			$_email->message .= "<p>Change password after login</p>";
			
			$result = $_email->sendEmail();
			
			if($result){
				return 1;
			}
			return 3;
		}
		return 4;
	}

	function signup(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass')) && !is_numeric($k)){
				if($k =='password'){
					if(empty($v))
						continue;
					$v = md5($v);

				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");

		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			if(empty($id))
				$id = $this->db->insert_id;
			foreach ($_POST as $key => $value) {
				if(!in_array($key, array('id','cpass','password')) && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
					$_SESSION['login_id'] = $id;
				if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function update_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','table','password')) && !is_numeric($k)){
				
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(!empty($password))
			$data .= " ,password=md5('$password') ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			foreach ($_POST as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function save_system_settings(){
		extract($_POST);
		$data = '';
		foreach($_POST as $k => $v){
			if(!is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if($_FILES['cover']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['cover']['name'];
			$move = move_uploaded_file($_FILES['cover']['tmp_name'],'../assets/uploads/'. $fname);
			$data .= ", cover_img = '$fname' ";

		}
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set $data where id =".$chk->fetch_array()['id']);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set $data");
		}
		if($save){
			foreach($_POST as $k => $v){
				if(!is_numeric($k)){
					$_SESSION['system'][$k] = $v;
				}
			}
			if($_FILES['cover']['tmp_name'] != ''){
				$_SESSION['system']['cover_img'] = $fname;
			}
			return 1;
		}
	}
	function save_image(){
		extract($_FILES['file']);
		if(!empty($tmp_name)){
			$fname = strtotime(date("Y-m-d H:i"))."_".(str_replace(" ","-",$name));
			$move = move_uploaded_file($tmp_name,'assets/uploads/'. $fname);
			$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
			$hostName = $_SERVER['HTTP_HOST'];
			$path =explode('/',$_SERVER['PHP_SELF']);
			$currentPath = '/'.$path[1]; 
			if($move){
				return $protocol.'://'.$hostName.$currentPath.'/assets/uploads/'.$fname;
			}
		}
	}
	function save_project(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(isset($user_ids)){
			$data .= ", user_ids='".implode(',',$user_ids)."' ";
		}
		// echo $data;exit;
		if(empty($id)){
			$save = $this->db->query("INSERT INTO project_list set $data");
		}else{
			$save = $this->db->query("UPDATE project_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_project(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM project_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_task(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO task_list set $data");
		}else{
			$save = $this->db->query("UPDATE task_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_task(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_progress(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'comment')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$dur = abs(strtotime("2020-01-01 ".$end_time)) - abs(strtotime("2020-01-01 ".$start_time));
		$dur = $dur / (60 * 60);
		$data .= ", time_rendered='$dur' ";
		// echo "INSERT INTO user_productivity set $data"; exit;
		if(empty($id)){
			$data .= ", user_id={$_SESSION['login_id']} ";
			
			$save = $this->db->query("INSERT INTO user_productivity set $data");
		}else{
			$save = $this->db->query("UPDATE user_productivity set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_progress(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM user_productivity where id = $id");
		if($delete){
			return 1;
		}
	}
	function get_report(){
		extract($_POST);
		$data = array();
		$get = $this->db->query("SELECT t.*,p.name as ticket_for FROM ticket_list t inner join pricing p on p.id = t.pricing_id where date(t.date_created) between '$date_from' and '$date_to' order by unix_timestamp(t.date_created) desc ");
		while($row= $get->fetch_assoc()){
			$row['date_created'] = date("M d, Y",strtotime($row['date_created']));
			$row['name'] = ucwords($row['name']);
			$row['adult_price'] = number_format($row['adult_price'],2);
			$row['child_price'] = number_format($row['child_price'],2);
			$row['amount'] = number_format($row['amount'],2);
			$data[]=$row;
		}
		return json_encode($data);

	}

	function support(){
		extract($_POST);
		$data = "";

		$sub = htmlentities(str_replace("'","&#x2019;",$subject));
		$msg = htmlentities(str_replace("'","&#x2019;",$message));

		$save = $this->db->query("INSERT INTO support(user_id, subject, message) VALUES($user_id, '$sub', '$msg')");
		
		if($save){
			$name = $_SESSION['login_name'];
			$email= $_SESSION['login_email'];
			$role = $_SESSION['login_type'] == 1 ? 'Admin' : ($_SESSION['login_type'] == 2 ? 'Project Manager' : 'Developer');
			
			$_email = new Email();
			$_email->to = 'jeunice.shakimwa@gmail.com';
			$_email->subject = "You have a new message from " . $_SESSION['login_name'];
			$_email->message ="
						<p><strong>User: </strong>$name</p>
						<p><strong>Email: </strong> $email</p>
						<p><strong>Role: </strong> $role</p>
						<p>Dear Admin,</p>
						<div>$message</div>";	
			
			$result = $_email->sendEmail();
			
			if($result){
				return 1;
			}
			return 2;
		}
		return 3;

	}

	function support_reply(){
		extract($_POST);
		$msg = htmlentities(str_replace("'","&#x2019;",$reply));

		$save = $this->db->query("UPDATE support SET reply='$msg' status=1 where id =$support_id");
		
		if($save){
			$_email = new Email();
			$_email->to = $support_email;
			$_email->subject = "Your message has been replied by " . $_SESSION['login_name'];
			$_email->message ="
						<p>Dear $support_name,</p>
						<div>$reply</div>";	
			
			$result = $_email->sendEmail();
			
			if($result){
				return 1;
			}
			return 2;
		}
		return 3;

	}
	function get_single_support_request(){
		extract($_POST);
		$request = null;
		$this->db->query("UPDATE support set status=1 where id = $id");

		$qry = $this->db->query("SELECT support.*, concat(firstname, ' ' , lastname) as name, type, email FROM support LEFT JOIN users ON support.user_id = users.id WHERE support.id = $id LIMIT 1");
		if($qry->num_rows > 0){
			

			while($row = $qry->fetch_assoc()){
			$role = $row['type'] == 1 ? 'Admin' : ($row['type'] == 2 ? 'Project Manager' : 'Developer');
			  $item = array(
				"id" => $row['id'],
				"subject" => $row['subject'],
				"message" => $row['message'],
				"reply" => $row['reply'],
				"date_created" => $row['date_created'],
				"status" => $row['status'],
				"user_id" => $row['user_id'],
				"user_name" => $row['name'],
				"user_email" => $row['email'],
				"user_role" => $role
			  );
			  $request = $item;
			}
		}
		return json_encode($request);
	}
	function get_support_request_count(){
		$data = 0;
		$get = $this->db->query("SELECT COUNT(id) as requests FROM support where datediff(now(), date_created) < 7 and status = 0 ");
		while($row= $get->fetch_assoc()){
			$data = (int)$row['requests'];
		}
		return json_encode($data);
	}
}

