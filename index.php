<?php
include 'db.php';
require('../events/MCAPI.class.php');
require 'vendor/autoload.php';
require_once('../events/includes/PHPMailer-master/PHPMailerAutoload.php');
date_default_timezone_set('America/New_York');
$app = new Slim\App();
   
function checkapilogin($Auth){ 
	if($Auth == 'A-0987654321')
	{
		 return true;
	} 
	else
	{
		return false;
	} 
}
	
function checkmemberlogin($Auth){  
	if($Auth == 'M-0123456789')
	{
		 return true;
	} 
	else
	{
		return false;
	} 
}  

function checkdateformat($datea)
{ 
	$date=date_create($datea);
	$date_format = 'M d, Y'; 
	$input = trim($datea);
	$time = strtotime($input);
	$is_valid = strtotime(date($date_format, $time)) == strtotime($input);  
	$val = 'false';
	if($is_valid)
	{ 
		$val = 'true';
	}
	
	return $val;
}

function checkattendanceformat($datea)
{  
	$date_format = 'Y-m-d'; 
	  $input = trim($datea);
	$time = strtotime($input);  
	  date($date_format, $time); 
	$is_valid = date($date_format, $time) == $input; 
	$val = 'false';
	if($is_valid)
	{
		$val = 'true';
	} 
	return $val;
}   


// gnereate random string function
function generateRandomString($length = 20) { 
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    } 
	return $randomString; }
 
$app->get('/', function ($request, $response, $args) {
    $response->write("Welcome to UWUA");
    return $response;});
 
// Token
$app->post('/adminLogin', function ($request, $response, $args) {  
        $parsedBody = $request->getParsedBody(); // print_r($parsedBody);
		$Auth = $request->getHeaderLine('Default-Admin-Token'); 
		if($Auth){
			$check = checkapilogin($Auth);
			if($check)
			{
				if(!empty($parsedBody)){
					$email = $parsedBody['email'];
					$password = md5($parsedBody['password']); 
					$sql = "SELECT id,access_key FROM `users` WHERE email = '$email' AND password = '$password'";
					$db = getDB();
					$stmt = $db->query($sql);
					$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
					$userID = $users[0]->id;
					if($userID)
					{ 
						$random = generateRandomString();
						$random = $userID.'-'.$random;
						if($users[0]->access_key == '')
						{
							$insertsql = "UPDATE users SET access_key= '$random' WHERE id=$userID";
							$stmt = $db->query($insertsql); 
						}  
						$sqla = "SELECT * FROM `users` WHERE id=$userID";
					    $user = $db->query($sqla);
					    $users = $user->fetchAll(PDO::FETCH_OBJ);  
						$db = null;  
						unset($users[0]->password); 
						$report_to = $users[0]->report_to;
						unset($users[0]->report_to);
						$users[0]->report_to = (int)str_replace(' ', '', $report_to);
						$response = $response->withJson($users[0], 200); 
					}
					else
					{
						$data = array('error_code'=>'E001','text' => "Enter Correct Username And Password.");
			            $response = $response->withJson($data, 401);  
					} 
				}
				else
				{
					$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			        $response = $response->withJson($data, 401);  
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Default Admin Token.");
				$response = $response->withJson($data, 401); 
			}
			$headers = $request->getHeaders();    
		}
		else
		{
			$data = array('error_code'=>'E008','text' => "Please Enter Token");
		    $response = $response->withJson($data, 401); 
		}
		return $response; });
	 
function checkAuthKey($Auth){ 
  $params = $Auth;
    $AccessKey =   explode('-',$params['AccessKey']);
	$Accss = $params['AccessKey']; 
	$sql = "SELECT id FROM `users` WHERE access_key ='$params' AND role != '3' ";  
	$db = getDB();
	$stmt = $db->query($sql);
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(empty($users))
	{  
	   return false;
	}
	else
	{
		return true;
	} 
}

function checkAuthKey1($Auth){ 
  $params = $Auth;
    $AccessKey =   explode('-',$params['AccessKey']);
	$Accss = $params['AccessKey']; 
	$sql = "SELECT id FROM `users` WHERE access_key ='$params' ";  
	$db = getDB();
	$stmt = $db->query($sql);
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(empty($users))
	{  
	   return false;
	}
	else
	{
		return true;
	} 
} 
    
// event location By ID
$app->get('/locationById[/{location_id}]', function ($request, $response, $args) { 
	$locationid = $args['location_id'];
	$Auth = $request->getHeaderLine('Admin-Auth-Token');  
	if($locationid)
	{ 
		if($Auth)
		{ 
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
				$sql = "SELECT * FROM location WHERE status= 1 AND is_deleted = 0 AND location_id = ".$locationid;
				$db = getDB();
				$stmt = $db->query($sql);
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($users)
				{ 
					 $response = $response->withJson($users[0], 200);  
				}
				else
				{
					$data = array('error_code'=>'E0014','text' => "Location Doesn't Exist.");
					$response = $response->withJson($data, 401); 
				}
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			}   
		}
		else
		{
		        $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		}
	}
	else
	{
		$data = array('error_code'=>'E0014','text' => "Location Doesn't Exist.");
		$response = $response->withJson($data, 401);  
	}
	return $response; });
 
// Add Location
$app->post('/addLocation', function ($request, $response, $args) {
	 $parsedBody = $request->getParsedBody();     //print_r($parsedBody);   
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{  
				$name = $parsedBody['location_name'];
				$Address = $parsedBody['address'];
				$city = $parsedBody['city'];  
				$state = $parsedBody['state'];
				$zip = $parsedBody['zip'];
			    $createdate = date('Y-m-d h:i:s'); 
				  
		        $sql = "INSERT INTO location(location_name,address,city,state,zip,user_id,status,timestamp,is_deleted) values('$name','$Address','$city','$state','$zip','$userID','0','$createdate','0')";   
			     $stmt = $db->query($sql); 
		         $lastinsert = $db->lastInsertId();    
			   	 $sqla = "SELECT * FROM location WHERE location_id = '$lastinsert'";  
			     $stmta = $db->query($sqla);
				 $users = $stmta->fetchAll(PDO::FETCH_OBJ);  
				 $response = $response->withJson($users[0], 201);   	  
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response;
});   

// get user by user ID
$app->get('/getUserById[/{user_id}]', function($request, $response, $args){
     
	$user_id = $args['user_id'];
	$Auth = $request->getHeaderLine('Admin-Auth-Token');  
	if($user_id)
	{ 
		if($Auth)
		{ 
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
		  
				$sql = "SELECT * FROM users WHERE id = ".$user_id;
				$db = getDB();
				$stmt = $db->query($sql);
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($users)
				{ 
				     for($i = 0; $i < 10; $i++)
					 {
						unset($users[$i]->password); 
						unset($users[$i]->access_key);
						$report_to = $users[0]->report_to;
						unset($users[0]->report_to); 
						if($report_to != ''){
							$users[0]->report_to = (int)str_replace(' ', '', $report_to);
						}
						else
						{
							$users[0]->report_to = $report_to;
						}
					 }
					 $response = $response->withJson($users[0], 200); 
				}
				else
				{
					$data = array('error_code'=>'E007','text' => "User Doesn't Exist.");
					$response = $response->withJson($data, 401); 
				} 
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			}   
		}
		else
		{
		        $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		}
	}
	else
	{
		$data = array('error_code'=>'E007','text' => "User Doesn't Exist.");
		$response = $response->withJson($data, 401);  
	}
	return $response;  
	 
});

// get data by role
$app->post('/getUsersByRole', function ($request, $response, $args) { 
     $parsedBodya = $request->getParsedBody(); // print_r($parsedBodya);  
	 $role = $parsedBodya['role'];
	 $page = $parsedBodya['page'];
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	 if($role != '' && $page != '')
	 {
		  if($Auth){
				$checkauth = checkAuthKey($Auth);
				if($checkauth)
				{  
				    if($page == '' || $page == '0')
					{
						$pagea = 1;
					}
					else
					{
						$pagea = $page ;
					}
					$offset =  ($pagea * 20); 
					$offset = ($offset -20);
					//$offset = $offset + 1;
					if($offset == 1)
					{
						$offset = 0;
					}  
					if($parsedBodya['role'] == '2')
					{
					    $sql = "SELECT * FROM users  WHERE role > '0' AND role < '3' ORDER BY role  LIMIT ".$offset.", 20"; 
						$db = getDB();
						$stmt = $db->query($sql);  
						$users = $stmt->fetchAll(PDO::FETCH_OBJ);
						$db = null;
						if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
					     $UserID = $users[$i]->id; 
					     $evnta= "SELECT count(*) as total FROM `member_check` WHERE `user_id` = '$UserID' AND `check_in` = 1 AND `confirm` = 1";  
						 $db = getDB();
						 $stmta = $db->query($evnta);
					     $eventa = $stmta->fetchAll(PDO::FETCH_OBJ); 
					     $checkID = $eventa[0]->total;
						 $usersa[$i] = $users[$i];
						 unset($usersa[$i]->password); 
						 unset($usersa[$i]->access_key);
						 $report_to = $usersa[$i]->report_to;
						 unset($usersa[$i]->report_to);
						 	if($report_to != ''){
								$usersa[$i]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$usersa[$i]->report_to = $report_to;
							}  
						}
						 $response = $response->withJson($usersa, 201); 
						}
						else
						{
						$data = array('error_code'=>'E007','text' => "User Doesn't Exist.");
						$response = $response->withJson($data, 401); 
						}
					}
					else
					{
						$sql = "SELECT * FROM users WHERE role = ".$parsedBodya['role']." ORDER BY id LIMIT ".$offset.", 20";   
						$db = getDB();
						$stmt = $db->query($sql);  
						$users = $stmt->fetchAll(PDO::FETCH_OBJ);
						$db = null;
						if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
						 $UserID = $users[$i]->id; 
					     $evnta= "SELECT count(*) as total FROM `member_check` WHERE `user_id` = '$UserID' AND `check_in` = 1 AND `confirm` = 1";  
						 $db = getDB();
						 $stmta = $db->query($evnta);
					     $eventa = $stmta->fetchAll(PDO::FETCH_OBJ); 
					     $checkID = $eventa[0]->total;
						 $usersa[$i] = $users[$i];
						 unset($usersa[$i]->password);
						 unset($usersa[$i]->access_key); 
						 $report_to = $usersa[0]->report_to;
						 unset($usersa[0]->report_to);
						    if($report_to != ''){
								$usersa[$i]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$usersa[$i]->report_to = $report_to;
							}
						}
						$response = $response->withJson($usersa, 201); 
						}
						else
						{
						$data = array('error_code'=>'E007','text' => "User Doesn't Exist.");
						$response = $response->withJson($data, 401); 
						}
					}      
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		  else
		  {
			    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		  } 
	 }
	 else
	 {
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401); 
	 }
	return $response;    
}); 

// Get Event Attendees   By field               
$app->post('/eventAttendeesByField[/{ev_id}]', function ($request, $response, $args) {  
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 $parsedBody = $request->getParsedBody();   // print_r($parsedBody);   
	 $evID = $args['ev_id'];
	 if($Auth)
	 {  
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0]; 
			$db = getDB();
			$data = array();
			if($checkauth)
			{
				$sql = "SELECT * FROM events WHERE id=$evID"; 
				$stmt = $db->query($sql);
				$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($event)
				 {  
					   if($parsedBody['page'] == '')
					   {
						   $page = 1;
					   }   
					   else
					   {
						  $page = $parsedBody['page'];  
					   }
				   
						$offset =  ($page * 20); 
						$offset = ($offset - 20);
						if($offset == 1)
						{
							$offset = 0;
						} 
				   if($parsedBody['grouped_by'] && $parsedBody['grouped_by'] <= 2 || $parsedBody['grouped_by'] == 0){
					   
					   if($parsedBody['grouped_by'] == '0'){  // Company 
							$sqlfield = "SELECT m.Company as fname, count(m.Company) AS attended FROM members m JOIN member_check mc ON m.Member_ID = mc.member_id and mc.event_id = $evID and mc.confirm=1 GROUP BY m.Company ORDER BY m.Company LIMIT $offset, 20";  
							$stmtf = $db->query($sqlfield);
							$Att = $stmtf->fetchAll(PDO::FETCH_OBJ); 
							if($Att){
								for($i = 0 ;$i < count($Att); $i++){   
									$memAtt[$i]['grouped_by'] = $parsedBody['grouped_by'];
									$memAtt[$i]['name'] =  $Att[$i]->fname;
									$memAtt[$i]['total'] = $Att[$i]->attended; 
								  }
								  $response = $response->withJson($memAtt, 200); 
							}
							else
								{
									$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
									$response = $response->withJson($data, 401);  	
								}  
					   }
					   elseif($parsedBody['grouped_by'] == '1') // Department
					   {
								  $sqlfield = "SELECT m.Department as fname, count(m.Department) AS attended FROM members m JOIN member_check mc ON m.Member_ID = mc.member_id and mc.event_id = $evID and mc.confirm=1 GROUP BY m.Department ORDER BY m.Department LIMIT $offset, 20";  
						   
								$stmtf = $db->query($sqlfield);
								$Att = $stmtf->fetchAll(PDO::FETCH_OBJ); 
								 
								if($Att){
									for($i = 0 ;$i < count($Att); $i++){   
										$memDepet[$i]['grouped_by'] = $parsedBody['grouped_by'];
										$memDepet[$i]['name'] = $Att[$i]->fname;
										$memDepet[$i]['total'] = $Att[$i]->attended; 
										
									}
									$response = $response->withJson($memDepet, 200); 
								} 
								else
								{
									$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
									$response = $response->withJson($data, 401);  	
								} 
					   }
					   elseif($parsedBody['grouped_by'] == '2') // Title
					   {
							 $sqlfield = "SELECT m.Title as fname, count(m.Title) AS attended FROM members m JOIN member_check mc ON m.Member_ID = mc.member_id and mc.event_id = $evID and mc.confirm=1 GROUP BY m.Title ORDER BY m.Title LIMIT $offset, 20";
								$stmtf = $db->query($sqlfield);
								$Att = $stmtf->fetchAll(PDO::FETCH_OBJ);  
								if($Att){
									for($i = 0 ;$i < count($Att); $i++){   
										$memDepet[$i]['grouped_by'] = $parsedBody['grouped_by'];
										$memDepet[$i]['name'] = $Att[$i]->fname;
										$memDepet[$i]['total'] = $Att[$i]->attended; 
										
									}
									$response = $response->withJson($memDepet, 200); 
								} 
								else
								{
									$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
									$response = $response->withJson($data, 401);  	
								} 
					   }  
				   }
				   else
				   {
					   $data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
			           $response = $response->withJson($data, 401);  	
				   }
				   
  				}
				else
				{
				   $data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
			       $response = $response->withJson($data, 401);  	
				} 
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			} 
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 } 
	 return $response;   
});

// User Edit
$app->post('/userEditById[/{user_id}]', function ($request, $response, $args) {   
	 $parsedBody = $request->getParsedBody(); // print_r($parsedBody); 
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	$fname = $parsedBody['fname'];
	$lname = $parsedBody['lname'];
	$email = $parsedBody['email']; 
	$role = $parsedBody['role'];
	$report_to = $parsedBody['report_to'];
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
			$password = '';
			if($parsedBody['password'])
			{
				$password = "password = '".md5($parsedBody['password'])."',";
			}
			else
			{
				$password = '';
			}
			if($parsedBody['fname'])
			{
				$fname = "fname = '".$fname."',";
			}
			else
			{
				$fname = '';
			}
			if($parsedBody['lname'])
			{
				$lname = "lname = '".$lname."',";
			}
			else
			{
				$lname = '';
			}
			if($parsedBody['report_to'])
			{
				$report_to = "report_to = '".$report_to."',";
			}
			else
			{
				$report_to = "report_to = NULL,";;
			}
			$em = '';
			if($parsedBody['email'])
			{
				$email = "email = '".$email."',";
			}
			else
			{
				$email = '';
			}
			$co = "";
			if($parsedBody['role'])
			{
				$role = "role = '".$role."'";  
				$co = ",";
			}
			else
			{
				$role = '';
			}
				
			 	$sql = "SELECT * FROM users WHERE id = ".$args['user_id'];   
				$stmt = $db->query($sql);
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);   
				if($users)
				{ 
				    $ac = '';
					if($parsedBody['password'])
					{
						$random = generateRandomString();  
						$random = $userID.'-'.$random;
						$access = "access_key= '".$random."'".$co;
						$ac = ",";
					}
					else
					{
						$access = '';
					} 
					if($parsedBody['report_to'] != ''){
				        $usr = "SELECT * FROM users WHERE id ='".$parsedBody['report_to']."' AND userrole = 'agent'";
						$ustmt = $db->query($usr);
						$users = $ustmt->fetchAll(PDO::FETCH_OBJ);
						if(!empty($users)){
								$update = "UPDATE users SET $password $fname $lname  $email  $access $report_to $role WHERE id = ".$args['user_id'];
								$stmt = $db->query($update); 
								$password = '';
								$sql = "SELECT * FROM users WHERE id = ".$args['user_id'];   
								$stmt = $db->query($sql);
								$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
								unset($users[0]->password);
								if($parsedBody['password'] == '')
								{
								unset($users[0]->access_key);
								}
								if($users[0]->id != $userID)
								{
								unset($users[0]->access_key);
								} 
								$report_to = $users[0]->report_to;
								unset($users[0]->report_to);
								if($report_to != ''){
									$users[0]->report_to = (int)str_replace(' ', '', $report_to);
								}
								else
								{
									$users[0]->report_to = $report_to;
								}
								$response = $response->withJson($users[0], 200);
						}
						else
						{
								$data = array('error_code'=>'E009','text' => "The User To Whom Report To Doesn't Exist.");
								$response = $response->withJson($data, 401);
						}
					}
					else{ 
								$update = "UPDATE users SET $password $fname $lname  $email  $access $report_to $role WHERE id = ".$args['user_id'];
								$stmt = $db->query($update); 
								$password = '';
								$sql = "SELECT * FROM users WHERE id = ".$args['user_id'];   
								$stmt = $db->query($sql);
								$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
								unset($users[0]->password);
								if($parsedBody['password'] == '')
								{
								  unset($users[0]->access_key);
								}
								if($users[0]->id != $userID)
								{
								  unset($users[0]->access_key);
								}
								$report_to = $users[0]->report_to;
								unset($users[0]->report_to);
								if($report_to != ''){
									$users[0]->report_to = (int)str_replace(' ', '', $report_to);
								}
								else
								{
									$users[0]->report_to = $report_to;
								} 
								$response = $response->withJson($users[0], 200); 
					}
				}
				else
				{
					 $data = array('error_code'=>'E007','text' => "User Doesn't Exist.");
		             $response = $response->withJson($data, 401);  
				}
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response;  });

// get event by status
$app->post('/eventByStatus', function($request, $response, $args){                 // 0 : All , 1 : Open , 2 : Past
     $parsedBodya = $request->getParsedBody();  // print_r($parsedBodya);   
	 $status = $parsedBodya['status'];
	 $page = $parsedBodya['page'];
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	 if($status != '' && $page != '')
	 {
		  if($Auth){
				$checkauth = checkAuthKey($Auth);
				if($checkauth)
				{   
				
				    if($page == '' || $page == '0')
					{
						$pagea = 1;
					}
					else
					{
						$pagea = $page ;
					}
					$offset =  ($pagea * 20); 
					$offset = ($offset - 20);
					//$offset = $offset + 1;
					if($offset == 1)
					{
						$offset = 0;
					}  
					if($status < 3 && $status >= 0 || $status != '') {
						 if($status == '0')
						 {
							$sql = "SELECT * FROM events ORDER BY date DESC LIMIT ".$offset.", 20";
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							if($event)
							{ 
							   for($i = 0 ;$i < count($event); $i++)
								{   
									   $evID = $event[$i]->id;
									   $evnta= "SELECT count(*) as total FROM `member_check` WHERE `event_id` = '$evID' AND `check_in` = 1 AND `confirm` = 1";
									   $stmta = $db->query($evnta);
									   $eventa = $stmta->fetchAll(PDO::FETCH_OBJ);
									   $checkID = $eventa[0]->total;
									   $event[$i]->number_of_checkin =$checkID;  
									   $data[$i] = $event[$i];  
								}   
							    $response = $response->withJson($data, 200); 
							}
							else
							 {
								 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
								  $response = $response->withJson($data, 401);   
							 } 
						 }
						 else if($status == '1')
						 {
							
							$sql = "SELECT * FROM events WHERE status = '1' ORDER BY date DESC LIMIT ".$offset.", 20";
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							if($event)
							{ 
							    $j= 0;
							   for($i = 0 ;$i < count($event); $i++)
								{     
								   $eventDT = strtotime($event[$i]->date." ".$event[$i]->time); 
								   if(($eventDT <= strtotime("tomorrow 6am")))
									{
									   $evID = $event[$i]->id;
									   $evnta= "SELECT count(*) as total FROM `member_check` WHERE `event_id` = '$evID' AND `check_in` = 1 AND `confirm` = 1";
									   $stmta = $db->query($evnta);
									   $eventa = $stmta->fetchAll(PDO::FETCH_OBJ);
									   $checkID = $eventa[0]->total;
									   $event[$i]->number_of_checkin =$checkID;  
									   $data[$j] = $event[$i];  
									   $j++;
									}
								}   
							    $response = $response->withJson($data, 200); 
							}
							else
							{
							    $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							    $response = $response->withJson($data, 401);   
							}  
						 }
						 else if($status == '2')
						 {  
							$date = date('Y-m-d');
							$time = date("H:i:s ", strtotime("today 6am"));  
						    $sql = "SELECT * FROM events WHERE status = '0' ORDER BY date DESC LIMIT ".$offset.", 20";// 
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							$data = array();
							if($event)
							{  
							     $J= 0;
								for($i = 0 ;$i < count($event); $i++)
								{    
								    $eventDT = strtotime($event[$i]->date." ".$event[$i]->time);
									 // $cldate = strtotime(date('Y-m-d H:i:s'));
									if(($eventDT < strtotime(date('Y-m-d H:i:s'))))
									{ 
									   array_push($data,$event[$i]);
									   $evID = $event[$i]->id;
									   $evnta= "SELECT count(*) as total FROM `member_check` WHERE `event_id` = '$evID' AND `check_in` = 1 AND `confirm` = 1";
									   $stmta = $db->query($evnta);
									   $eventa = $stmta->fetchAll(PDO::FETCH_OBJ);
									   $checkID = $eventa[0]->total;
									   $event[$j]->number_of_checkin =$checkID;
									   $J++;
									}
								}   
								if($event){
								   $response = $response->withJson($data, 200); 
								} 
							}
							else
							{
							    $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							    $response = $response->withJson($data, 401);   
							} 
					    }
						 else
						 {
							 $data = array();
							 $response = $response->withJson($data, 200);
						 }
					}
					else
					{
						$data = array('error_code'=>'E004','text' => "Invalid Status.");
		                $response = $response->withJson($data, 401); 
					} 
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		  else
		  {
			    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		  } 
	 }
	 else
	 {
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401); 
	 }
	return $response;    
});

// User Add 
$app->post('/addUser', function ($request, $response, $args) {  
	 $parsedBody = $request->getParsedBody();  //  print_r($parsedBody);  
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
				$password = md5($parsedBody['password']);
				$fname = $parsedBody['fname'];
				$lname = $parsedBody['lname'];
				$email = $parsedBody['email']; 
				$role = $parsedBody['role'];
				$userrole = $parsedBody['userrole'];
				$report_to = $parsedBody['report_to'];
				if($userrole == '')
				{
				   $userrole = 'steward';
				}
			    $sql = "SELECT * FROM users WHERE email ='$email'"; 
				$stmt = $db->query($sql);
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);   
				if($users)
				{ 
					$data = array('error_code'=>'E0011','text' => "Email Already Exists. Please Choose Another.");
			        $response = $response->withJson($data, 401);  
				}
				else
				{
					if($report_to != ''){
						$usr = "SELECT * FROM users WHERE id ='$report_to'"; 
						$ustmt = $db->query($usr);
						$users = $ustmt->fetchAll(PDO::FETCH_OBJ);
						if(!empty($users)){
							$sql = "INSERT INTO users(password,fname,lname,email,role,userrole,report_to) values('$password','$fname','$lname','$email','$role','$userrole','$report_to')";
							$stmt = $db->query($sql); 
							$sql = "SELECT * FROM users WHERE email ='$email' AND password = '$password'"; 
							$stmt = $db->query($sql);
							$users = $stmt->fetchAll(PDO::FETCH_OBJ);
							if($users[0]->id)
							{
								unset($users[0]->password); 
								unset($users[0]->access_key);
								$report_to = $users[0]->report_to;
								unset($users[0]->report_to);
								if($report_to != ''){
									$users[0]->report_to = (int)str_replace(' ', '', $report_to);
								}
								else
								{
									$users[0]->report_to = $report_to;
								}
								$response = $response->withJson($users[0], 201);  
							}
						}
						else
						{
								$data = array('error_code'=>'E009','text' => "The User To Whom Report To Doesn't Exist.");
								$response = $response->withJson($data, 401);
						}
					}
					else
					{
						$sql = "INSERT INTO users(password,fname,lname,email,role,userrole,report_to) values('$password','$fname','$lname','$email','$role','$userrole',NULL)";
						$stmt = $db->query($sql); 
						$sql = "SELECT * FROM users WHERE email ='$email' AND password = '$password'"; 
						$stmt = $db->query($sql);
						$users = $stmt->fetchAll(PDO::FETCH_OBJ);
						if($users[0]->id)
						{
							unset($users[0]->password); 
							unset($users[0]->access_key);
							$report_to = $users[0]->report_to;
							unset($users[0]->report_to);
							if($report_to != ''){
								$users[0]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$users[0]->report_to = $report_to;
							}
							$response = $response->withJson($users[0], 201);  
						}
					}
				}
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 return $response;  
});
 
// get event by id
$app->get('/eventById[/{ev_id}]', function($request, $response, $args){ 
	$ev_id = $args['ev_id'];
	$Auth = $request->getHeaderLine('Admin-Auth-Token');  
	if($ev_id)
	{ 
		if($Auth)
		{ 
			$checkauth = checkAuthKey1($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
		  
				$sql = "SELECT * FROM events WHERE id = ".$ev_id;
				$db = getDB();
				$stmt = $db->query($sql);
				$usersa = $stmt->fetchAll(PDO::FETCH_OBJ);
				if($usersa)
				{ 
				     $sqla = "SELECT count(*) AS total FROM `member_check` WHERE `check_in` = 1 AND `confirm` = 1 AND event_id =".$ev_id;
				     $db = getDB();
				     $stmta = $db->query($sqla);
					 $userss = $stmta->fetchAll(PDO::FETCH_OBJ); 
					 $totaluser = $userss[0]->total; 
					 $usersa[0]->number_of_checkin = $totaluser; 
					 $response = $response->withJson($usersa[0], 200); 
				}
				else
				{
					$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
					$response = $response->withJson($data, 401); 
				} 
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			}   
		}
		else
		{
		        $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		}
	}
	else
	{
		$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
		$response = $response->withJson($data, 401);  
	}
	return $response;  
});
 
// Add Event
$app->post('/addEvent', function ($request, $response, $args) {
	 $parsedBody = $request->getParsedBody();   // print_r($parsedBody);   
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB(); 
			if($checkauth)
			{   
			     $ab = date('Y-m-d',strtotime($parsedBody['date']));
			    if(strtotime($ab.' '.$parsedBody['time']) > strtotime(date('Y-m-d'))){
					$name = $parsedBody['event_name'];
					$date =  date('Y-m-d H:i:s',strtotime($ab.' '.$parsedBody['time']));
					$time = $parsedBody['time'];  
					$createdate = date('Y-m-d H:i:s',strtotime($parsedBody['date'].' '.$parsedBody['time'])); 
					$modifydate = '0000-00-00 00:00:00';
					if($parsedBody['status'] != ''){
						$status = $parsedBody['status'];
					}
					else
					{
						$status = 1;
					}
					$time = date('H:i:s',strtotime($date));
					$sql = "INSERT INTO events(event_name,date,time,status,created_at,modified_at,creator) values('$name','$date','$time','$status','$createdate','$modifydate',$userID)";
					$stmt = $db->query($sql); 
					$lastID = $db->lastInsertId();
					$sql = "SELECT * FROM events WHERE id ='$lastID'";  
					$stmt = $db->query($sql);
					$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
					$response = $response->withJson($users[0], 201);
					
				}
				else
				{ 
					$data = array('error_code'=>'E002','text' => "Please Enter Correct Date.");
			        $response = $response->withJson($data, 401);
				}
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response;
}); 

// Get Event Checkin
$app->post('/eventCheckInMember[/{ev_id}]', function ($request, $response, $args) { 

	$evID = $args['ev_id']; 
    $parsedBody = $request->getParsedBody();     //print_r($parsedBody);   
	$Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 {
		  
		  if(!empty($parsedBody)){
			  
				$checkauth = checkAuthKey($Auth); 
				$accesstoken =  explode('-',$Auth); 
				$userID = $accesstoken[0];
				$member_id = $parsedBody['member_id'];
				$db = getDB();
				if($checkauth)
				{  
					$member = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'";
					$memberdata = $db->query($member);
					$member = $memberdata->fetchAll(PDO::FETCH_OBJ);
				   if($member){ 
						if($parsedBody['checked_in'] == 'true')
						{ 
						  $select = "SELECT * FROM `member_check` WHERE `member_id` = '$member_id' AND `event_id` = '$evID' AND `user_id` = '$userID'";
						  $selectstmt = $db->query($select);
						  $membercheck = $selectstmt->fetchAll(PDO::FETCH_OBJ);
							  if($membercheck)
							  { 
								  $datea = date('Y-m-d- H:i:s');
								  $update = "UPDATE member_check SET confirm=1 ,check_in=1,checkin_time= '$datea' WHERE event_id=".$evID." AND member_id='".$member_id."'"; 
								  $stmt = $db->query($update); 
								  $sel = "SELECT * FROM `member_check` WHERE `member_id` = '$member_id' AND `event_id` = '$evID'";
								  $selectmember = $db->query($sel);
								  $membercheckin = $selectmember->fetchAll(PDO::FETCH_OBJ);
								  $user = "SELECT * FROM `users` WHERE `id` = '$userID'";
								  $selectuser = $db->query($user);
								  $userdata = $selectuser->fetchAll(PDO::FETCH_OBJ); 
								  unset($userdata[0]->password);
								  unset($userdata[0]->access_key);
								    $report_to = $userdata[0]->report_to;
									unset($userdata[0]->report_to);
									if($report_to != ''){
										$userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
									}
									else
									{
										$userdata[0]->report_to = $report_to;
									}
								  $data =  array('member' => $member[0],"event_id"=>$evID ,"date_checked" => $membercheckin[0]->checkin_time,"is_checked"=>$parsedBody['checked_in'] ,"user"=>$userdata[0] );
								  $response = $response->withJson($data, 200);
								  
							  }
							  else
							  { 
								  $dateb = date('Y-m-d H:i:s');
								  $insert = "INSERT INTO member_check VALUES (null, '".$member_id."','".$evID."', 1, 1,'".$userID."','".$dateb."')"; 
								  $stmt = $db->query($insert);
								  $sel = "SELECT * FROM `member_check` WHERE `member_id` = '$member_id' AND `event_id` = '$evID'";
								  $selectmember = $db->query($sel);
								  $membercheckin = $selectmember->fetchAll(PDO::FETCH_OBJ);
								  $user = "SELECT * FROM `users` WHERE `id` = '$userID'";
								  $selectuser = $db->query($user);
								  $userdata = $selectuser->fetchAll(PDO::FETCH_OBJ); 
								  unset($userdata[0]->password);
								  unset($userdata[0]->access_key);
								    $report_to = $userdata[0]->report_to;
									unset($userdata[0]->report_to);
									if($report_to != ''){
										$userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
									}
									else
									{
										$userdata[0]->report_to = $report_to;
									}
								  $data =  array('member' => $member[0],"event_id"=>$evID ,"date_checked" => $membercheckin[0]->checkin_time,"is_checked"=>$parsedBody['checked_in'] ,"user"=>$userdata[0] );
								  $response = $response->withJson($data, 200);
							  } 
						}
						elseif($parsedBody['checked_in'] == 'false')
						{ 
					        $datea = date('Y-m-d- H:i:s');
						    $update = "UPDATE member_check SET confirm=0 ,check_in=0,checkin_time= '$datea' WHERE event_id=".$evID." AND member_id='".$member_id."'"; 
							$stmt = $db->query($update);
							$sel = "SELECT * FROM `member_check` WHERE `member_id` = '$member_id' AND `event_id` = '$evID'";
							$selectmember = $db->query($sel);
							$membercheckin = $selectmember->fetchAll(PDO::FETCH_OBJ);
							$user = "SELECT * FROM `users` WHERE `id` = '$userID'";
							$selectuser = $db->query($user);
							$userdata = $selectuser->fetchAll(PDO::FETCH_OBJ); 
							unset($userdata[0]->password);
							unset($userdata[0]->access_key);
							$data =  array('member' => $member[0],"event_id"=>$evID ,"date_checked" => $membercheckin[0]->checkin_time,"is_checked"=>$parsedBody['checked_in'] ,"user"=>$userdata[0] );
							
							$response = $response->withJson($data, 200);
						} 
				   }
					else
				   {
					    $data = array('error_code'=>'E002','text' => "Please Enter Value.");
					    $response = $response->withJson($data, 401);      
				   } 
				}
				else
				{
				   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				   $response = $response->withJson($data, 401);  
				} 
			}
		    else
		    {
			  $data = array('error_code'=>'E002','text' => "Please Enter Value.");
			  $response = $response->withJson($data, 401);   
		    } 
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 } 
 return $response;
}); 
 
// Get Event Attendees   By User Role               
$app->get('/eventAttendeesByUserRole[/{ev_id}]', function ($request, $response, $args) {  
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 $evID = $args['ev_id'];
	 if($Auth)
	 {  
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			$data = array();
			if($checkauth)
			{
				$sql = "SELECT * FROM events WHERE id=$evID"; 
				$stmt = $db->query($sql);
				$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($event)
				{ 
					// SUPER ADMIN 
					$superadmin = "SELECT * FROM `users` WHERE `role` = 1 ORDER BY fname ASC";  
					$superadmin = $db->query($superadmin);
					$allsuperadmin = $superadmin->fetchAll(PDO::FETCH_OBJ); 
					$j =0;
					for($i = 0; $i < count($allsuperadmin); $i++)
					{ 
						$rorleID = $allsuperadmin[$i]->id;
						$superadminall = "SELECT count(*) as total FROM `member_check` WHERE `user_id` = '$rorleID' AND `check_in` = 1 AND `confirm` = 1 AND `event_id` = '$evID'";
						$stmta = $db->query($superadminall);
						$allsuperadminall = $stmta->fetchAll(PDO::FETCH_OBJ);  
						if($allsuperadminall[0]->total > '0'){
							unset($allsuperadminall[$i]->password);
							unset($allsuperadmin[$i]->password);
							unset($allsuperadmin[$i]->access_key);
							$report_to = $allsuperadmin[$i]->report_to;
							unset($allsuperadmin[$i]->report_to);
							if($report_to != ''){
								$allsuperadmin[$i]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$allsuperadmin[$i]->report_to = $report_to;
							}
							$datasuperadmin[$j]->user = $allsuperadmin[$i];
							$datasuperadmin[$j]->total = $allsuperadminall[0]->total;
							$totalsuperadmin[$j] = $datasuperadmin[$j];
						}
					} 
					 if($totalsuperadmin == '')
					 {
						 $totalsuperadmin = array();
					 }
					
					// ADMIN
					$admin = "SELECT * FROM `users` WHERE `role` = 2 ORDER BY fname ASC";  
					$admin = $db->query($admin);
					$alladmin = $admin->fetchAll(PDO::FETCH_OBJ); 
					$j= 0;
					for($i = 0; $i < count($alladmin); $i++)
					{ 
						$rorleID = $alladmin[$i]->id;
						$alladminall = "SELECT count(*) as total FROM `member_check` WHERE `user_id` = '$rorleID' AND `check_in` = 1 AND `confirm` = 1 AND `event_id` = '$evID'";
						$stmta = $db->query($alladminall);
						$alladminall = $stmta->fetchAll(PDO::FETCH_OBJ);  
						if($alladminall[0]->total > '0'){
							unset($alladminall[$i]->password);
							unset($alladmin[$i]->password);
							unset($alladmin[$i]->access_key);
							$report_to = $alladmin[$i]->report_to;
							unset($alladmin[$i]->report_to);
							if($report_to != ''){
								$alladmin[$i]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$alladmin[$i]->report_to = $report_to;
							} 
							$dataadmin[$j]->user = $alladmin[$i];
							$dataadmin[$j]->total = $alladminall[0]->total;
							$totaladmin[$j] = $dataadmin[$j];
						}
					}  
					 if($totaladmin == '')
					 {
						 $totaladmin = array();
					 }
					$addarray = array_merge($totaladmin,$totalsuperadmin);
					 
					// STAFF 
					$staff = "SELECT * FROM `users` WHERE `role` = 3 ORDER BY fname ASC";  
					$staff = $db->query($staff);
					$allstaff = $staff->fetchAll(PDO::FETCH_OBJ);
					 $j =0;
					for($i = 0; $i < count($allstaff); $i++)
					{
						$rorleID = $allstaff[$i]->id;  
						$staffall = "SELECT count(*) as total FROM `member_check` WHERE `user_id` = '$rorleID' AND `check_in` = 1 AND `confirm` = 1 AND `event_id` = '$evID'"; 
						$stmtastaffall = $db->query($staffall);
						$allstaffall = $stmtastaffall->fetchAll(PDO::FETCH_OBJ); 
						if($allstaffall[0]->total > '0'){
							unset($allstaffall[$i]->password);
							unset($allstaff[$i]->password);
							unset($allstaff[$i]->access_key);
							$report_to = $allstaff[$i]->report_to;
							unset($allstaff[$i]->report_to);
							if($report_to != ''){
								$allstaff[$i]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$allstaff[$i]->report_to = $report_to;
							} 
							$data[$j]->user = $allstaff[$i];
							$data[$j]->total = $allstaffall[0]->total;  
							$totalstaff[$j] = $data[$j];
							$j++; 
						} 
					}  
					 if($totalstaff == '')
					 {
						  $totalstaff = array();
					 } 
					$alldata['admin'] = $addarray;
					$alldata['staff'] = $totalstaff;
					$response = $response->withJson($alldata, 200);	 
				}
				else
				{
					$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
					$response = $response->withJson($data, 401); 
				} 
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			} 
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 } 
	 return $response;   
}); 

// event location
$app->post('/picketLocations', function ($request, $response, $args) { 
      
     $parsedBodya = $request->getParsedBody(); //print_r($parsedBodya);   
	 $active = $parsedBodya['active'];
	 $page = $parsedBodya['page'];
	 $query = $parsedBodya['query'];
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 { 
	        $checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
			    if($page) {
					$offset =  ($page * 20); 
					$offset = ($offset - 20); 
					if($offset == 1)
					{
						$offset = 0;
					}
				}
				if($query){
					if($active == 'true'){ 
			        $sql = "SELECT * FROM `location` WHERE status= 1 AND is_deleted = 0 AND `location_name` LIKE '%$query%' ORDER BY location_id LIMIT ".$offset.", 20";  
				   $stmt = $db->query($sql);
				   $event = $stmt->fetchAll(PDO::FETCH_OBJ);  
				   if($event){
		              $response = $response->withJson($event, 200);   
				   }
				   else
				   {
					   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
					   $response = $response->withJson($data, 401);   
					} 
				}else if($active == 'false'){ 
			       $sql = "SELECT * FROM `location` WHERE status= 0 AND is_deleted = 0 AND `location_name` LIKE '%$query%' ORDER BY location_id LIMIT ".$offset.", 20";  
				   $stmt = $db->query($sql);
				   $event = $stmt->fetchAll(PDO::FETCH_OBJ);  
		           if($event){
		              $response = $response->withJson($event, 200);   
				   }else
				   {
					   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
					   $response = $response->withJson($data, 401);   
				   }
				}
				}
				else
				{
					if($active == 'true'){ 
			       $sql = "SELECT * FROM `location` WHERE status= 1 AND is_deleted = 0 ORDER BY location_id LIMIT ".$offset.", 20";   
				   $stmt = $db->query($sql);
				   $event = $stmt->fetchAll(PDO::FETCH_OBJ);  
				   if($event){
		              $response = $response->withJson($event, 200);   
				   }
				   else
				   {
					   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
					   $response = $response->withJson($data, 401);   
					} 
				}else if($active == 'false'){ 
			       $sql = "SELECT * FROM `location` WHERE status= 0 AND is_deleted = 0 ORDER BY location_id LIMIT ".$offset.", 20";  
				   $stmt = $db->query($sql);
				   $event = $stmt->fetchAll(PDO::FETCH_OBJ);  
		           if($event){
		              $response = $response->withJson($event, 200);   
				   }else
				   {
					   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
					   $response = $response->withJson($data, 401);   
				   }
				}
				}   
			     
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			    $response = $response->withJson($data, 401);    
			}   
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 406);  
	 }
	 
	 return $response;   });
 
// Get Home Page Data
$app->get('/getHomeData', function($request, $response, $args){ 
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
		if($Auth)
		{ 
			$checkauth = checkAuthKey1($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
				$event = "SELECT * FROM events WHERE status=1 ORDER BY date DESC";  
				$event = $db->query($event);
				$allevent = $event->fetchAll(PDO::FETCH_OBJ);
				$a = 0;   
				for($i = 0 ;$i < count($allevent); $i++)
				{  
				   $eventDT = strtotime($allevent[$i]->date." ".$allevent[$i]->time); 
				   if(($eventDT <= strtotime("tomorrow 6am")))
					{    
					   $alldata = $allevent[$i]; 
					   $evID = $allevent[$i]->id;
					   $evnta= "SELECT count(*) as total FROM `member_check` WHERE `event_id` = '$evID' AND `check_in` = 1 AND `confirm` = 1";
					   $stmta = $db->query($evnta);
					   $eventa = $stmta->fetchAll(PDO::FETCH_OBJ);
					   $checkID = $eventa[0]->total;
					   $alldata->number_of_checkin = $checkID; 
					   $a++; 
					} 
				}   
				if($a == '1')
				{
					$homedata->event = $alldata;
				} 
				
				// code for call_in data
				$callin_date =  date('Y-m-d');
				$query = $db->query("SELECT * FROM call_in WHERE callin_date = '".$callin_date."' AND user = '".$userID."' ");
				$users = $query->fetchAll(PDO::FETCH_OBJ);  
				for($i = 0; $i < count($users); $i++)
				{  
					$data[$i] = $users[0];
					// User Code 
					$user = "SELECT * FROM users WHERE id = '".$users[$i]->user."'";
					$userdata = $db->query($user);
					$udata = $userdata->fetchAll(PDO::FETCH_OBJ); 
					unset($udata[0]->password);
					unset($udata[0]->access_key);
				}
				if(!empty($data[0])){
				   $homedata->call_in = $data[0];
				}
				$sql = "SELECT * FROM picket_duty WHERE status = 1 AND is_deleted=0 ORDER BY start_date DESC"; 
				$picket = $db->query($sql);
				$allpicket = $picket->fetchAll(PDO::FETCH_OBJ); 
				
				if(count($allpicket) == '1')
				{
					$pk_id = $allpicket[0]->picket_id;
					$psql = "SELECT count(*) as total FROM `picket_registration` WHERE `picket_id` = '$pk_id'";
					$pstmt = $db->query($psql);
					$pickets = $pstmt->fetchAll(PDO::FETCH_OBJ); 
					$homedata->picket = $allpicket[0]; 
					$UID = $allpicket[0]->user_id;
					
					$homedata->total_signed_up = $pickets[0]->total;
				}  
				    $sqluser = "SELECT * FROM `users` WHERE id = '$userID'"; 
					$user = $db->query($sqluser);
					$userdata = $user->fetchAll(PDO::FETCH_OBJ);
					if($userdata)
					{
						unset($userdata[0]->password);
						unset($userdata[0]->access_key); 
						$report_to = $userdata[0]->report_to;
						unset($userdata[0]->report_to);
							if($report_to != ''){
								$userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$userdata[0]->report_to = $report_to;
							} 
						$homedata->user = $userdata[0]; 
					}  
				if($homedata){
				  $response = $response->withJson($homedata, 200);
				}
				else
				{ 
				  $d = array(); 
                   $response = json_encode($d, JSON_FORCE_OBJECT); 
				}
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			}   
		}
		else
		{
		        $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		} 
	return $response;   
});
 
// Edit Event
$app->post('/editEventById[/{ev_id}]', function ($request, $response, $args) { 
	 $parsedBody = $request->getParsedBody();     
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 $evID = $args['ev_id'];
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{  
			    $sql = "SELECT * FROM `events` WHERE `ID` = $evID"; 
				$stmt = $db->query($sql);
				$location = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($location)
				{
					$name = $parsedBody['event_name'];
					$date = $parsedBody['date'];
					$time = $parsedBody['time'];  
					$status = $parsedBody['status']; 
					$modifydate = date('Y-m-d h:i:s');
					$update = "UPDATE events SET event_name= '$name', date = '$date', time = '$time',status = '$status' ,modified_at = '$modifydate' WHERE id = ".$evID;  
					$stmt = $db->query($update); 
					$sql = "SELECT * FROM events WHERE id =$evID";  
					$stmt = $db->query($sql);
					$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
					$response = $response->withJson($users[0], 201);   	 
				}
				else
				{
					$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			        $response = $response->withJson($data, 401);
				}
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response; });
	 
// Edit Location
$app->post('/editLocationById[/{loc_id}]', function ($request, $response, $args) { 
	 $parsedBody = $request->getParsedBody();   
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 $LocID = $args['loc_id'];
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
			    $sql = "SELECT * FROM `location` WHERE `location_id` = $LocID"; 
				$stmt = $db->query($sql);
				$location = $stmt->fetchAll(PDO::FETCH_OBJ);
				if(!empty($location))
				{
					$name = $parsedBody['location_name'];
					$Address = $parsedBody['address'];
					$city = $parsedBody['city'];  
					$state = $parsedBody['state'];
					$zip = $parsedBody['zip'];
					$status  = $parsedBody['status'];
				    $update = "UPDATE location SET location_name= '$name',address = '$Address', city = '$city', state = '$state',zip = '$zip',status = '$status' WHERE location_id = ".$LocID;
					$stmtupdate = $db->query($update);
				    $sql = "SELECT * FROM `location` WHERE `location_id` = $LocID"; 
				    $stmt = $db->query($sql);
				    $newlocation = $stmt->fetchAll(PDO::FETCH_OBJ);  
					$response = $response->withJson($newlocation[0], 201);  
				}
				else
				{
					$data = array('error_code'=>'E0013','text' => "Location Doesn't Exist.");
					$response = $response->withJson($data, 401);
				} 	 
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response; });
 
// Add Picket
$app->post('/addPicket', function ($request, $response, $args) {  
	 $parsedBody = $request->getParsedBody(); 
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
				$picket_name = $parsedBody['picket_name'];
				$no_of_weeks = $parsedBody['no_of_weeks'];
				$start_date = $parsedBody['start_date'];
			 	$hours_per_week = $parsedBody['hours_per_week']; 
				$day_start = $parsedBody['day_start']; 
				$total_signup = $parsedBody['total_signup'];
				$status = $parsedBody['status'];
				$creationdate = date('Y-m-d h:i:s');
				$update = "0000-00-00 00:00:00";
			    $sql = "INSERT INTO picket_duty(start_date,creation_time,Updation_time,user_id,status,no_of_weeks,picket_name,hours_per_week,day_start,total_signup,is_deleted) values('$start_date','$creationdate','$update','$userID','$status','$no_of_weeks','$picket_name','$hours_per_week','$day_start','$total_signup','0')";  
				$stmt = $db->query($sql); 
				$lastinsert = $db->lastInsertId();
				$sql = "SELECT * FROM picket_duty WHERE picket_id = $lastinsert"; 
				$stmt = $db->query($sql); 
				$picket = $stmt->fetchAll(PDO::FETCH_OBJ); 
				if($picket)
				{
					$response = $response->withJson($picket[0], 200);  
				} 
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 return $response;  
});

// get Picket by id
$app->get('/getPicketById[/{pk_id}]', function($request, $response, $args){ 
	 $pk_id = $args['pk_id'];  
	$Auth = $request->getHeaderLine('Admin-Auth-Token');  
	if($pk_id)
	{ 
		if($Auth)
		{ 
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{   
		        $sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `picket_id` = '$pk_id'";   
				$stmt = $db->query($sql);
				$usersa = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($usersa)
				{  
					$sqlp = "SELECT count(*) as total FROM `picket_registration` WHERE `picket_id` = '$pk_id'";
					$stmtp = $db->query($sqlp);
					$picket = $stmtp->fetchAll(PDO::FETCH_OBJ); 
					$usersa[0]->total_signed_up = $picket[0]->total;
					$response = $response->withJson($usersa[0], 200);  
				}
				else
				{
					$data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
					$response = $response->withJson($data, 401); 
				} 
			}
			else
			{
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			}   
		}
		else
		{
		        $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		}
	}
	else
	{
		$data = array('error_code'=>'E009','text' => "Picket Doesn't Exist.");
		$response = $response->withJson($data, 401);  
	}
	return $response;  
	
});
 
// Edit Picket
$app->post('/editPicketById[/{pk_id}]', function ($request, $response, $args) {  
	 $parsedBody = $request->getParsedBody();
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	  $pk_id = $args['pk_id']; 
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{
				$sql = "SELECT * FROM `picket_duty` WHERE `picket_id` = $pk_id"; 
				$stmt = $db->query($sql);
				$location = $stmt->fetchAll(PDO::FETCH_OBJ);  
				if($location)
				{    
			    $picket_name = $parsedBody['picket_name'];
				$no_of_weeks = $parsedBody['no_of_weeks'];
				 $start_date =  date('Y-m-d',strtotime($parsedBody['start_date'])); 
				$hours_per_week = $parsedBody['hours_per_week']; 
				$day_start = $parsedBody['day_start']; 
				$total_signup = $parsedBody['total_signup'];
				$creationdate = date('Y-m-d h:i:s');
				$status = $parsedBody['status'];
				$update = date('Y-m-d h:i:s');
			   $update = "UPDATE picket_duty SET start_date= '$start_date', Updation_time = '$update', status = '$status', no_of_weeks = '$no_of_weeks' ,picket_name = '$picket_name',hours_per_week = '$hours_per_week',day_start = '$day_start',total_signup = '$total_signup' WHERE picket_id = ".$pk_id; 
				$stmt = $db->query($update); 
				$lastinsert = $db->lastInsertId();
				$stmt = $db->query($sql); 
				$picket = $stmt->fetchAll(PDO::FETCH_OBJ);
					$response = $response->withJson($picket[0], 200);  
				}
				else
				{
				  $data = array('error_code'=>'E002','text' => "Please Enter Value.");
			       $response = $response->withJson($data, 401);  	
				 }
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response;  
    
});
 
// get Picket by status
$app->post('/picketByStatus', function($request, $response, $args){                 // 0 : All , 1 : Open , 2 : Past
     $parsedBodya = $request->getParsedBody();  
	 $status = $parsedBodya['status'];
	 $page = $parsedBodya['page'];
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	 if($status != '' && $page != '')
	 {
		  if($Auth){
				$checkauth = checkAuthKey($Auth);
				if($checkauth)
				{   
				    if($page == '' || $page == '0')
					{
						$pagea = 1;
					}
					else
					{
						$pagea = $page ;
					}
					$offset =  ($pagea * 20); 
					$offset = ($offset - 20);
					if($offset == 1)
					{
						$offset = 0;
					}  
					if($status < 3) {
						 if($status == '0')  // 0: Active
						 { 
						 	$sql = "SELECT * FROM picket_duty WHERE status = 1 AND is_deleted=0 ORDER BY start_date DESC LIMIT $offset, 20"; 
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							if($event)
							{     
								 for($i=0;$i < count($event); $i++)
								 {
								    $PKID = $event[$i]->picket_id;
									$sqlp = "SELECT count(*) as total FROM `picket_registration` WHERE `picket_id` = '$PKID'"; 
									$db = getDB();
									$stmtp = $db->query($sqlp);
									$picket = $stmtp->fetchAll(PDO::FETCH_OBJ);
									$signup =  $picket[0]->total;
									$data[$i] = $event[$i];
									$data[$i]->total_signed_up = $signup;
								 } 
							    $response = $response->withJson($data, 200); 
							} 
							else
							{
							   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							   $response = $response->withJson($data, 401);
							}
						 }
						 else if($status == '1')  // 1: Disable
						 {
							
							$sql = "SELECT * FROM picket_duty WHERE status = 0 AND is_deleted=0 ORDER BY start_date DESC LIMIT $offset, 20";
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							if($event)
							{    
								 for($i=0;$i < count($event); $i++)
								 {
								    $PKID = $event[$i]->picket_id;
									$sqlp = "SELECT count(*) as total FROM `picket_registration` WHERE `picket_id` = '$PKID'"; 
									$db = getDB();
									$stmtp = $db->query($sqlp);
									$picket = $stmtp->fetchAll(PDO::FETCH_OBJ);
									$signup =  $picket[0]->total;
									$data[$i] = $event[$i];
									$data[$i]->total_signed_up = $signup;
								 } 
							    $response = $response->withJson($data, 200);   
							} 
							else
							{
							   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							   $response = $response->withJson($data, 401);
							}
						 }
						 else if($status == '2')  // 2: Past
						 {
							 $sql = "SELECT * FROM picket_duty WHERE status = 2 AND is_deleted=0 ORDER BY start_date DESC LIMIT $offset, 20";
							$db = getDB();
							$stmt = $db->query($sql);
							$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
							if($event)
							{   
								for($i=0;$i < count($event); $i++)
								 {
								    $PKID = $event[$i]->picket_id;
									$sqlp = "SELECT count(*) as total FROM `picket_registration` WHERE `picket_id` = '$PKID'"; 
									$db = getDB();
									$stmtp = $db->query($sqlp);
									$picket = $stmtp->fetchAll(PDO::FETCH_OBJ);
									$signup =  $picket[0]->total;
									$data[$i] = $event[$i];
									$data[$i]->total_signed_up = $signup;
								 }   
							        $response = $response->withJson($data, 200); 
							} 
							else
							{
							   $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							   $response = $response->withJson($data, 401);
							}
					    }
					}
					else
					{
						$data = array('error_code'=>'E004','text' => "Invalid Status.");
		                $response = $response->withJson($data, 401); 
					} 
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		  else
		  {
			    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		  } 
	 }
	 else
	 {
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401); 
	 }
	return $response;    
});
 
// Get Event Checkin   
$app->post('/checkinEvent[/{ev_id}]', function ($request, $response, $args) {   
	$evID = $args['ev_id']; 
    $parsedBody = $request->getParsedBody();
	$Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	$db = getDB(); 
	 if($Auth)
	 { 
		$checkauth = checkAuthKey($Auth); 
		   if($checkauth)
			{ 
			$sql = "SELECT * FROM events WHERE  id=$evID"; 
			$stmt = $db->query($sql);
			$event = $stmt->fetchAll(PDO::FETCH_OBJ);  
			if($event)
			{
				     if($parsedBody['page'] == '' || $parsedBody['page'] == '0')
					{
						$page = 1;
					}
					else
					{
						$page = $parsedBody['page'];
					}
						$offset =  ($page * 20); 
						$offset = ($offset - 20);
					if($offset == 1)
					{
					    $offset = 0;
					}   
					 
					 if($parsedBody['checkedIn'] == 'true')
					 {
					 $s_val = explode(" ",$parsedBody['query']);
                     $query = "SELECT * FROM members LEFT JOIN member_check ON members.Member_ID = member_check.member_id WHERE (members.Member_ID LIKE '%".$s_val[0]."%' OR (members.First_Name LIKE '%".$s_val[0]."%' AND members.Last_Name LIKE '%".$s_val[1]."%') OR (members.First_Name LIKE '%".$s_val[1]."%' AND members.Last_Name LIKE '%".$s_val[0]."%'))   AND member_check.event_id='$evID' ORDER BY members.First_Name LIMIT $offset,20";   
					 $stmta = $db->query($query);
					 $allmember = $stmta->fetchAll(PDO::FETCH_OBJ); 
					 $k=0;
					    for($i=0;$i < count($allmember); $i++)
						{
						     $memberID = $allmember[$i]->Member_ID;
							$checkin = "SELECT * FROM member_check WHERE member_id='$memberID' AND `check_in` = 1 AND `confirm` = 1 AND event_id='$evID'";  
							$checkindata = $db->query($checkin);
							$checkinuser = $checkindata->fetchAll(PDO::FETCH_OBJ); 
								if($checkinuser){
									$member = "SELECT * FROM members WHERE member_id='$memberID'";  
							        $members = $db->query($member);
							        $memberss = $members->fetchAll(PDO::FETCH_OBJ); 
									$UserID = $checkinuser[0]->user_id;
									$user = "SELECT * FROM `users` WHERE `id` = '$UserID'"; 
									$usera = $db->query($user);
									$userdata = $usera->fetchAll(PDO::FETCH_OBJ);
									$flag = true; 
									$result[$k]['member'] =  $memberss[0];
									$result[$k]['date_checked'] = $checkinuser[0]->checkin_time; 
									$result[$k]['is_checked'] = $flag;
									$report_to = $userdata[0]->report_to;
									unset($userdata[0]->report_to);
									if($userdata[0] != ''){
										if($report_to != ''){
											$userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
										}
										else
										{
											$userdata[0]->report_to = $report_to;
										}  
									} 
									unset($userdata[0]->password);
									unset($userdata[0]->access_key);
									$result[$k]['user'] = $userdata[0] ; 
									$k++;
							    }      
						}	  
					 }
					 elseif($parsedBody['checkedIn'] == 'false')
					 {
						$s_val = explode(" ",$parsedBody['query']); 
					   $query = "SELECT * FROM members WHERE (Member_ID LIKE '%".$s_val[0]."%' OR (First_Name LIKE '%".$s_val[0]."%' AND Last_Name LIKE '%".$s_val[1]."%') OR (First_Name LIKE '%".$s_val[1]."%' AND Last_Name LIKE '%".$s_val[0]."%')) ORDER BY First_Name LIMIT $offset,20";  
					 $stmta = $db->query($query);
					 $allmember = $stmta->fetchAll(PDO::FETCH_OBJ);
					 $l=0; 
					    for($i=0;$i < count($allmember); $i++)
						{
						    $memberID = $allmember[$i]->Member_ID;
						 	$checkin = "SELECT * FROM member_check WHERE member_id='$memberID' AND event_id='$evID'";   
							$checkindata = $db->query($checkin);
							$checkinuser = $checkindata->fetchAll(PDO::FETCH_OBJ);
						 
							$member = "SELECT * FROM members WHERE member_id='$memberID'";  
							$members = $db->query($member);
							$memberss = $members->fetchAll(PDO::FETCH_OBJ);
							  if($checkinuser[0]->check_in != '1' && $checkinuser[0]->confirm != '1'){
									$UserID = $checkinuser[0]->user_id;
									$user = "SELECT * FROM `users` WHERE `id` = '$UserID'"; 
									$usera = $db->query($user);
									$userdata = $usera->fetchAll(PDO::FETCH_OBJ);
									$flag = false; 
									$result[$l]['member'] =  $memberss[0];
									$result[$l]['date_checked'] = $checkinuser[0]->checkin_time; 
									$result[$l]['is_checked'] = $flag;
									$report_to = $userdata[0]->report_to;
									unset($userdata[0]->report_to);
									if($userdata[0] != ''){
									  if($report_to != ''){
											$userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
										}
										else
										{
											$userdata[0]->report_to = $report_to;
										} 
									}
									unset($userdata[0]->password);
									unset($userdata[0]->access_key);
									$result[$l]['user'] = $userdata[0] ;
									$l++;
							  }        
						}	
						 
					 }
					 else
					 {
					   $s_val = explode(" ",$parsedBody['query']);
                       $query = "SELECT * FROM members WHERE (Member_ID LIKE '%".$s_val[0]."%' OR (First_Name LIKE '%".$s_val[0]."%' AND Last_Name LIKE '%".$s_val[1]."%') OR (First_Name LIKE '%".$s_val[1]."%' AND Last_Name LIKE '%".$s_val[0]."%')) ORDER BY First_Name LIMIT $offset,20";  
					 $stmta = $db->query($query);
					 $allmember = $stmta->fetchAll(PDO::FETCH_OBJ);
					 $m=0;  
					    for($i=0;$i < count($allmember); $i++)
						{
						    $memberID = $allmember[$i]->Member_ID;
						 	$checkin = "SELECT * FROM member_check WHERE member_id='$memberID' AND event_id='$evID'";   
							$checkindata = $db->query($checkin);
							$checkinuser = $checkindata->fetchAll(PDO::FETCH_OBJ); 
							$member = "SELECT * FROM members WHERE member_id='$memberID'";  
							$members = $db->query($member);
							$memberss = $members->fetchAll(PDO::FETCH_OBJ); 
							$UserID = $checkinuser[0]->user_id;
							$user = "SELECT * FROM `users` WHERE `id` = '$UserID'"; 
							$usera = $db->query($user);
							$userdata = $usera->fetchAll(PDO::FETCH_OBJ);
							$flag = false;
							if($checkinuser[0]->check_in == '1' && $checkinuser[0]->confirm == '1')
							{
							 $flag = 'true';
							}
							$result[$m]['member'] =  $memberss[0];
							$result[$m]['date_checked'] = $checkinuser[0]->checkin_time; 
							$result[$m]['is_checked'] = $flag;
							unset($userdata[0]->password);
							unset($userdata[0]->access_key);
							$report_to = $userdata[0]->report_to;
							unset($userdata[0]->report_to);
							if($userdata[0] != ''){
							  if($report_to != ''){
								 $userdata[0]->report_to = (int)str_replace(' ', '', $report_to);
							  }
							  else
							  {
								 $userdata[0]->report_to = $report_to;
							  } 
							}
							$result[$m]['user'] = $userdata[0] ;
							$m++;  
						} 
					 }
					 
				     if($result){
					    $response = $response->withJson($result, 200);  
					 }
					 else
					 {   
					    $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						$response = $response->withJson($data, 401);   
					 } 
			}
			else
			{
				$data = array('error_code'=>'E009','text' => "Event Doesn't Exist.");
				$response = $response->withJson($data, 401); 
			}  	 
			}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);  
		}   
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
 return $response; }); 
 
// Delete Picket
$app->get('/deletePicket[/{pk_id}]', function ($request, $response, $args) {  
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	  $pk_id = $args['pk_id']; 
	 if($Auth)
	 {  
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{
			$sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `picket_id` = $pk_id"; 
			$stmt = $db->query($sql);
			$location = $stmt->fetchAll(PDO::FETCH_OBJ);  
			if($location)
			{ 
			    $update = "UPDATE picket_duty SET is_deleted = '1' WHERE picket_id = '$pk_id'"; 
				$stmtu = $db->query($update);   
                $data = array();
                $response = $response->withJson($data, 200); 
			}
			else
			{
			  $data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
			   $response = $response->withJson($data, 401);  	
			 }
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);  
		} 
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }
	 
	 return $response;  
    
});

// Delete Location
$app->get('/deletePicketLocation[/{loc_id}]', function ($request, $response, $args) {  
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	  $loc_id = $args['loc_id']; 
	 if($Auth)
	 {  
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{
			$sql = "SELECT * FROM `location` WHERE `is_deleted` = '0' AND `location_id` = $loc_id"; 
			$stmt = $db->query($sql);
			$location = $stmt->fetchAll(PDO::FETCH_OBJ);  
			if($location)
			{ 
			    $update = "UPDATE location SET is_deleted = '1' WHERE location_id = '$loc_id'"; 
				$stmtu = $db->query($update);   
                $data = array();
                $response = $response->withJson($data, 200); 
			}
			else
			{
			  $data = array('error_code'=>'E0014','text' => "Location Doesn't Exist.");
			   $response = $response->withJson($data, 401);  	
			 }
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);  
		} 
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 } 
	 return $response;   
});
 
// Edit Member
$app->post('/editMember[/{member_id}]', function ($request, $response, $args) { 
	 $parsedBody = $request->getParsedBody();
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');  
	 $member_id = $args['member_id'];
	 if($Auth)
	 { 
		 if(!empty($parsedBody)){
			$checkauth = checkAuthKey($Auth);
			$accesstoken =  explode('-',$Auth); 
			$userID = $accesstoken[0];
			$db = getDB();
			if($checkauth)
			{ 
			    $sql = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'";
				$stmt = $db->query($sql);
				$member = $stmt->fetchAll(PDO::FETCH_OBJ);   
				if(!empty($member))
				{ 
					$mem = "SELECT email FROM members WHERE Email = '".$parsedBody['Email']."'";
					$memm = $db->query($mem);
					$memma = $memm->fetchAll(PDO::FETCH_OBJ);
					if($memma[0]->email == ''){
						  
				    $Company  = ''; 
					$Company = $parsedBody['Company_Prefix']; 
					$Last_Name = $parsedBody['Last_Name'];
					$First_Name = $parsedBody['First_Name'];  
					$Title_Code = $parsedBody['Title_Code'];
					$Title = $parsedBody['Title_Desc']; 
					$Mgrp_Code = $parsedBody['Mgrp_Code'];
					$Major_Group = $parsedBody['Major_Group'];
					$Org_Code = $parsedBody['Org_Code'];
					$organization = $parsedBody['organization'];
					$Dept_Code = $parsedBody['Dept_Code']; 
					$Department = $parsedBody['Department'];
					$Sect_Code = $parsedBody['Sect_Code'];
					$Section  = $parsedBody['Section']; 
					$TelHome1  = $parsedBody['TelHome1']; 
					$Email  = $parsedBody['Email'];
					$Active  = $parsedBody['Active'];
					$Home_Addr1  = $parsedBody['Home_Addr1'];
					$Home_Addr2  = $parsedBody['Home_Addr2'];
					$Home_City  = $parsedBody['Home_City'];
					$Home_State  = $parsedBody['Home_State'];
					$Home_Zip  = $parsedBody['Home_Zip'];
					$Adj_Hire_Date  = $parsedBody['Adj_Hire_Date']; 
					$updated_at = date('Y-m-d H:i:s');
				     
					$update = "UPDATE members SET Email = '$Email', Home_Addr1 = '$Home_Addr1',Home_Addr2 = '$Home_Addr2' ,TelHome1 = '$TelHome1', Home_City = '$Home_City', Home_State = '$Home_State', Home_Zip = '$Home_Zip', updated_at = '$updated_at',Company_Prefix = '$Company' WHERE Member_ID = '$member_id'";    
					 $api = new MCAPI('ad058f0ed354dfce4816872920403076-us9'); //6c895ef5e14e36ea4d7239d73de40a22-us13
					 $fname =  $member[0]->First_Name;
					 $lname =  $member[0]->Last_Name;
					 echo $fname;
					 $merge_vars = array('FIRSTNAME'=>$fname, 'LASTNAME'=>$lname);
					 $check = $api->listSubscribe('7ff5d4c3f9', $Email, $merge_vars ,'html', false); //9659c76df7
					 $stmtmember = $db->query($update);
				     $sql = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'"; 
				     $stmt = $db->query($sql);
				     $newmember = $stmt->fetchAll(PDO::FETCH_OBJ);
					 $cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$newmember[0]->Company_Prefix."'"; 
				     $cmpdata = $db->query($cmp);
				     $CompanyData = $cmpdata->fetchAll(PDO::FETCH_OBJ); 
					 if(!empty($CompanyData)){
					   $newmember[0]->Company = $CompanyData[0]->Company_Name;
					 }
					 unset($newmember[0]->Member_ID);
					 $response = $response->withJson($newmember[0], 201);  
				
				    }
					else
					{
						$data = array('error_code'=>'E0013','text' => "Email Already Exists. Please Choose Another.");
						$response = $response->withJson($data, 401);
					}
				} 
				else
				{
				  $data = array('error_code'=>'E0013','text' => "Member Doesn't Exist.");
				  $response = $response->withJson($data, 401);  	
				}
			}
			else
			{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
			}
		 }
		 else
		 {
			$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			$response = $response->withJson($data, 401);   
		 }
	 }
	 else
	 {
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);  
	 }

	 
	 return $response; 
});
	  
// get Picket picketsignups 
$app->post('/picketSignups[/{picked_id}]', function($request, $response, $args){ 
 
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
	$parsedBody = $request->getParsedBody();
	$Picket_Id = $args['picked_id'];
	$query = $parsedBody['query'];
	$page = $parsedBody['page'];
	$signedup = $parsedBody['signedup']; 
	if($Auth)
	{ 
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{  
			if($parsedBody['page'] == '' || $parsedBody['page'] == '0')
			{
				$page = 1;
			}
			else
			{
				$page = $parsedBody['page'];
			}
				$offset =  ($page * 20); 
				$offset = ($offset - 20);
			if($offset == 1)
			{
				$offset = 0;
			}  
		    $s_val = explode(" ",$query); 
			if($query != ''){ 
		    $sqla = "SELECT * FROM picket_registration LEFT JOIN members ON members.Member_ID = picket_registration.member_id WHERE (members.Member_ID LIKE '%".$s_val[0]."%' OR (members.First_Name LIKE '%".$s_val[0]."%' AND members.Last_Name LIKE '%".$s_val[1]."%') OR (members.First_Name LIKE '%".$s_val[1]."%' AND members.Last_Name LIKE '%".$s_val[0]."%'))   AND picket_registration.picket_id='$Picket_Id' ORDER BY members.First_Name LIMIT $offset,20";
				$member = $db->query($sqla);
				$members = $member->fetchAll(PDO::FETCH_OBJ);   
				if($signedup == ''){
					$j = '0';  
					$sqlm = "SELECT * FROM members WHERE (Member_ID LIKE '%".$s_val[0]."%' OR (First_Name LIKE '%".$s_val[0]."%' AND Last_Name LIKE '%".$s_val[1]."%') OR (First_Name LIKE '%".$s_val[1]."%' AND Last_Name LIKE '%".$s_val[0]."%')) ORDER BY First_Name LIMIT $offset,20 "; 
					$memberm = $db->query($sqlm);
					$members = $memberm->fetchAll(PDO::FETCH_OBJ);
					for($i =0 ;$i < count($members); $i++)
				    {
						$MemberId = $members[$i]->Member_ID;
						$preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'";
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);	
						$Uid = $pmembers[0]->user_id;
						$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
						$puser = $db->query($user);
						$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
						$muser = "SELECT * FROM `members` WHERE `Member_ID` = '$MemberId'";  
						$mpuser = $db->query($muser);
						$pusersm = $mpuser->fetchAll(PDO::FETCH_OBJ);
						$Lid = $pmembers[0]->location_id;
						$location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
						$location = $db->query($location);
						$locations = $location->fetchAll(PDO::FETCH_OBJ);
						$flag = 'false';   
						if($pmembers)
						{
							$flag = 'true'; 
						}
						$data[$j]->member = $pusersm[0];
						$data[$j]->is_signed_up = $flag;
						unset($pusers[0]->password);
						unset($pusers[0]->access_key);
						$data[$j]->user = $pusers[0];
						$dec = json_decode($pmembers[0]->event_week);
						$data[$j]->event_week  = $dec;
						$data[$j]->location  = $locations[0];
						$j++;
				    }   
					if($data){ 
						  $response = $response->withJson($data, 200);  
					}
					else
					 {
						 $data = array('error_code'=>'P001','text' => "No More Data Found!!!||");
						  $response = $response->withJson($data, 401);   
					 } 
				}
				elseif($signedup != ''){
					if($signedup == 'true')
					{ 
					$j= 0;
					   for($i =0 ;$i < count($members); $i++)
					   {    
					    $preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'"; 
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
					    $MemberId = $members[$i]->Member_ID; 
						$preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'"; 
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
						$Uid = $pmembers[0]->user_id;
						$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
						$puser = $db->query($user);
						$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
						$userm = "SELECT * FROM `members` WHERE `Member_ID` = '$MemberId'";  
						$puserm = $db->query($userm);
						$pusersm = $puserm->fetchAll(PDO::FETCH_OBJ);
						$Lid = $pmembers[0]->location_id;
					    $location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
						$location = $db->query($location);
						$locations = $location->fetchAll(PDO::FETCH_OBJ);
						$flag = 'false';   
						if($pmembers){  
						  $flag = 'true';
						  $data[$j]->member = $pusersm[0];
						  $data[$j]->is_signed_up = $flag; 
						  unset($pusers[0]->password);
						  unset($pusers[0]->access_key);
						  $data[$j]->user = $pusers[0];
						  $dec = json_decode($pmembers[0]->event_week);
						  $data[$j]->event_week  = $dec;
						  $data[$j]->location  = $locations[0];
						 $j++;
						} 
					   }
					     if($data){ 
					       $response = $response->withJson($data, 200);  
						 }else
						 {
							 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
							  $response = $response->withJson($data, 401);   
						 } 
					}
					elseif($signedup == 'false')
					{ 
						$da = "SELECT member_id FROM picket_registration WHERE picket_id='$Picket_Id'";
						$dad = $db->query($da);
						$amembers = $dad->fetchAll(PDO::FETCH_OBJ); 
						for($a = 0; $a < count($amembers); $a++){
						   $datam[$a] = $amembers[$a]->member_id;
						}
				     $s_val = explode(" ",$query);
				   $sqla = "SELECT * FROM members WHERE (Member_ID LIKE '%".$s_val[0]."%' OR (First_Name LIKE '%".$s_val[0]."%' AND Last_Name LIKE '%".$s_val[1]."%') OR (First_Name LIKE '%".$s_val[1]."%' AND Last_Name LIKE '%".$s_val[0]."%') AND Member_ID NOT IN ( '" . implode($datam, "', '") . "' )) ORDER BY First_Name LIMIT $offset,20 ";   
				     $member = $db->query($sqla);
				     $members = $member->fetchAll(PDO::FETCH_OBJ);  
					 $j = 0;
					 for($i =0 ;$i < count($members); $i++)
					 { 
						$MemberId = $members[$i]->Member_ID;
					    $preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'";
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
						 
						$Uid = $pmembers[0]->user_id;
						$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
						$puser = $db->query($user);
						$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
						$Lid = $pmembers[0]->location_id;
					    $location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
						$location = $db->query($location);
						$locations = $location->fetchAll(PDO::FETCH_OBJ);
						$muser = "SELECT * FROM `members` WHERE `Member_ID` = '$MemberId'";  
						$mpuser = $db->query($muser);
						$pusersm = $mpuser->fetchAll(PDO::FETCH_OBJ);
						$flag = 'false';   
							if($pmembers[0] == ''){  
							  $flag = 'false';
							  $data[$j]->member = $pusersm[0];
							  $data[$j]->is_signed_up = $flag; 
							  unset($pusers[0]->password);
							  unset($pusers[0]->access_key);
							  $data[$j]->user = $pusers[0];
							  $dec = json_decode($pmembers[0]->event_week);
							  $data[$j]->event_week  = $dec;
							  $data[$j]->location  = $locations[0];
							 $j++;
							}
					 }  
					  
					 if($data){ $response = $response->withJson($data, 200);  }else
					 {
						 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						 $response = $response->withJson($data, 401);   
					 }   	
					} 
				} 
			}
			else
			{ 
				$muser = "SELECT * FROM `members` ORDER BY First_Name LIMIT $offset,20";  
				$mpuser = $db->query($muser);
				$members = $mpuser->fetchAll(PDO::FETCH_OBJ);
						
			    $da = "SELECT member_id FROM picket_registration WHERE picket_id='$Picket_Id'";
				$dad = $db->query($da);
				$amembers = $dad->fetchAll(PDO::FETCH_OBJ);
				
				for($a = 0; $a < count($amembers); $a++){
				  $datam[$a] = $amembers[$a]->member_id;
				}  
				if($signedup == ''){ 
				$l = 0;
				for($i =0 ;$i < count($members); $i++)
					 { 
					$MemberId = $members[$i]->Member_ID;
				    $preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'";  
					$pmember = $db->query($preg);
					$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
					
					$Uid = $pmembers[0]->user_id;
					$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
					$puser = $db->query($user);
					$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
					
					$Lid = $pmembers[0]->location_id;
					$location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
					$location = $db->query($location);
					$locations = $location->fetchAll(PDO::FETCH_OBJ);
						
					$flag = 'false';   
					if($pmembers)
					{
						$flag = 'true'; 
					}
					$data[$l]->member = $members[$i];
					$data[$l]->is_signed_up = $flag;
					unset($pusers[0]->password);
					unset($pusers[0]->access_key);
					$data[$l]->user = $pusers[0];
					$dec = json_decode($pmembers[0]->event_week);
						 // $da = '';
//						  for($k=0; $k < count($dec); $k++)
//						  {
//							  $da .= $dec[$k].' ,';
//						  }
//						 // print_r($dec);
						  $data[$l]->event_week  = $dec;
						  $data[$l]->location  = $locations[0];
					$l++;
					 }
					if($data){ $response = $response->withJson($data, 200);  }else
					 {
						 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						  $response = $response->withJson($data, 401);   
					 } 
				}
				elseif($signedup != ''){
					if($signedup == 'true')
					{ 
				$sqla = "SELECT * FROM picket_registration RIGHT JOIN members ON members.Member_ID = picket_registration.member_id WHERE picket_registration.picket_id='$Picket_Id' ORDER BY picket_registration.member_id LIMIT $offset,20 ";  
				$member = $db->query($sqla);
				$members = $member->fetchAll(PDO::FETCH_OBJ);
				$l = 0;
				for($i =0 ;$i < count($members); $i++)
					 { 
						$MemberId = $members[$i]->Member_ID;
						$preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'";  
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
						
						$Uid = $pmembers[0]->user_id;
						$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
						$puser = $db->query($user);
						$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
						
						$prega = "SELECT * FROM `members` WHERE `Member_ID` = '$MemberId'";
						$pmr = $db->query($prega);
						$mems = $pmr->fetchAll(PDO::FETCH_OBJ);
						
						$Lid = $pmembers[0]->location_id;
						$location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
						$location = $db->query($location);
						$locations = $location->fetchAll(PDO::FETCH_OBJ);
							
						$flag = 'false';   
						if($pmembers)
						{
							$flag = 'true';
							$data[$l]->member = $mems;
							$data[$l]->is_signed_up = $flag;
							unset($pusers[0]->password);
							unset($pusers[0]->access_key);
							$data[$l]->user = $pusers[0];
							$dec = json_decode($pmembers[0]->event_week);
							//$da = '';
//							for($k=0; $k < count($dec); $k++)
//							{
//							  $da .= $dec[$k].' ,';
//							}
//							// print_r($dec);
							$data[$l]->event_week  = $dec;
							$data[$l]->location  = $locations[0];
							$l++; 
						 } 
					 }
					if($data){ $response = $response->withJson($data, 200);  }else
					 {
						 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						  $response = $response->withJson($data, 401);   
					 } 
				}
					elseif($signedup == 'false')
					{ 
					$da = "SELECT member_id FROM picket_registration WHERE picket_id='$Picket_Id'";
					$dad = $db->query($da);
					$amembers = $dad->fetchAll(PDO::FETCH_OBJ); 
					for($a = 0; $a < count($amembers); $a++){
					  $datam[$a] = $amembers[$a]->member_id;
					} 
				    $sqla = "SELECT * FROM members WHERE Member_ID NOT IN ( '" . implode($datam, "', '") . "' )  LIMIT $offset,20 ";  
				     $member = $db->query($sqla);
				     $members = $member->fetchAll(PDO::FETCH_OBJ);  
						$j = 0;
					 for($i =0 ;$i < count($members); $i++)
					 { 
						$MemberId = $members[$i]->Member_ID;
						$preg = "SELECT * FROM picket_registration WHERE picket_id='$Picket_Id' AND member_id='$MemberId'";
						$pmember = $db->query($preg);
						$pmembers = $pmember->fetchAll(PDO::FETCH_OBJ);
						 
						$Uid = $pmembers[0]->user_id;
						$user = "SELECT * FROM `users` WHERE `id` = '$Uid'";  
						$puser = $db->query($user);
						$pusers = $puser->fetchAll(PDO::FETCH_OBJ);
						 
						$Lid = $pmembers[0]->location_id;
					    $location = "SELECT * FROM `location` WHERE `location_id` = '$Lid'";  
						$location = $db->query($location);
						$locations = $location->fetchAll(PDO::FETCH_OBJ);
						 
							  $flag = 'false';
							  $data[$j]->member = $members[$i];
							  $data[$j]->is_signed_up = $flag; 
							  unset($pusers[0]->password);
							  unset($pusers[0]->access_key);
							  $data[$j]->user = $pusers[0];
							 // $dec = json_decode($pmembers[0]->event_week);
//							  $da = '';
//							  for($k=0; $k < count($dec); $k++)
//							  {
//								  $da .= $dec[$k].',';
//							  }
							  //print_r($dec);
							  $data[$j]->event_week  = $dec;
							  $data[$j]->location  = $locations[0];
							 $j++;
							 
					 }  
					  
					 if($data){ $response = $response->withJson($data, 200);  }else
					 {
						 $data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						  $response = $response->withJson($data, 401);   
					 }    	
					} 
				} 
			
			
			}
			
		}
		else
		{
			$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			$response = $response->withJson($data, 401);    
		}   
	}
	else
	{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
	}
	 
	return $response;  
	
});

// get Picket completedslots  
$app->post('/picketCompletedSlots', function($request, $response, $args){  
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
	$parsedBody = $request->getParsedBody();    //print_r($parsedBody);  
	$Picket_Id = $parsedBody['picket_id'];
	$Loc_Id = $parsedBody['location_id'];
	if($Auth)
	{ 
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{ 
		     if($Picket_Id != '' && $Loc_Id != '')
			 { 
			    // Picket 
			    $picket_duty = "SELECT * FROM `picket_duty` WHERE `picket_id` = '$Picket_Id'";  
				$picketd = $db->query($picket_duty);
				$picketds = $picketd->fetchAll(PDO::FETCH_OBJ); 
				
				$sqla = "SELECT * FROM `picket_registration` WHERE picket_id='$Picket_Id' and location_id='$Loc_Id' ";  
				$picket = $db->query($sqla);
				$pickets = $picket->fetchAll(PDO::FETCH_OBJ); 
				$total_signup = $picketds[0]->total_signup; 
				 $k =0;
				  for($i=0; $i < count($pickets); $i++)
				  {  
					$dec = json_decode($pickets[$i]->event_week);   
					for($j = 0; $j < count($dec); $j++)
					{     
					    $sqlaa = "SELECT count(*) AS total FROM `picket_registration` WHERE `picket_id` = '$Picket_Id' AND `event_week` LIKE '%$dec[$j]%'";  
						$picketa = $db->query($sqlaa);
						$picketsa = $picketa->fetchAll(PDO::FETCH_OBJ); 
						if($picketsa[0]->total >= $total_signup)
						{ 
						    $eventarray[$k]  =  $dec[$j];  
						    $k++; 
						}
					}  
				  }    
				$data = array_unique($eventarray); 
				$data = array_values($data);   
				//$data = json_encode($data);
				$apickets = $data; 
				if($apickets){
				    $response = $response->withJson($apickets, 200); 
				}
				else
				{
					$data = array();
					$response = $response->withJson($data, 200); 
				}
			 } 
		}
		else
		{
			$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			$response = $response->withJson($data, 401);    
		}   
	}
	else
	{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
	}
	 
	return $response;  
	
});
 
// Add picket signup
$app->post('/addPicketSignup[/{picked_id}]', function($request, $response, $args){ 
	$Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	$accesstoken =  explode('-',$Auth);     
	$parsedBody = $request->getParsedBody();
	$Pkid = $args['picked_id']; 
	$userID = $accesstoken[0];
	$member_id = $parsedBody['member_id'];
	$location_id = $parsedBody['location_id'];
	$event_week = $parsedBody['event_week'];
	if($member_id != '' && $location_id != '' && $event_week != ''){
	  if($Auth){
		 
		$checkauth = checkAuthKey($Auth); 
		$db = getDB();
		if($checkauth)
		{
			$sqla = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'";  
			$stmta = $db->query($sqla);
			$members = $stmta->fetchAll(PDO::FETCH_OBJ); 
			$memberEMAIL = $members[0]->Email;
			$firstname = $members[0]->First_Name;
			$lastname = $members[0]->Last_Name;
			if($members)
			{
				$sqll = "SELECT * FROM `location` WHERE `location_id` = '$location_id'";  
				$stmtl = $db->query($sqll);
				$locationl = $stmtl->fetchAll(PDO::FETCH_OBJ);
				$address = $locationl[0]->address;
				$city = $locationl[0]->city;
				$state = $locationl[0]->state;
				$zip = $locationl[0]->zip;
				$zipdata = '';
				if($zip)
				{
					$zipdata = ', '.$zip;
				}
				if($locationl)
				{ 
					$sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `status` = 1 AND `picket_id` = $Pkid"; 
					$stmt = $db->query($sql);
					$location = $stmt->fetchAll(PDO::FETCH_OBJ);  
					if($location)
					{   
					    $ev = json_decode($event_week); 
						$z =0; 
						for($i=0;$i<count($ev);$i++)
						{ 
							 $e =explode('|',$ev[$i]);
							 if(count($e) != '4')
							 {     
								 $z = 1;  			 
								 break;
							 }
							 else
							 {
								 // Date Vali date  
								 $e[0] =  date('M j, Y', strtotime($e[0])); 
								 $datecheck = checkdateformat($e[0]); 
								 if($datecheck == 'false')
								 {  
									 $z = 1;  
									 break;  
								 } 
								
								 // time slot  
								 $time = $e[1];
								 if($time)
								 {
									$t = explode('-',$time); 
									$t1 = strlen($str = ltrim($t[0], '0'));
									$t2 = strlen($str = ltrim($t[1], '0'));
									if($t1 <= '4' && $t1 >= '3' && $t2 <= '4' && $t2 >= '3')
									{ 
									   $dt =  $e[0].' '.$t[0];  
									   $start_date = $location[0]->start_date.' '.$location[0]->day_start.'AM'; 
									   if(strtotime($dt) < strtotime($start_date))
									   { 
										   $z= 1; 
										   break; 
									   }  
									}
									else
									{   
										$z= 1;
									    break;
									}
								 }
								 else
								 { 
									 $z= 1;
									 break;
								 }
								 
								 // location
         						$loc = $e[2]; 
								 
							    if(!is_numeric($loc) || $loc != $location_id)
								{  
									$z = 1; 
                                    break;
								}   
								 
								 // Week
         					     $week = $e[3];
								  $no_of_loc = $location[0]->no_of_weeks; 
							    if(!is_numeric($week) || $week > $no_of_loc)
								{  
								     
									$z= 1;
									break;
								} 
							 }
							  
						}   
						 
						 if($z == '1')
						 {
							$data = array('error_code'=>'E0015','text' => "Please Enter Correct Data Format.");
							$response = $response->withJson($data, 401); 
						 }
						 else
						 {
							  
						// Picket 
						$picket_duty = "SELECT * FROM `picket_duty` WHERE `picket_id` = '$Pkid'";  
						$picketd = $db->query($picket_duty);
						$picketds = $picketd->fetchAll(PDO::FETCH_OBJ); 
						$picketname = $picketds[0]->picket_name;
						$tot_weeks = $picketds[0]->no_of_weeks;
						$sqla = "SELECT * FROM `picket_registration` WHERE picket_id='$Pkid' and location_id='$location_id' ";  
						$picket = $db->query($sqla);
						$pickets = $picket->fetchAll(PDO::FETCH_OBJ); 
						$total_signup = $picketds[0]->total_signup; 
						 $k =0;
						  for($i=0; $i < count($pickets); $i++)
						  {  
							$dec = json_decode($pickets[$i]->event_week);   
							for($j = 0; $j < count($dec); $j++)
							{     
								$sqlaa = "SELECT count(*) AS total FROM `picket_registration` WHERE `picket_id` = 27 AND `event_week` LIKE '%$dec[$j]%'";  
								$picketa = $db->query($sqlaa);
								$picketsa = $picketa->fetchAll(PDO::FETCH_OBJ); 
								if($picketsa[0]->total >= $total_signup)
								{ 
									$eventarray[$k]  =  $dec[$j];  
									$k++; 
								}
							}  
						  }    
						$data = array_unique($eventarray); 
						$data = array_values($data);   
						$D =  json_decode($event_week);
						$E = 0;
						for($c=0;$c <count($D);$c++)
						{ 
						 if(in_array($D[$c], $data))
						 { 
							 $E = 1;  
						 } 
						}  
						
						if($E == '1')
						{
						 $data = array('error_code'=>'E0010','text' => "Signup limit has been reached at this location on the days selected.");
						 $response = $response->withJson($data, 401);   
						}
						else
						{
							$member = "SELECT * FROM `picket_registration` WHERE `member_id` = '$member_id'"; 
							$stmta = $db->query($member);
							$members = $stmta->fetchAll(PDO::FETCH_OBJ); 
							
							$ev = json_decode($event_week);  
							$df = 0;
							for($f=0;$f<count($ev);$f++)
							{
							  $e = explode('|',$ev[$f]); 
							  $ta = explode('-',$e[1]); 
							  $t1a = ltrim($ta[0], '0');
							  $t2a = ltrim($ta[1], '0');
							  $e[1] = $t1a.'-'.$t2a;
							  // $evnt[$df] = $e;
							  $evnt[$f] = $e[0].'|'.$t1a.'-'.$t2a.'|'.$e[2].'|'.$e[3];
							  $df++;
							} 
							$event_week =  json_encode($evnt);  
							if($members)
							{  
								$timestamp = date('Y-m-d H:i:s');
								$sqln = "UPDATE picket_registration SET user_id='$userID', event_week='$event_week', location_id='$location_id', timestamp='$timestamp', picket_id='$Pkid' WHERE member_id='$member_id'";  
								$stmtn = $db->query($sqln);  //$member_id
								if($memberEMAIL)
								{ 
								$membera = "SELECT * FROM `picket_registration` WHERE `member_id` = '$member_id'"; 
							    $stmtaa = $db->query($membera);
							    $evnt = $stmtaa->fetchAll(PDO::FETCH_OBJ); 
								 
								$picketdata1 =  str_replace('["'," ",$evnt[0]->event_week);
								$picketdata =  str_replace('"]'," ",$picketdata1);
								$picketdata = explode('","',$picketdata); 
								$endpicket = end($picketdata);
								$lastpicket =  explode('|',$endpicket); 
								$picketlast = end($lastpicket); 
								$html = ''; 
							    $html .= ' 
					<table style="padding:0px; margin:0px; width:640px;" cellpadding="0" cellspacing="0"><tbody><tr>
					<td style="padding:0px; margin:0px;"><img src="http://uwua1-2.org/checkin/includes/images/email/email-header.png"></td>
					</tr><tr>
					<td style="padding:20px; margin:0px; font-family:Arial, sans-serif; font-size:15px; text-align:left; line-height:20px; color:#222;">
					
					<p>Hello '. $firstname.' '.$lastname.',</p>
					<p>This is to confirm your registration for &quot;'.$picketname.'&quot; at the following location: '.$address .', '.$city.', '.$state.''.$zipdata .'</p>
					<p>The days you are registered for are:</p>';
					
					$html .= '<table style="padding:0px; margin:0px; border:1px solid #ddd; width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0"><tbody>';
					$a = 1;
					//print_r($event_week);
						  for($n = 1; $n <= $tot_weeks; $n++){
							  $a = $a+$n;
						      $html .= '<tr><td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px; background:#eee;" colspan="2"><strong>Week '.$n.'</strong></td></tr>' ;
							   //$event_weeka = explode("|",$event_week[$n-1]);
							  //  print_r($event_weeka);
							  $b = 1;   
							 for($k = 1; $k <= count($picketdata); $k++)
								{     
									// echo "<br/>";
									$picket =  explode('|',$picketdata[$k-1]); 
									// echo end($picket);
									//echo "<br/>";
									if($n == end($picket)) 
									{
										$html .= '<tr>
										<td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px;">'. date("l", strtotime($picket[0])).'  '.$picket[0]. '</td>
										<td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px; text-align:right;"> ' .strtolower($picket[1]).' </td>
										</tr>'; 
									} 
								}
							   $event_weeka = '';
						   }  
						
					$html .= '</tbody></table>';
					$html .=  '<p>If you have any questions contact us at <a href="tel:(212) 575-4400">(212) 575-4400</a></p>
					</td>
					</tr>
					</tbody></table>'; 
									require_once('../events/includes/PHPMailer-master/PHPMailerAutoload.php');
									$mail = new PHPMailer(); // create a new object
									$mail->IsSMTP(); // enable SMTP
									$mail->IsHTML(true);
									$mail->SMTPAuth = true; // authentication enabled
									$mail->Host = "smtp.gmail.com";
									$mail->Port = 465; // or 587
									$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
									$mail->Username = "vwdevteam@gmail.com"; // vwdevteam@gmail.com | uwua12events@gmail.com
									$mail->Password = "II7zEaA1dTJsySL"; //  II7zEaA1dTJsySL | local12events
									//$mail->SetFrom("Utility Workers Union of America", "webmaster@uwua.net");
									$mail->From = "webmaster@uwua.net";   
									$mail->FromName = "Utility Workers Union of America";
									$mail->Subject = "New Registration";
									$mail->Body = $html;
									$mail->AddAddress($memberEMAIL);
									$mailreturn = 0;
									if(!$mail->Send()) //Error
									{
										$mailreturn =  2 ; // Error
									} 
									else //Success
									{
										$mailreturn =  1 ; // Success
									}
					}
								 
								$member = "SELECT * FROM `picket_registration` WHERE `member_id` = '$member_id'  ORDER BY reg_id DESC";
								$stmta = $db->query($member);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);
								unlink($members[0]->event_week);
								$ev = json_decode($members[0]->event_week);
								//$ev = stripslashes($members[0]->event_week);  
								$members[0]->event_week = $ev;
								if($members[0]){$response = $response->withJson($members[0], 201);}
								else
								{ $data = array(); $response = $response->withJson($data, 201); }
							}
							else
							{ 
								$timestamp = date('Y-m-d H:i:s');
								$sqln = "INSERT INTO picket_registration (reg_id,member_id,user_id,picket_id,location_id,event_week,timestamp) VALUES (NULL, '$member_id', '$userID', '$Pkid', '$location_id', '$event_week', '$timestamp')";   
								$stmtn = $db->query($sqln); 
								$lastinsert = $db->lastInsertId(); 
								$member = "SELECT * FROM `picket_registration` WHERE `reg_id` = '$lastinsert'"; 
								$stmta = $db->query($member);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);
								unlink($members[0]->event_week);
								$ev = json_decode($members[0]->event_week);
								//$ev = stripslashes($members[0]->event_week); 
								
								if($memberEMAIL)
								{ 
								$membera = "SELECT * FROM `picket_registration` WHERE `member_id` = '$member_id'"; 
							    $stmtaa = $db->query($membera);
							    $evnt = $stmtaa->fetchAll(PDO::FETCH_OBJ); 
								 
								$picketdata1 =  str_replace('["'," ",$evnt[0]->event_week);
								$picketdata =  str_replace('"]'," ",$picketdata1);
								$picketdata = explode('","',$picketdata); 
								$endpicket = end($picketdata);
								$lastpicket =  explode('|',$endpicket); 
								$picketlast = end($lastpicket); 
								$html = ''; 
							    $html .= ' 
					<table style="padding:0px; margin:0px; width:640px;" cellpadding="0" cellspacing="0"><tbody><tr>
					<td style="padding:0px; margin:0px;"><img src="http://uwua1-2.org/checkin/includes/images/email/email-header.png"></td>
					</tr><tr>
					<td style="padding:20px; margin:0px; font-family:Arial, sans-serif; font-size:15px; text-align:left; line-height:20px; color:#222;">
					
					<p>Hello '. $firstname.' '.$lastname.',</p>
					<p>This is to confirm your registration for &quot;'.$picketname.'&quot; at the following location: '.$address .', '.$city.', '.$state.''.$zipdata .'</p>
					<p>The days you are registered for are:</p>';
					
					$html .= '<table style="padding:0px; margin:0px; border:1px solid #ddd; width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0"><tbody>';
					$a = 1;
					//print_r($event_week);
						  for($n = 1; $n <= $tot_weeks; $n++){
							  $a = $a+$n;
						      $html .= '<tr><td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px; background:#eee;" colspan="2"><strong>Week '.$n.'</strong></td></tr>' ;
							   //$event_weeka = explode("|",$event_week[$n-1]);
							  //  print_r($event_weeka);
							  $b = 1;   
							 for($k = 1; $k <= count($picketdata); $k++)
								{     
									// echo "<br/>";
									$picket =  explode('|',$picketdata[$k-1]); 
									// echo end($picket);
									//echo "<br/>";
									if($n == end($picket)) 
									{
										$html .= '<tr>
										<td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px;">'. date("l", strtotime($picket[0])).'  '.$picket[0]. '</td>
										<td style="padding:5px; margin:0px; border:1px solid #ddd; font-family:Arial, sans-serif; font-size:15px; text-align:right;"> ' .strtolower($picket[1]).' </td>
										</tr>'; 
									} 
								}
							   $event_weeka = '';
						   }  
						
					$html .= '</tbody></table>';
					$html .=  '<p>If you have any questions contact us at <a href="tel:(212) 575-4400">(212) 575-4400</a></p>
					</td>
					</tr>
					</tbody></table>'; 
									require_once('../events/includes/PHPMailer-master/PHPMailerAutoload.php');
									$mail = new PHPMailer(); // create a new object
									$mail->IsSMTP(); // enable SMTP
									$mail->IsHTML(true);
									$mail->SMTPAuth = true; // authentication enabled
									$mail->Host = "smtp.gmail.com";
									$mail->Port = 465; // or 587
									$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
									$mail->Username = "vwdevteam@gmail.com"; // vwdevteam@gmail.com | uwua12events@gmail.com
									$mail->Password = "II7zEaA1dTJsySL"; //  II7zEaA1dTJsySL | local12events
									//$mail->SetFrom("Utility Workers Union of America", "webmaster@uwua.net");
									$mail->From = "webmaster@uwua.net";   
									$mail->FromName = "Utility Workers Union of America";
									$mail->Subject = "New Registration";
									$mail->Body = $html;
									$mail->AddAddress($memberEMAIL);
									$mailreturn = 0;
									if(!$mail->Send()) //Error

									{
										$mailreturn =  2 ; // Error
									} 
									else //Success
									{
										$mailreturn =  1 ; // Success
									}
					}
								
								$members[0]->event_week = $ev;  
								if($members[0]){
									$response = $response->withJson($members[0], 201); 
								}
								else
								{
									$data = array();
									$response = $response->withJson($data, 201);
								}  
							}  
						}   
						 } 
					}
					else
					{
					   $data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
					   $response = $response->withJson($data, 401);  	
					} 
				}
				else
				{
					$data = array('error_code'=>'E0014','text' => "Please Enter Value.");
					$response = $response->withJson($data, 401);  
				}
			}
			else
			{
			  $data = array('error_code'=>'E0013','text' => "Member doesn't exist");
			  $response = $response->withJson($data, 401);  
			} 
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);  
		}  
	
	  }
	  else
	  { 
	    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		$response = $response->withJson($data, 401);   
	  }
	}else
	{
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401);  
	}
	 return $response;
}); 

// Picket Attendence picketattendancesummary
$app->post('/picketAttendanceSummary[/{picked_id}]', function($request, $response, $args){  
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
	$parsedBody = $request->getParsedBody();    //print_r($parsedBody); 
	$Pkid = $args['picked_id']; 
	$week = $parsedBody['week']; 
	$location = $parsedBody['location'];  
	if($Auth)
	{ 
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{  
			$sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `status` = 1 AND `picket_id` = $Pkid"; 
			$stmt = $db->query($sql);
			$sdate = $stmt->fetchAll(PDO::FETCH_OBJ);
		    $NO_OF_WEEK = $sdate[0]->no_of_weeks;  
			if($sdate)
			{
				
				if(!is_numeric($week))
				{ 
					$data = array('error_code'=>'E002','text' => "Please Enter Value.");
					$response = $response->withJson($data, 401);  
				}
				else
				{
					if($week <= $NO_OF_WEEK){
					    
				$addDay = 0;
			$addsingle = 1; 
			for($i=1; $i<=8;$i++)
			{
				$wdate[$i] = date('M j, Y', strtotime($sdate[0]->start_date . "+".$addDay." day")); 
				$addDay = $addDay + 7; 
			}
			//print_r($wdate);
			
			$addDay = 0; 
			for($i=1; $i<= $sdate[0]->no_of_weeks ;$i++)
			{
				 $wdatea[$i] = date('M j, Y', strtotime($sdate[0]->start_date . "+".$addDay." day")); 
				 $addDay = $addDay + 7;
				 $dataweek[$i] = date("W", strtotime($wdatea[$i]." +1 day"));
			}
			//print_r($dataweek);
			
			if(isset($week))
			  $date = $wdate[$week];
			else	
			  $date = $wdate['1'];
			
			$date_for_sunday = array();
			$totaldate = array();
			for($k=1; $k<= count($wdatea) ;$k++)
			{
			 $timestamp = mktime( 0, 0, 0, 1, 1, date('Y',strtotime($sdate[0]->start_date))) + ( $dataweek[$k] * 7  * 24 * 60 * 60 );
			 $timestamp_for_sunday = $timestamp - 86400 * ( date( 'N', $timestamp ) );
			 $date_for_sunday[$i] = date( 'Y-m-d', $timestamp_for_sunday );
			 $totaldate[] = $date_for_sunday[$i];
			}  
			// print_r($totaldate);
			
			 
			 $timestamp = strtotime($date);
			 $day = date('l', $timestamp);
			 $loop =	date('N', strtotime($date));
			 if($week == 1){
				  if($loop == 7)
				  {
					  $loop = 0 ;
				  }
			   $sloop = $loop +1;
			 }
			 else
			  $sloop = 0;
			
			$day_name = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
			if($week == 1){
				$sloop = $sloop-1; 
			}
			 $flag = 0; 
			 $addsingle = 0;
			 $adddate = 0; 
			  $J= 0;
			  for($l=$sloop,$k=1; $l <= 6; $l++,$k++ )
			  {
				    if($week != 1)
					{  
					  $date = $totaldate[$week - 1]; 
					}
					 
					 if($flag == 0){ 
					    $datedisplay =  date('M j, Y', strtotime($date . " +".$addsingle." day"));
					    $flag = 1;
					 }
					else
					 { 
					   $datedisplay =  date('M j, Y', strtotime($date . " +".$addsingle." day")); 
					 } 
					    $locID = '';
					   if($location)
					   {
					     $locID = 'and location_id ='.$location;
					   }
					   $locationdata = '';
					   $sqll = "SELECT * FROM location WHERE is_deleted = 0 AND status = 1 $locID"; 
					   $stmta = $db->query($sqll);
					   $locationdata = $stmta->fetchAll(PDO::FETCH_OBJ);
					    
					   for($c=0; $c < count($locationdata); $c++)
			           { 
					       $LID = $locationdata[$c]->location_id;
						   $search_string = date('M j, Y', strtotime($date . "+".$addsingle." day")).'|'.'%'.'|'.$locationdata[$c]->location_id.'|'.$week; 
						   $data = "SELECT count(0) as total FROM picket_checkin WHERE picket_id='$Pkid' AND location_id ='$LID' AND confirm=1 AND check_in=1 AND date = '".date_format(date_create($date . "+".$addsingle." day"), 'm/d/Y')."'"; 
						   $checkin = $db->query($data);
						   $checkincount = $checkin->fetchAll(PDO::FETCH_OBJ);
						   
						   
						    $search_string = date('M j, Y', strtotime($date . "+".$addsingle." day")).'|'.'%'.'|'.$locationdata[$c]->location_id.'|'.$week; 
						   $dataa = "SELECT count(*) as totalreg FROM picket_registration WHERE picket_id='$Pkid' AND event_week LIKE '%".$search_string."%'"; 
						   $regis = $db->query($dataa);
						   $regcount = $regis->fetchAll(PDO::FETCH_OBJ); 
						   $test[$c] = array("location"=>$locationdata[$c],"checked_in"=>$checkincount[0]->total,"signed_up"=>$regcount[0]->totalreg); 
					   }   
					   $A = date('Y-m-d',strtotime($datedisplay));
					   $alldata[$J]->$A = $test;	   
					   $J++; $addsingle++;
			  }  
			  if($location){
				 $search_stringa = '%|'.'%'.'|'.$location.'|'.$week;
			  }
			  else
			  {
				 $search_stringa = '%|'.'%'.'|'.'%'.'|'.$week;
			  }
			  $reg = "SELECT count(*) as totaldata FROM `picket_registration` WHERE `picket_id` = '$Pkid' AND `event_week` LIKE '%".$search_stringa."%'"; 
			  $regda = $db->query($reg);
			  $countreg = $regda->fetchAll(PDO::FETCH_OBJ);       
			  $tdata->signed_up =  $countreg[0]->totaldata;
			  $tdata->signed_up_dates = $alldata;
			 
			  if($tdata){ 
			       $response = $response->withJson($tdata, 200);
			  }else
			  {
				  $response = $response->withJson(array(), 200);
			  }
			    
					}
					else
					{
						$data = array('error_code'=>'E002','text' => "Please Enter Value.");
						$response = $response->withJson($data, 401); 
					}
				}
			
			}
			else
			{
				$data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
				$response = $response->withJson($data, 401);
			} 
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);    
		}   
	}
	else
	{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
	} 
	 
	return $response;  
	
});
 
 //picketattendancebydate date format : 'Y-m-d'
$app->post('/picketAttendanceByDate[/{picked_id}]', function($request, $response, $args){  
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
	$parsedBody = $request->getParsedBody();    //print_r($parsedBody); 
	$Pkid = $args['picked_id']; 
	$week = $parsedBody['week'];
	$location = $parsedBody['location']; 
	$start_hour = $parsedBody['start_hour'];
	$checked_in = $parsedBody['checked_in'];
	$query = $parsedBody['query'];
	$date = $parsedBody['date'];
	$ldate = $parsedBody['date'];
	$page = $parsedBody['page'];
	if($Auth != '')
	{  
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{  
			$sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `status` = 1 AND `picket_id` = $Pkid"; 
			$stmt = $db->query($sql);
			$sdate = $stmt->fetchAll(PDO::FETCH_OBJ);
		    $NO_OF_WEEK = $sdate[0]->no_of_weeks;  
			if($sdate)
			{ 
			    $Z= 0;
				// check week
				if(!is_numeric($week)){ 
					$Z = 1;
				} 
				// check location 
				if(!is_numeric($location)){ 
					$Z = 1;
				} 
				// check no of week
				if($week > $NO_OF_WEEK){
				   $Z =1;     
				}
				// check date
				 $checkdate = checkattendanceformat($date); // date forma 'Y-m-d'
				if($date == '' || $checkdate != 'true'){ 
					$Z = 1;
				} 
				
				if($start_hour)
				 { 
					$t = explode('-',$start_hour);  
					$aa = str_replace(" ",'',ltrim($t[0], '0'));
					$bb = str_replace(" ",'',ltrim($t[1], '0')); 
					 
					if(strlen($aa) > '5' || strlen($aa) < '3' || strlen($bb) > '5' || strlen($bb) < '3')
					{ 
						   $Z= 1;   
					}
				 }
				  
				if($Z == '0')
				{ 
				    // Pageination code
					   if($parsedBody['page'] == '')
					   {
						   $page = 1;
					   }   
					   else
					   {
						  $page = $parsedBody['page'];  
					   }
				   
						$offset =  ($page * 20); 
						$offset = ($offset - 20);
						//$offset = $offset + 1;
						if($offset == 1)
						{
							$offset = 0;
						}
					 
						if($start_hour == '')
						{
							$start_hour = 'all';
						}
						
						if($start_hour == 'all' || $start_hour == '')
						{
							$search_string = date('M j, Y', strtotime($date)).'|'.'%'.'|'.$location.'|'.$week;
						}
						else
						{
							$start_hour = $aa.'-'.$bb;
							$search_string = date('M j, Y', strtotime($date)).'|'.$start_hour.'|'.$location.'|'.$week;
						}
						
						if($start_hour == '')
						{
							$start_hour = '';
						}  
						 $edate = date('m/d/Y',strtotime($date)); //06/16/2016
						 $date = date('m/d/Y',strtotime($date));  //06/16/2016 
				        $st = '';
						if($checked_in == 'true' || $start_hour != ''){
							$st = "AND checkin_timeslot='$start_hour'";
						}
						$ZA = '';
				        if($checked_in == 'false' || $checked_in == ''){
						  if($query)
						  {	 
							$s_val = explode(" ",$query); 
							if($start_hour == 'all' || $start_hour == '')
							{
								$ZA = '1';
								$fl  = '';
								if($checked_in == 'false')
								{
									$fl = "AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1)";
								}
								$mquery = "SELECT * FROM members WHERE (Member_ID like '%".$s_val[0]."%' OR (First_Name like '%".$s_val[0]."%' AND Last_Name like '%".$s_val[1]."%') OR (First_Name like '%".$s_val[1]."%' AND Last_Name like '%".$s_val[0]."%')) AND Member_ID IN (SELECT member_id FROM picket_registration WHERE picket_id=".$Pkid." AND location_id=".$location." AND event_week LIKE '%".$search_string."%') ".$fl." ORDER BY First_Name LIMIT $offset, 20";  //AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1)
							}
							else
							{
								$ZA = '2';
								$mquery = "SELECT * FROM members WHERE (Member_ID like '%".$s_val[0]."%' OR (First_Name like '%".$s_val[0]."%' AND Last_Name like '%".$s_val[1]."%') OR (First_Name like '%".$s_val[1]."%' AND Last_Name like '%".$s_val[0]."%')) AND Member_ID IN (SELECT member_id FROM picket_registration WHERE picket_id=".$Pkid." AND  location_id=".$location." AND event_week LIKE '%".$search_string."%') AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1 AND week=".$week.") ORDER BY First_Name LIMIT $offset, 20"; //AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1)
							} 		
						 }
						 else
						 {
							if($start_hour == 'all' || $start_hour == '')
							{
								$ZA = '3';
								$de =''; $ad = '';
								if($checked_in == 'false')
								{
									$de = "AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1)";
								}
								
								if($checked_in != 'false' && $checked_in != '' || $start_hour == 'all'){
								$ad = "AND (event_week LIKE '%".date('M j, Y', strtotime($date)).'|_________|'.$location.'|'.$week."%' OR event_week LIKE '%".date('M j, Y', strtotime($date)).'|_______|'.$location.'|'.$week."%' OR event_week LIKE '%".date('M j, Y', strtotime($date)).'|________|'.$location.'|'.$week."%')";                      
								 }
								$mquery = "SELECT * FROM members WHERE Member_ID IN (SELECT member_id FROM picket_registration WHERE picket_id=".$Pkid." ".$ad." AND location_id=".$location." ) ".$de." ORDER BY First_Name LIMIT $offset, 20"; //AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1) 
							}
							else
							{
								$ZA = '4';
								$vb = "";
								if($checked_in == 'false')
								{
								$vb = "AND Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1)";
							    }
								$mquery = "SELECT * FROM members WHERE Member_ID IN (SELECT member_id FROM picket_registration WHERE location_id=".$location." AND picket_id=".$Pkid." AND event_week LIKE '%".$search_string."%') ".$vb." ORDER BY First_Name LIMIT $offset, 20"; //
							}
						}   
					 }
					 else
					 { 
					    $s_val = explode(" ",$query); 
					    if($start_hour == 'all' || $start_hour == '')
						{
							$ZA = '5';
							$mquery = "SELECT * FROM members WHERE (Member_ID like '%".$s_val[0]."%' OR (First_Name like '%".$s_val[0]."%' AND Last_Name like '%".$s_val[1]."%') OR (First_Name like '%".$s_val[1]."%' AND Last_Name like '%".$s_val[0]."%')) AND Member_ID IN (SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1 AND week=".$week.") ORDER BY First_Name LIMIT $offset, 20";
						}
						else
						{ 
						$ZA = '6';
						$mquery = "SELECT * FROM members WHERE Member_ID IN (SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1 AND week=".$week." AND check_in=1 $st) ORDER BY First_Name LIMIT $offset, 20"; 
						}
					 }  
					 
					 $data = $db->query($mquery);
			         $serchdata = $data->fetchAll(PDO::FETCH_OBJ); 
					 if($serchdata){
						 $df = array();
						 for($i=0;$i < count($serchdata); $i++)
						 {
						   $Member_ID = $serchdata[$i]->Member_ID;
						   $alldata[$i]->member = $serchdata[$i];
						   $alldata[$i]->week = $week;
						   $alldata[$i]->date = $ldate;
						   $sqlm = "SELECT * FROM picket_checkin WHERE member_id = '$Member_ID' AND picket_id=".$Pkid." AND location_id=".$location." AND date = '".$edate."' AND confirm=1 AND check_in=1";   
							$stmtm = $db->query($sqlm);
							$mem = $stmtm->fetchAll(PDO::FETCH_OBJ); 
							//echo "SELECT * FROM picket_registration WHERE member_id = '$Member_ID' AND picket_id=".$Pkid." AND location_id=".$location."";
							$sqla = "SELECT * FROM picket_registration WHERE member_id = '$Member_ID' AND picket_id=".$Pkid." AND location_id=".$location."";   
							//echo "<br/>";
							$stmta = $db->query($sqla);
							$mema = $stmta->fetchAll(PDO::FETCH_OBJ);
							 //print_r($mema);
							$flag = 'false'; $check = ''; $slot ='';
							if($mem)
							{
								$flag = 'true';
								$check = $mem[0]->checkin_time;
								$slot = $mem[0]->checkin_timeslot;
							}
						   $alldata[$i]->is_checked = $flag;
						   $alldata[$i]->date_checked = $check;
						   for($k=0;$k < count($mema);$k++)
						   {
								$d = json_decode($mema[$k]->event_week); 
								$p=0;
								for($z=0;$z<count($d);$z++){
									 $f = explode("|",$d[$z]);
									 //echo $f[0];
									// echo "| ".date('M j, Y',strtotime($ldate));
									 if($f[0] == date('M j, Y',strtotime($ldate)))
									 {
										 if($week == $f[3]){
										  $n[$p] = $f[1]; 
										  $df = $n[$p];
										  $p++;
										 }
									 }
								} 
							}
						   $alldata[$i]->time_slot = $n;
						   $n = ''; 
						 } 
						    
						    if($start_hour == '' || $start_hour == 'all')
							{
								$q = "SELECT count(*) as checkin_total FROM `picket_checkin` WHERE picket_id=".$Pkid." AND location_id =".$location." AND check_in = 1 AND confirm = 1 AND date = '".date('m/d/Y', strtotime($date))."'"; 
							}
							else
							{
								$q = "SELECT count(*) as checkin_total FROM `picket_checkin` WHERE picket_id=".$Pkid." AND location_id =".$location." AND check_in = 1 AND confirm = 1 AND date = '".date('m/d/Y', strtotime($date))."' AND checkin_timeslot='".$start_hour."'";
							}
							 // for total signup
							  	 
							if($checked_in == 'false' || $checked_in == '' ){ 
							  $to =  "SELECT COUNT(*) AS total_signup FROM `picket_registration` WHERE`picket_id` = ".$Pkid." AND `location_id` = ".$location." AND `event_week` LIKE '%".$search_string."%'";   
							}else
							{
							  $to =  "SELECT COUNT(*) AS total_signup FROM `picket_registration` WHERE Member_ID NOT IN ( SELECT member_id FROM picket_checkin WHERE picket_id=".$Pkid." AND location_id=".$location." AND date = '".date('m/d/Y', strtotime($date))."' AND confirm=1) AND `picket_id` = ".$Pkid." AND `location_id` = ".$location." AND `event_week` LIKE '%".$search_string."%'"; 
							}
							 $tosign = $db->query($to);
			                 $sign_data = $tosign->fetchAll(PDO::FETCH_OBJ);
						     //$SignNo = $sign_data[0]->total_signup;  
					         $cdata = $db->query($q);
			                 $chk_data = $cdata->fetchAll(PDO::FETCH_OBJ);
							 if($checked_in == 'false' || $checked_in == '')
							 {
								$da->total = count($alldata); 
							 } 
							 else
							 {
								$da->total = $chk_data[0]->checkin_total;
							 }
						     
						     $da->signed_up_members = $alldata;
					         $response = $response->withJson($da, 200); 
					 }
					 else
					 { 
						$d->total = 0;
						$d->signed_up_members = array();
						$response = $response->withJson($d, 200); 
					 }
				}
				else
				{
					$data = array('error_code'=>'E002','text' => "Please Enter Value.");
					$response = $response->withJson($data, 401); 
				} 
			}
			else
			{
				$data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
				$response = $response->withJson($data, 401);
			} 
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);    
		}   
	}
	else
	{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
	}
	 
	return $response;  
	
});

// picketcheckin
$app->post('/picketCheckin[/{picked_id}]', function($request, $response, $args){  
	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
	$parsedBody = $request->getParsedBody();    //print_r($parsedBody); 
	$Pkid = $args['picked_id']; 
	$week = $parsedBody['week'];
	$location_id = $parsedBody['location_id']; 
	$member_id = $parsedBody['member_id'];
	$time_slot = $parsedBody['time_slot'];
	$checked_in = $parsedBody['checked_in']; 
	$date = $parsedBody['date']; 
	if($Auth)
	{ 
		$checkauth = checkAuthKey($Auth);
		$accesstoken =  explode('-',$Auth); 
		$userID = $accesstoken[0];
		$db = getDB();
		if($checkauth)
		{  
			$sql = "SELECT * FROM `picket_duty` WHERE `is_deleted` = '0' AND `status` = 1 AND `picket_id` = $Pkid"; 
			$stmt = $db->query($sql);
			$checkpicket = $stmt->fetchAll(PDO::FETCH_OBJ);
		    $NO_OF_WEEK = $checkpicket[0]->no_of_weeks;  
			if($checkpicket)
			{ 
			    $Z= 0;
				// check week
				if(!is_numeric($week)){  
					$Z = 1;
				} 
				// check location 
				if(!is_numeric($location_id)){  
					$Z = 1;
				}
				// check Time Slot
				if($time_slot == ''){  
					$Z = 1;
				} 
				// check no of week
				if($week > $NO_OF_WEEK){ 
				   $Z =1;     
				}   
				// check date
				 $checkdate = checkattendanceformat($date);
				if($date == '' && $checkdate != 'true'){ 
					$Z = 1;
				}  
				 
				 if($time_slot)
				 { 
					$t = explode('-',$time_slot);  
					$t1 = strlen(str_replace(" ",'',ltrim($t[0], '0')));    
					$t2 = strlen(str_replace(" ",'',ltrim($t[1], '0')));   
					//echo $t1.' '.$t2;
					if($t1 > '5' || $t1 < '3' || $t2 > '5' || $t2 < '3')
					{ 
						   $Z= 1;   
					}  
				 }
				 $time_slot = str_replace(" ",'',ltrim($t[0], '0')).'-'.str_replace(" ",'',ltrim($t[1], '0'));
				$f = date('Y-m-d'); 
				$dcheck = 0;
				if(strtotime($date) > strtotime($f) || strtotime($date) < strtotime($f)){
				   $Z= 1;   
				   $dcheck = 1;
				}
				
			    $chkstr = date('M j, Y', strtotime($date)).'|'.$time_slot.'|'.$location_id.'|'.$week;
				     $checkin = "SELECT * FROM `picket_registration` WHERE member_id = '$member_id' AND `picket_id` = '$Pkid' AND `location_id` = '$location_id' AND `event_week` LIKE '%$chkstr%'"; 
					 $ckdata = $db->query($checkin);
					 $checkindata = $ckdata->fetchAll(PDO::FETCH_OBJ);
					 $ckid = 0;
					 if(!$checkindata)
					 {
						$ckid = 1;
						$Z= 1;
					 } 
				  
				if($Z == '0')
				{
					$sqll = "SELECT * FROM `location` WHERE `is_deleted` = '0' AND `location_id` = '$location_id'"; 
					$stmtl = $db->query($sqll);
					$locationdata = $stmtl->fetchAll(PDO::FETCH_OBJ);
					if($locationdata){
					   $mem = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'"; 
					$stmtm = $db->query($mem);
					$membercheck = $stmtm->fetchAll(PDO::FETCH_OBJ);
						if($membercheck){
							 
							  // check checkin true/ false
							   $check = '';
							   if($checked_in == 'true')
							   {
								  $check = '`check_in` = 1 , `confirm` = 1'; 
							   }
							   else
							   {
								  $check = '`check_in` = 0 , `confirm` = 0';  
							   }
							   
						  $sqla = "SELECT * FROM `picket_checkin` WHERE `location_id` = '$location_id' AND `picket_id` = '$Pkid' AND `member_id` = '$member_id'";
						  $stmta = $db->query($sqla);
						  $conformcheck = $stmta->fetchAll(PDO::FETCH_OBJ);	
						  if($conformcheck)
						  { 
						     //$t = date('H:i:s',time()); 
							  $d = date_create($date.''.date("H:i:s"));
							  $checkin_time = date_format($d,"Y/m/d H:i:s");
							//$checkin_time = date('Y-m-d h:i:s',strtotime($date));
							//echo $checkin_time; 
							$date = date('m/d/Y',strtotime($date)); 
							$time_slot = ltrim($time_slot, '0');
							$sqlu = "UPDATE picket_checkin SET week= '$week', date= '$date', checkin_time='$checkin_time' , checkin_timeslot= '$time_slot', $check  WHERE picket_id='$Pkid' AND member_id='$member_id' AND `location_id` = '$location_id'";  
							$stmt = $db->query($sqlu);     
							$sqlp = "SELECT * FROM `picket_checkin` WHERE picket_id='$Pkid' AND member_id='$member_id' AND `location_id` = '$location_id'";
							$stmtp = $db->query($sqlp);
							$picketcheckin = $stmtp->fetchAll(PDO::FETCH_OBJ); 
							if($picketcheckin)
							{
								$memberID = $picketcheckin[0]->member_id;
								$sqlm = "SELECT * FROM `members` WHERE `Member_ID` = '$memberID' ";
							    $stmtm = $db->query($sqlm);
							    $membercheckin = $stmtm->fetchAll(PDO::FETCH_OBJ);
								$alldata->member = $membercheckin[0];
								$alldata->week = $picketcheckin[0]->week;
								$flag = 'false';$checktime = '';$timeslot = '';
								if($picketcheckin[0]->confirm != 0)
								{
									$flag = 'true';
									$checktime = $picketcheckin[0]->checkin_time;
									$timeslot = $picketcheckin[0]->checkin_timeslot;
								}
								$alldata->date = date('Y-m-d',strtotime($picketcheckin[0]->date));
								$alldata->is_checked = $flag;
								$alldata->date_checked = $checktime;
								$alldata->time_slot = $timeslot;
								$response = $response->withJson($alldata, 200);
							}       
						  }
						  else
						  {
							   $check = '';
							   if($checked_in == 'true')
							   {
								  $check = "'1','1'"; 
							   }
							   else
							   {
								  $check = "'0','0'";  
							   }
							//$checkin_time = date('Y-m-d h:i:s',strtotime($date));
							  $d = date_create($date.''.date("H:i:s"));
							  $checkin_time = date_format($d,"Y/m/d H:i:s");
							$date = date('m/d/Y',strtotime($date)); 
							$time_slot = ltrim($time_slot, '0');
							$sql = "insert into picket_checkin(picket_id,week,date,location_id,member_id,check_in,confirm,user_id,checkin_time,checkin_timeslot) values('$Pkid','$week','$date','$location_id','$member_id',$check,'$userID','$checkin_time','$time_slot')";   
							$stmt = $db->query($sql);    
							$lastinsert = $db->lastInsertId(); 
							$sqlp = "SELECT * FROM `picket_checkin` WHERE `id` = '$lastinsert'";
							$stmtp = $db->query($sqlp);
							$picketcheckin = $stmtp->fetchAll(PDO::FETCH_OBJ); 
							$memberID = $picketcheckin[0]->member_id;
							if($picketcheckin){
								$sqlm = "SELECT * FROM `members` WHERE `Member_ID` = '$memberID' ";
							    $stmtm = $db->query($sqlm);
							    $membercheckin = $stmtm->fetchAll(PDO::FETCH_OBJ);
								$alldata->member = $membercheckin[0];
								$alldata->week = $picketcheckin[0]->week;
								$flag = 'false';$checktime = '';$timeslot = '';
								if($picketcheckin[0]->confirm != 0)
								{
									$flag = 'true';
									$checktime = $picketcheckin[0]->checkin_time;
									$timeslot = $picketcheckin[0]->checkin_timeslot;
								}
								$alldata->date = date('Y-m-d',strtotime($picketcheckin[0]->date));
								$alldata->is_checked = $flag;
								$alldata->date_checked = $checktime;
								$alldata->time_slot = $timeslot;
								$response = $response->withJson($alldata, 200);
							} 
						  } 
						
						} 
						else
						{
							$data = array('error_code'=>'E0013','text' => "Member Doesn't Exist.");
							$response = $response->withJson($data, 401);
						}
					}
					else
					{
						$data = array('error_code'=>'E0014','text' => "Location Doesn't Exist.");
				        $response = $response->withJson($data, 401);
					}
				}
				else
				{				
					if($dcheck == 1)
					{
					  $data = array('error_code'=>'E0018','text' => "Check in is only allowed to be done in the same day.");
					  $response = $response->withJson($data, 401);  
					}
					else if($ckid == 1)
					{
					   $data = array('error_code'=>'E0019','text' => "Invalid Check In.");
					   $response = $response->withJson($data, 401); 
					}
					else
					{
					  $data = array('error_code'=>'E002','text' => "Please Enter Value.");
					  $response = $response->withJson($data, 401); 
					 }
				}  
			}
			else
			{
				  $data = array('error_code'=>'E0012','text' => "Picket Duty Doesn't Exist.");
				  $response = $response->withJson($data, 401); 
			} 
		}
		else
		{
		   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
		   $response = $response->withJson($data, 401);    
		}   
	}
	else
	{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
	}
	return $response;  
});

// User reset password
$app->get('/userResetPassword[/{email}]', function ($request, $response, $args) { 
		$Auth = $request->getHeaderLine('Default-Admin-Token'); 
		if($Auth){
			$check = checkapilogin($Auth);
			if($check)
			{
				if(!empty($args)){
					$email = $args['email']; 
					$sql = "SELECT id,access_key FROM `users` WHERE email = '$email'";
					$db = getDB();
					$stmt = $db->query($sql);
					$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
					$userID = $users[0]->id; 
					if($userID)
					{ 
						$alphabet = "abcdefghijklmnopqrstuwxyz0123456789";
						$pass = array(); //remember to declare $pass as an array
						$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
						for ($i = 0; $i < 6; $i++) {
						  $n = rand(0, $alphaLength);
						  $pass[] = $alphabet[$n];
						}
						$random = generateRandomString();
						$random = $userID.'-'.$random;
						$pass = implode($pass);  
						$sqlU = "UPDATE users SET password=MD5('$pass'),access_key= '$random'  WHERE email='$email'"; 
						$stmt = $db->query($sqlU);
						// multiple recipients
						$mail = new PHPMailer(); // create a new object
						$mail->IsSMTP(); // enable SMTP
						$mail->SMTPAuth = true; // authentication enabled
						$mail->Host = "smtp.gmail.com";
						$mail->Port = 465; // or 587
						$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
						$mail->Username = "vwdevteam@gmail.com"; //  vwdevteam@gmail.com   ,  uwua12events@gmail.com
						$mail->Password = "II7zEaA1dTJsySL"; //   II7zEaA1dTJsySL  ,   local12events      |  / II7zEaA1dTJsySL
						//$mail->SetFrom("Utility Workers Union of America", "webmaster@uwua.net");
						$mail->From = "webmaster@uwua.net";
						$mail->FromName = "Utility Workers Union of America";
						$mail->Subject = "Password Reset";
						$mail->Body = "Your New Password is: $pass";
						$mail->AddAddress($email);
						$mail->Send();   
						$data = array($A);
						$response = '{}'; 
					}
					else
					{
						$data = array('error_code'=>'E0016','text' => "Email Doesn't Exist.");
			            $response = $response->withJson($data, 401);  
					} 
				}
				else
				{
					$data = array('error_code'=>'E002','text' => "Please Enter Value.");
			        $response = $response->withJson($data, 401);  
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Default-Admin-Token");
				$response = $response->withJson($data, 401);  
			}
			$headers = $request->getHeaders();    
		}
		else
		{
			$data = array('error_code'=>'E008','text' => "Please Enter Token");
		    $response = $response->withJson($data, 401); 
		} 
		return $response; 	
	 
});

// meta (check app verson end point)
$app->get('/getMetadata[/{key}]', function ($request, $response, $args) { 
	 $metaAuth = $request->getHeaderLine('Default-Member-Token'); 
	 $defaultAuth = $request->getHeaderLine('Default-Admin-Token');
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');
	 $member_id = $args['member_id']; 
	 $platform = $args['key'];
	 if($metaAuth) //Default-Member-Token
	 {  
	       $check = checkmemberlogin($metaAuth); 
		   if($check)
			{   
				$sql = "SELECT * FROM `metadata` WHERE metakey = '$platform'";
				$db = getDB();
				$stmt = $db->query($sql);
				$platformdata = $stmt->fetchAll(PDO::FETCH_OBJ);  
				//$platformdata = $datas[0]->id; 
				if($platformdata)
				{  
					unset($platformdata[0]->id);
					unset($platformdata[0]->metakey);
					$response = $response->withJson($platformdata[0], 200);
				}
				else
				{
					$data = array('error_code'=>'E0017','text' => "Key Doesn't Exist.");
					$response = $response->withJson($data, 401);  
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Member Default Token.");
				$response = $response->withJson($data, 401); 
			}  
	 }
	 elseif($defaultAuth) //Default-Admin-Token
	 {
	       $check = checkapilogin($defaultAuth); 
		   if($check)
			{  
				$sql = "SELECT * FROM `metadata` WHERE metakey = '$platform'";
				$db = getDB();
				$stmt = $db->query($sql);
				$platformdata = $stmt->fetchAll(PDO::FETCH_OBJ);  
				//$platformdata = $datas[0]->id; 
				if($platformdata)
				{  
					unset($platformdata[0]->id);
					unset($platformdata[0]->metakey);
					$response = $response->withJson($platformdata[0], 200);
				}
				else
				{
					$data = array('error_code'=>'E0017','text' => "Key Doesn't Exist.");
					$response = $response->withJson($data, 401);  
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Default Token.");
				$response = $response->withJson($data, 401); 
			} 
	 }
	 elseif($Auth) //Admin-Auth-Token
	 { 
		$data = array('error_code'=>'E006','text' => "Enter token.");
		$response = $response->withJson($data, 401); 
  
	 }
	 else
	 {
		$data = array('error_code'=>'E006','text' => "Enter Token");
		$response = $response->withJson($data, 401); 
	 }
	 
	 return $response;  
});

// GET Member By Member ID 
$app->get('/getMemberById[/{member_id}]', function ($request, $response, $args) {  
	 $memberAuth = $request->getHeaderLine('Default-Member-Token'); 
	 $defaultAuth = $request->getHeaderLine('Default-Admin-Token');
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');
	 $member_id = $args['member_id']; 
	 if($memberAuth != '') //Default-Member-Token
	 {  
	       $check = memberdefaultcheck($memberAuth); 
		   if($check)
			{   
				$sql = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'"; 
				$db = getDB();
				$stmt = $db->query($sql);   
				$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ);
				
				$cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$memberdata[0]->ID_Prefix."'";
				$comdata = $db->query($cmp);
				$cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
				
				if($memberdata)
				{  
				    if($cdata[0]->Company_Name != ''){
				       $memberdata[0]->Company = $cdata[0]->Company_Name;
					}else
					{
						$memberdata[0]->Company = '';
				    }
					unset($memberdata[0]->Member_ID);
					$response = $response->withJson($memberdata[0], 200);
				}
				else
				{
				    $data = array('error_code'=>'E0012','text' => "Member Doesn't Exist.");
				    $response = $response->withJson($data, 401);  	
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Member Default Token.");
				$response = $response->withJson($data, 401); 
			}  
	 }
	 elseif($defaultAuth != '') //Default-Admin-Token
	 {
	    $data = array('error_code'=>'E006','text' => "Enter token.");
		$response = $response->withJson($data, 401);  
	 }
	 elseif($Auth) //Admin-Auth-Token
	 {
	       $check = checkAuthKey($Auth);
		   if($check)
			{  
				$sql = "SELECT * FROM `members` WHERE `Member_ID` = '$member_id'"; 
				$db = getDB();
				$stmt = $db->query($sql);   
				$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ);
				
				$cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$memberdata[0]->ID_Prefix."'";
				$comdata = $db->query($cmp);
				$cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
				
				if($memberdata)
				{
					if($cdata[0]->Company_Name != ''){
				       $memberdata[0]->Company = $cdata[0]->Company_Name;
					}else
					{
						$memberdata[0]->Company = '';
				    }
					$response = $response->withJson($memberdata[0], 200);
				}
				else
				{
				    $data = array('error_code'=>'E0012','text' => "Member Doesn't Exist.");
				    $response = $response->withJson($data, 401);  	
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct User Token.");
				$response = $response->withJson($data, 401); 
			}
	 }
	 else
	 {
		$data = array('error_code'=>'E006','text' => "Enter Token");
		$response = $response->withJson($data, 401); 
	 }
	 
	 return $response;  
    
});

// add union member and point (add by admin)
$app->post('/addMemberByAdmin', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
	$Auth = $request->getHeaderLine('Admin-Auth-Token');
	$db = getDB();
	if($Auth != '') //Auth-Token
	{
		$check = checkAuthKey($Auth);
		$UserID = explode('-',$Auth);
		if($check)
		{
			$user = "SELECT * FROM users WHERE id = '".$UserID[0]."'";
			$userdata = $db->query($user);
			$udata = $userdata->fetchAll(PDO::FETCH_OBJ); 
			 
			if($udata[0]->role != 3)
			{
				$Member_ID = $parsedBody['Emp_No'];
				$First_Name = $parsedBody['First_Name'];
				$Last_Name = $parsedBody['Last_Name'];
				$Company = strtoupper($parsedBody['Company_Prefix']);
				
				$mem = "SELECT * FROM members WHERE Emp_No = '".$Member_ID."' AND Company_Prefix = '".$Company."'";
				$memdata = $db->query($mem);
				$mdata = $memdata->fetchAll(PDO::FETCH_OBJ);
				
				$cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$Company."'";
				$comdata = $db->query($cmp);
				$cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
	 
					if(empty($mdata)){
					   if($First_Name != '' && $Last_Name != '' && $Member_ID != ''){
						  
						 if(!empty($cdata)){
						   
						    $memID = $cdata[0]->ID_Prefix.''.str_pad($Member_ID, $cdata[0]->Emp_No_Length, '0', STR_PAD_LEFT);
							$MID = ltrim($Member_ID,0);
							$sql = "INSERT INTO members (Member_ID,Company_Prefix,Emp_No,Last_Name,First_Name,New_Union_Member) VALUES ('".$memID."','".$Company."','".$MID."','".$Last_Name."','".$First_Name."',1)";
							$stmt = $db->query($sql); 
							$lastinsert = $db->lastInsertId();
							
							$mem = "SELECT * FROM members WHERE id = '".$lastinsert."'";
							$memdata = $db->query($mem);
							$mdata = $memdata->fetchAll(PDO::FETCH_OBJ);
							$mdata[0]->Company = $cdata[0]->Company_Name;
							unset($mdata[0]->Member_ID);
							$response = $response->withJson($mdata[0], 200);
						 }
						 else{
							 $data = array('error_code'=>'E002','text' => "Enter Correct Company Name.");
						     $response = $response->withJson($data, 401);
						 }
					   } 
				       else
				       {
						  
						$data = array('error_code'=>'E002','text' => "Please Enter Value.");
						$response = $response->withJson($data, 401); 
				
				       }
					}
					else
					{
						$data = array('error_code'=>'E0011','text' => "Member Already Exists. Please Choose Another.");
			            $response = $response->withJson($data, 401);  
					}
			}
			else
			{
				$data = array('error_code'=>'E0022','text' => "Not Enough Permissions.");
				$response = $response->withJson($data, 401);
			}
		}
		else
		{
			$data = array('error_code'=>'E003','text' => "Please Enter Correct User Token.");
			$response = $response->withJson($data, 401); 
		}
	}
	else
	{
	    $data = array('error_code'=>'E006','text' => "Enter Token");
	    $response = $response->withJson($data, 401); 
	}
	return $response;
});



/*Code For New Member __________________________*/ 
// GET Member By Member ID Picket

// Check Member Default Code.
function memberdefaultcheck($MAuth){
	
	$MDfault = 'M-0123456789'; 
    if($MAuth == $MDfault)
	{
		return true;
	}
	else
	{
		return false;
	}
}

// Add Member Code
$app->post('/addMemberData', function ($request, $response, $args) { 
	$MAuth = $request->getHeaderLine('Default-Member-Token');  
	$parsedBody = $request->getParsedBody();
	if($MAuth)
	{ 
		$checkmdefautl = memberdefaultcheck($MAuth); 
		$db = getDB();
		if($checkmdefautl)
		{ 
		    $sqlm = "SELECT * FROM member_data WHERE Emp_No = '".$parsedBody['Emp_No']."' AND Company_Prefix = '".$parsedBody['Company_Prefix']."' AND email = '".$parsedBody['Email']."'";  
			$stmtm = $db->query($sqlm);
			$onem = $stmtm->fetchAll(PDO::FETCH_OBJ);
			if(!$onem)
			{
				$flag = 0;
				$ID = strtoupper($parsedBody['Emp_No']);
				$Password = md5($parsedBody['Password']);
				$First_Name = $parsedBody['First_Name'];
				$Last_Name = $parsedBody['Last_Name']; 
				$Email = $parsedBody['Email'];
				$Phone = $parsedBody['Phone'];
				$Street_Address = $parsedBody['Street_Address'];
				$Apt_Suite_Room = $parsedBody['Apt_Suite_Room'];
				$City = $parsedBody['City'];
				$State = $parsedBody['State'];
				$Zip = $parsedBody['Zip_Code']; 
				$Company = $parsedBody['Company_Prefix'];
				$Report_To = $parsedBody['Report_To']; 
				$Role = $parsedBody['Role']; 
				if(!$ID)
				{
					$flag = 1;
				}
				if(!$Password)
				{
					$flag = 1;
				}
				if(!$First_Name)
				{
					$flag = 1;
				}
				if(!$Last_Name)
				{
					$flag = 1;
				}
				if(!$Email)
				{
					$flag = 1;
				}
				if(!$Company)
				{
					$flag = 1;
				}
				
		        $cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$Company."'";
				$comdata = $db->query($cmp);
				$cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
				if(!empty($cdata)){  
				  if($flag == 0){
					$memID = $cdata[0]->ID_Prefix.''.str_pad($ID, $cdata[0]->Emp_No_Length, '0', STR_PAD_LEFT);
					$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$charactersLength = strlen($characters);
					$randomString = '';
					$length = 20;
					for ($i = 0; $i < $length; $i++) {
						$randomString .= $characters[rand(0, $charactersLength - 1)];
					} 
				     $memauthkey = $memID.'-'.$randomString;
					$mem = "SELECT email FROM member_data WHERE email = '".$parsedBody['Email']."'";
					$memm = $db->query($mem);
					$memma = $memm->fetchAll(PDO::FETCH_OBJ);
					if($memma[0]->email == ''){
					 	if($Role == 'Steward')
					 	{ 
					     	if($Report_To != '')
						 	{
							$usr = "SELECT * FROM users WHERE id ='$Report_To' AND userrole='agent'";
							$ustmt = $db->query($usr);
							$users = $ustmt->fetchAll(PDO::FETCH_OBJ);
							if(!empty($users)){
							    $sql = "insert into member_data(Member_ID,Emp_No,Password,First_Name,Last_Name,Email,Phone,Street_Address,Apt_Suite_Room,City,State,Zip_Code,Company_Prefix,Member_Auth_Token,Role,Report_To) values('$memID','$ID','$Password','$First_Name','$Last_Name','$Email','$Phone','$Street_Address','$Apt_Suite_Room','$City','$State','$Zip','$Company','$memauthkey','$Role','$Report_To')"; 
								$stmt = $db->query($sql); 
								$lastinsert = $db->lastInsertId();     
								$sqla = "SELECT * FROM member_data WHERE id = '$lastinsert'";  
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{  
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}
								    $rMember = $members[0]->Report_To; 
									if($members[0]->Report_To != ''){
										$sqlu = "SELECT * FROM users  WHERE id = '".$rMember."'";
										$stmtu = $db->query($sqlu);  
										$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
										unset($Users[0]->password);
										unset($Users[0]->access_key);
										unset($Users[0]->report_to);
									    $members[0]->Report_To_User = $Users[0];
									}
									unset($members[0]->Report_To);
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								}
							}
							else
							{
								$data = array('error_code'=>'E009','text' => "The User To Whom Report To Doesn't Exist.");
								$response = $response->withJson($data, 401);
							}
						 }
						 	else
						 	{
							$data = array('error_code'=>'E002','text' => "Please Enter Report To User.");
							$response = $response->withJson($data, 401); 
						 }  
					 }
					 	else if($Role == 'Member')
					 	{  
					     if($Report_To == '')
						 {   
								$sql = "insert into member_data(Member_ID,Emp_No,Password,First_Name,Last_Name,Email,Phone,Street_Address,Apt_Suite_Room,City,State,Zip_Code,Company_Prefix,Member_Auth_Token,Role) values('$memID','$ID','$Password','$First_Name','$Last_Name','$Email','$Phone','$Street_Address','$Apt_Suite_Room','$City','$State','$Zip','$Company','$memauthkey','$Role')";
								$stmt = $db->query($sql); 
								$lastinsert = $db->lastInsertId();    
								 $sqla = "SELECT * FROM member_data WHERE id = '$lastinsert'";  //$lastinsert 
								  
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{  
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									unset($members[0]->Report_To);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								} 
						 }
						 else
						 { 
							$data = array('error_code'=>'E002','text' => "Member Role Doesn't Report To Another Member.");
							$response = $response->withJson($data, 401); 
						 }
					 
					 }
					 	else
					 	{ 
					   
					   if($Role == '' && $Report_To != ''){
					       $data = array('error_code'=>'E002','text' => "Must Select A Role.");
					       $response = $response->withJson($data, 401);
					   }
					   else
					   {
					       $Role = 'Member';
					       $sql = "insert into member_data(Member_ID,Emp_No,Password,First_Name,Last_Name,Email,Phone,Street_Address,Apt_Suite_Room,City,State,Zip_Code,Company_Prefix,Member_Auth_Token,Role) values('$memID','$ID','$Password','$First_Name','$Last_Name','$Email','$Phone','$Street_Address','$Apt_Suite_Room','$City','$State','$Zip','$Company','$memauthkey','$Role')"; 
								$stmt = $db->query($sql); 
								$lastinsert = $db->lastInsertId();     
								$sqla = "SELECT * FROM member_data WHERE id = '$lastinsert'";  
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{  
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}
								    $rMember = $members[0]->Report_To; 
									if($members[0]->Report_To != ''){
										$sqlu = "SELECT * FROM users  WHERE id = '".$rMember."'";
										$stmtu = $db->query($sqlu);  
										$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
										unset($Users[0]->password);
										unset($Users[0]->access_key);
										unset($Users[0]->report_to);
									    $members[0]->Report_To_User = $Users[0];
									}
									unset($members[0]->Report_To);
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								}
					   }
					 
					 }	
					 }
					 else
						 {
							$data = array('error_code'=>'E0013','text' => "Email Already Exists. Please Choose Another.");
							$response = $response->withJson($data, 401);
						 }			
				  } 
				  else
				  {
					$data = array('error_code'=>'E005','text' => "Please Fill All Required Fields.");
					$response = $response->withJson($data, 401);
				  }
				}
				else
				{
					$data = array('error_code'=>'E002','text' => "Enter Correct Company Name.");
					$response = $response->withJson($data, 401);
				}
			}
			else
			{
			      $data = array('error_code'=>'E0013','text' => "This Member Already Exist.");
				  $response = $response->withJson($data, 401);	
			} 
		}
		else
		{
			$data = array('error_code'=>'E005','text' => "Invalid Member Default Token");
			$response = $response->withJson($data, 401);
		}    
	}
	else
	{
		$data = array('error_code'=>'E005','text' => "Enter Member Default Token");
		$response = $response->withJson($data, 401);
	}
 	return $response;  
	 
});

// Member Login
$app->post('/memberLogin', function ($request, $response, $args) { 
	$MAuth = $request->getHeaderLine('Default-Member-Token');  
	$parsedBody = $request->getParsedBody();
	if($MAuth)
	{ 
		$checkmdefautl = memberdefaultcheck($MAuth); 
		$db = getDB();
		if($checkmdefautl)
		{ 
		   $UserName = strtoupper($parsedBody['UserName']);
		   $password = md5($parsedBody['Password']);
		   if(!filter_var($UserName, FILTER_VALIDATE_EMAIL) === false) { 
			    $sqla = "SELECT * FROM member_data WHERE Email = '$UserName' AND Password = '$password'";
				$stmta = $db->query($sqla);
				$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
				if($members)
				{
					if(!$members[0]->Member_Auth_Token)
					{ 
						$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						$length = 20;
						for ($i = 0; $i < $length; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						} 
					    $memauthkey = $members[0]->Member_ID.'-'.$randomString;
					    $sqlu = "Update member_data set Member_Auth_Token = '$memauthkey' WHERE Email = '$UserName' AND Password = '$password'";
				        $stmta = $db->query($sqlu);	 
					}  
					$sqla = "SELECT * FROM member_data WHERE Email = '$UserName' AND Password = '$password'";
					$stmta = $db->query($sqla);
					$members = $stmta->fetchAll(PDO::FETCH_OBJ);
					unset($members[0]->Password);
					//$members[0]->Zip = $members[0]->Zip_Code;
					// unset($members[0]->Zip_Code);
					$report_to = $members[0]->Report_To;
					unset($members[0]->Report_To);
					$members[0]->Report_To = (int)str_replace(' ', '', $report_to);
					$response = $response->withJson($members[0], 201);   
				}
				else
				{ 
					$data = array('error_code'=>'E001','text' => "Enter Correct Username and Password.");
					$response = $response->withJson($data, 401);
				}       
		   }
		   else
		   {
			    $sqla = "SELECT * FROM member_data WHERE Member_ID = '$UserName' AND Password = '$password'";
				$stmta = $db->query($sqla);
				$members = $stmta->fetchAll(PDO::FETCH_OBJ);  
				if($members)
				{
					if(!$members[0]->Member_Auth_Token)
					{ 
						$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						$length = 20;
						for ($i = 0; $i < $length; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						} 
					    $memauthkey = $members[0]->Member_ID.'-'.$randomString;
					    $sqlu = "Update member_data set Member_Auth_Token = '$memauthkey' WHERE Member_ID = '$UserName' AND Password = '$password'";
				        $stmta = $db->query($sqlu);	 
					}  
					$sqla = "SELECT * FROM member_data WHERE Member_ID = '$UserName' AND Password = '$password'";
					$stmta = $db->query($sqla);
					$members = $stmta->fetchAll(PDO::FETCH_OBJ);
					unset($members[0]->Password);
					//$members[0]->Zip = $members[0]->Zip_Code;
					//unset($members[0]->Zip_Code);
					$response = $response->withJson($members[0], 201);   
				}
				else
				{ 
					$data = array('error_code'=>'E001','text' => "Enter Correct Username and Password.");
					$response = $response->withJson($data, 401);
				}  
		   }
		    /*
		   else
		   {
			   $UserName = $parsedBody['UserName'];
		       $password = md5($parsedBody['Password']);
			   $UserName = 'CE000'.$UserName;
			   $sqla = "SELECT * FROM member_data WHERE Member_ID = '$UserName' AND Password = '$password'";
				$stmta = $db->query($sqla);
				$members = $stmta->fetchAll(PDO::FETCH_OBJ);  
				if($members)
				{
					if(!$members[0]->Member_Auth_Token)
					{ 
						$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						$length = 20;
						for ($i = 0; $i < $length; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						} 
					    $memauthkey = $members[0]->Member_ID.'-'.$randomString;
					    $sqlu = "Update member_data set Member_Auth_Token = '$memauthkey' WHERE Member_ID = '$UserName' AND Password = '$password'";
				        $stmta = $db->query($sqlu);	 
					}  
					$sqla = "SELECT * FROM member_data WHERE Member_ID = '$UserName' AND Password = '$password'";
					$stmta = $db->query($sqla);
					$members = $stmta->fetchAll(PDO::FETCH_OBJ);
					unset($members[0]->Password);
					$response = $response->withJson($members[0], 201);   
				}
				else
				{ 
					$data = array('error_code'=>'E001','text' => "Enter Correct Username and Password.");
					$response = $response->withJson($data, 401);
				}     
		   }
		   */
		}
		else
		{
			$data = array('error_code'=>'E005','text' => "Invalid Member Default Token");
			$response = $response->withJson($data, 401);
		}    
	}
	else
	{
		$data = array('error_code'=>'E005','text' => "Enter Member Default Token");
		$response = $response->withJson($data, 401);
	}
 	return $response;   
});
 
// Get Member By ID
$app->get('/getMemberDataById[/{member_id}]', function ($request, $response, $args) {  
	 
	 $memberDefault = $request->getHeaderLine('Default-Member-Token');  
	 $MemberAuth = $request->getHeaderLine('Member-Auth-Token');
	 $member_id = $args['member_id']; 
	 if($memberDefault) //Default-Member-Token
	 {  
	   $check = memberdefaultcheck($memberDefault); 
	   if($check)
		{  
			$sql = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'"; 
			$db = getDB();
			$stmt = $db->query($sql);   
			$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ); 
			if($memberdata)
			{   
			    $cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$memberdata[0]->ID_Prefix."'";
				$comdata = $db->query($cmp);
				$cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
				if($cdata[0]->Company_Name != ''){
				   $memberdata[0]->Company = $cdata[0]->Company_Name;
				}else
				{
				   $memberdata[0]->Company = '';
				}  
			    unset($memberdata[0]->Password);
				unset($memberdata[0]->Member_ID);
				$report_to = $memberdata[0]->Report_To;
				unset($memberdata[0]->Report_To);
				if($report_to != ''){
					$memberdata[0]->Report_To = (int)str_replace(' ', '', $report_to);
				}
				else
				{
					$memberdata[0]->Report_To = $report_to;
				}
				
				$response = $response->withJson($memberdata[0], 200);
			}
			else
			{
				$data = array('error_code'=>'E0012','text' => "Member Doesn't Exist.");
				$response = $response->withJson($data, 401);  	
			}
		}
		else
		{
			$data = array('error_code'=>'E003','text' => "Please Enter Correct Token.");
			$response = $response->withJson($data, 401); 
		}  
	 } 	
	 elseif($MemberAuth)
	 {
		$sql = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id' AND `Member_Auth_Token` = '$MemberAuth'"; 
		$db = getDB();
		$stmt = $db->query($sql);   
		$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ);
		if($memberdata)
		{  
			$sql = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'"; 
			$stmt = $db->query($sql);   
			$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ);
			if($memberdata)
			{  
			    unset($memberdata[0]->Password);
				unset($memberdata[0]->Member_ID);
				$report_to = $memberdata[0]->Report_To;
				unset($memberdata[0]->Report_To);
				if($report_to != ''){
					$memberdata[0]->Report_To = (int)str_replace(' ', '', $report_to);
				}
				else
				{
					$memberdata[0]->Report_To = $report_to;
				}
				$response = $response->withJson($memberdata[0], 200);
			}
			else
			{
				$data = array('error_code'=>'E0012','text' => "Member Doesn't Exist.");
				$response = $response->withJson($data, 401);  	
			}
		}
		else
		{
			$data = array('error_code'=>'E003','text' => "Please Enter Correct Member Auth Token.");
			$response = $response->withJson($data, 401); 
		}    
	 } 
	 else
	 {
		$data = array('error_code'=>'E003','text' => "Enter Correct Token.");
		$response = $response->withJson($data, 401);  
	 }
	 return $response;  
    
});
 
$app->get('/memberResetPassword[/{email}]', function ($request, $response, $args) { 
		$MDAuth = $request->getHeaderLine('Default-Member-Token'); 
		$email = $args['email']; 
		$db = getDB();
		if($MDAuth != ''){
			$check = memberdefaultcheck($MDAuth);
			if($check)
			{  
				$sql = "SELECT * FROM `member_data` WHERE Email = '$email'"; 
				$stmt = $db->query($sql);
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);  
				$memberID = $users[0]->Member_ID; 
				if($memberID)
				{ 
						$alphabet = "abcdefghijklmnopqrstuwxyz0123456789";
						$pass = array(); //remember to declare $pass as an array
						$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
					for ($i = 1; $i < 6; $i++) {
					  $n = rand(0, $alphaLength);
					  $pass[] = $alphabet[$n];
					}
						$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						$length = 20;
					for ($i = 0; $i < $length; $i++) {
						$randomString .= $characters[rand(0, $charactersLength - 1)];
					} 
						$random = $memberID.'-'.$randomString;
						$mpass = implode($pass);
						$pass = md5(implode($pass));    
						$sqlU = "UPDATE member_data SET Password='$pass',Member_Auth_Token= '$random'  WHERE Email='$email'";
						$stmt = $db->query($sqlU);
						// multiple recipients
						$mail = new PHPMailer(); // create a new object
						$mail->IsSMTP(); // enable SMTP
						$mail->SMTPAuth = true; // authentication enabled
						$mail->Host = "smtp.gmail.com";
						$mail->Port = 465; // or 587
						$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
						$mail->Username = "vwdevteam@gmail.com"; //  vwdevteam@gmail.com   ,  uwua12events@gmail.com
						$mail->Password = "II7zEaA1dTJsySL"; //   II7zEaA1dTJsySL  ,   local12events      |  / II7zEaA1dTJsySL
						//$mail->SetFrom("Utility Workers Union of America", "webmaster@uwua.net");
						$mail->From = "technog33k2@gmail.com"; //webmaster@uwua.net
						$mail->FromName = "Utility Workers Union of America";
						$mail->Subject = "Password Reset";
						$mail->Body = "Your New Password is: $mpass";
						$mail->AddAddress($email);
						$mail->Send();   
						$data = array($A);
						$response = '{}'; 
				}
				else
				{
					$data = array('error_code'=>'E0016','text' => "Email Doesn't Exist.");
					$response = $response->withJson($data, 401);  
				}
			}
			else
			{
				$data = array('error_code'=>'E003','text' => "Please Enter Correct Default-Admin-Token");
				$response = $response->withJson($data, 401);  
			}
			$headers = $request->getHeaders();    
		}
		else
		{
			$data = array('error_code'=>'E008','text' => "Please Enter Token");
		    $response = $response->withJson($data, 401); 
		} 
		return $response; 	
	 
});


// Edit Member in member_data table...
$app->post('/editMemberData[/{member_id}]', function ($request, $response, $args) { 
	$MAuth = $request->getHeaderLine('Member-Auth-Token');  
	$parsedBody = $request->getParsedBody();
	$member_id = $args['member_id'];
	echo $member_id;
	$MID = ltrim(preg_replace("/[^0-9,.]/", "", $member_id ),'0');
	echo $member_id;
	$db = getDB();
	$EmpID = $parsedBody['Emp_No'];
	echo '...'.$EmpID;
	if($MAuth)
	{    
			$sqla = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'";
			$stmta = $db->query($sqla);   
			$memberS = $stmta->fetchAll(PDO::FETCH_OBJ);
			if($memberS){
				$sql = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id' AND `Member_Auth_Token` = '$MAuth'"; 
				$db = getDB();
				$stmt = $db->query($sql);   
				$memberdata = $stmt->fetchAll(PDO::FETCH_OBJ);
				if($memberdata)
				{
					if($MID == $EmpID){
				  		  
				  $ID = $parsedBody['ID'];  
				  $First_Name = $parsedBody['First_Name'];
				  $Last_Name = $parsedBody['Last_Name'];
				  $Email = $parsedBody['Email'];
				  $Phone = $parsedBody['Phone'];
				  $Street_Address = $parsedBody['Street_Address'];
				  $Apt_Suite_Room = $parsedBody['Apt_Suite_Room'];
				  $City = $parsedBody['City'];
				  $State = $parsedBody['State'];	
				  $Zip = $parsedBody['Zip_Code'];
				  $Pas = $parsedBody['Password'];
				  $Password = md5($parsedBody['Password']);
				  $p = '';
				  if($Pas != '')
				  {
					  $p = "Password = '$Password' ,";
				  } 
				  $Company = $parsedBody['Company_Prefix'];  
				  $Role = $parsedBody['Role'];
				  $Report_To = $parsedBody['Report_To']; 
				  
				  $cmp = "SELECT * FROM `company` WHERE `ID_Prefix` = '".$Company."'";
				  $comdata = $db->query($cmp);
				  $cdata = $comdata->fetchAll(PDO::FETCH_OBJ);
					$mem = "SELECT email FROM member_data WHERE email = '".$parsedBody['Email']."' AND Emp_No != '".$parsedBody['Emp_No']."'";
					$memm = $db->query($mem);
					$memma = $memm->fetchAll(PDO::FETCH_OBJ); 
				  	if($Role == 'Steward')
				  	{
					 if($Report_To != '')
					 {
						$usr = "SELECT * FROM users WHERE id ='$Report_To' AND userrole='agent'";
						$ustmt = $db->query($usr);
						$users = $ustmt->fetchAll(PDO::FETCH_OBJ);
						if(!empty($users)){
						    if($memma[0]->email == ''){
								$Update = "UPDATE member_data SET $p First_Name = '$First_Name', Last_Name = '$Last_Name',Email = '$Email', Phone = '$Phone', Street_Address = '$Street_Address', Apt_Suite_Room = '$Apt_Suite_Room', City = '$City', State = '$State', Zip_Code = '$Zip', Company_Prefix = '$Company', Role = '$Role', Report_To = '$Report_To' WHERE Member_ID = '$member_id' ";
								$stmt = $db->query($Update);  
								$sqla = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'";
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{  
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}
									$rMember = $members[0]->Report_To; 
									if($members[0]->Report_To != ''){
										$sqlu = "SELECT * FROM users  WHERE id = '".$rMember."'";
										$stmtu = $db->query($sqlu);  
										$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
										unset($Users[0]->password);
										unset($Users[0]->access_key);
										unset($Users[0]->report_to);
										$members[0]->Report_To_User = $Users[0];
									}
									unset($members[0]->Report_To);
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								}
							}
							else
							{
								$data = array('error_code'=>'E0013','text' => "Email Already Exists. Please Choose Another.");
								$response = $response->withJson($data, 401);
							}
						}
						else
						{
							$data = array('error_code'=>'E009','text' => "The User To Whom Report To Doesn't Exist.");
							$response = $response->withJson($data, 401);
						}
					 }
					 else
					 {
						$data = array('error_code'=>'E002','text' => "Please Enter Report To User.");
						$response = $response->withJson($data, 401); 
					 } 
				   }
				  	else if($Role == 'Member')
				  	{  
				      
					     if($Report_To == '')
						 {   
						  if($memma[0]->email == ''){ 
								$Update = "UPDATE member_data SET $p First_Name = '$First_Name',Last_Name = '$Last_Name',Email = '$Email',Phone = '$Phone',Street_Address = '$Street_Address',Apt_Suite_Room = '$Apt_Suite_Room',City = '$City',State = '$State',Zip_Code = '$Zip',Company_Prefix = '$Company',Role = '$Role' WHERE Member_ID = '$member_id' ";
								$stmt = $db->query($Update); 
								$sqla = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'"; 
								  
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{   
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									unset($members[0]->Report_To);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								} 
							}
						  else
						  {
							  $data = array('error_code'=>'E0013','text' => "Email Already Exists. Please Choose Another.");
							  $response = $response->withJson($data, 401);
						  }
						 }
						 else
						 { 
							$data = array('error_code'=>'E002','text' => "Member Role Doesn't Report To Another Member.");
							$response = $response->withJson($data, 401); 
						 } 
				  
				  }
				  	else
				  	{ 
				     if($Role == '' && $Report_To != ''){
								   $data = array('error_code'=>'E002','text' => "Must Select A Role.");
								   $response = $response->withJson($data, 401);
					   }
					 else
					 {
					  		  if($memma[0]->email == ''){
							   	    
							   $Role = 'Member';
							   $Update = "UPDATE member_data SET $p First_Name = '$First_Name',Last_Name = '$Last_Name',Email = '$Email',Phone = '$Phone',Street_Address = '$Street_Address',Apt_Suite_Room = '$Apt_Suite_Room',City = '$City',State = '$State',Zip_Code = '$Zip',Company_Prefix = '$Company' WHERE Member_ID = '$member_id' ";
									$stmt = $db->query($Update);  
								$sqla = "SELECT * FROM `member_data` WHERE `Member_ID` = '$member_id'";  
								$stmta = $db->query($sqla);
								$members = $stmta->fetchAll(PDO::FETCH_OBJ);   
								if($members)
								{  
									if($cdata[0]->Company_Name != ''){
									   $members[0]->Company = $cdata[0]->Company_Name;
									}else
									{
										$members[0]->Company = '';
									}  
									unset($members[0]->Report_To);
									unset($members[0]->Password);
									unset($members[0]->Member_ID);
									$response = $response->withJson($members[0], 201);   
								}
								else
								{
								   $data = array();
								   $response = $response->withJson($data, 201);
								}
						      
							  }
							  else
							  {
							  	   $data = array('error_code'=>'E0013','text' => "Email Already Exists. Please Choose Another.");
								   $response = $response->withJson($data, 401);
							  } 
					   } 
				  } 
				  
				
				 	}
					else
					{
					  $data = array('error_code'=>'E001','text' => "Enter Correct Member_ID Or Emp_No.");
			          $response = $response->withJson($data, 401);
					}
				}
				else
				{
					$data = array('error_code'=>'E005','text' => "Invalid Member Auth Token");
					$response = $response->withJson($data, 401);
				}   
			}
			else
			{
				$data = array('error_code'=>'E0013','text' => "Member Doesn't Exist.");
				$response = $response->withJson($data, 401);
			} 
	}
	else
	{
		$data = array('error_code'=>'E005','text' => "Enter Member Auth Token");
		$response = $response->withJson($data, 401);
	}
 	return $response;   
});

/*Code For New Member END __________________________*/ 


// Call IN Modual  Code//
																					
$app->post('/callIns', function ($request, $response, $args) { 
	$parsedBody = $request->getParsedBody();
	$Auth = $request->getHeaderLine('Admin-Auth-Token');
	$check = checkAuthKey($Auth);
	$db = getDB();
	if($check)
	{
		$accesstoken =  explode('-',$Auth); 
	    $userID = $accesstoken[0]; 
		
		$sel = "SELECT * FROM users WHERE id = '".$userID."'";  
		$stmt = $db->query($sel);
		$seldata = $stmt->fetchAll(PDO::FETCH_OBJ);  
	    
			if($parsedBody['date'] != '')
			   $date = $parsedBody['date'];  
			else
			   $date = date('Y-m-d');
	
			$page = $parsedBody['page'];
			$filter = $parsedBody['agent'];  
			if($page == '' || $page == '0')
				$pagea = 1;
			else
				$pagea = $page;
	
			$offset =  ($pagea * 10); 
			$offset = ($offset -10);
	
			if($offset == 1)
				$offset = 0;
	        
			$searchval = explode(" ",$parsedBody['agent']);
			
			if($seldata[0]->role == 3){ 
			    $query = $db->query("SELECT * FROM users WHERE (id LIKE '%".$searchval[0]."%' OR (fname LIKE '%".$searchval[0]."%' AND lname LIKE '%".$searchval[1]."%') OR (fname LIKE '%".$searchval[1]."%' AND lname LIKE '%".$searchval[0]."%'))  AND ( id = '".$seldata[0]->id."')  ORDER BY fname LIMIT ".$offset.", 10");
			}
			else
			{ 
				$query = $db->query("SELECT * FROM users WHERE (id LIKE '%".$searchval[0]."%' OR (fname LIKE '%".$searchval[0]."%' AND lname LIKE '%".$searchval[1]."%') OR (fname LIKE '%".$searchval[1]."%' AND lname LIKE '%".$searchval[0]."%'))  AND (userrole = 'agent' OR userrole = 'senioragent')  ORDER BY fname LIMIT ".$offset.", 10");
			} 
			
			 
			
			$users = $query->fetchAll(PDO::FETCH_OBJ);  
			if(count($users) > 0)
			{
				     if($seldata[0]->role == 3){    
						// call_in code
						
						$UID = $seldata[0]->id;
						if($parsedBody['date'] == ''){
						   $sel = "SELECT * FROM call_in WHERE user = '".$UID."'";
						} 
						else{
						   $sel = "SELECT * FROM call_in WHERE callin_date = '".$date."' AND user = '".$UID."'";
						} 
						$stmt = $db->query($sel);
						$seldata = $stmt->fetchAll(PDO::FETCH_OBJ); 
						for($i = 0; $i < count($seldata); $i++)
						{ 
							// User Code
							$user = "SELECT * FROM users WHERE id = '".$UID."'";
							$userdata = $db->query($user);
							$udata = $userdata->fetchAll(PDO::FETCH_OBJ); 
							$report_to = $udata[0]->report_to;
							unset($udata[0]->report_to);
							if($report_to != ''){
								$udata[0]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$udata[0]->report_to = $report_to;
							}
							unset($udata[0]->password);
							unset($udata[0]->access_key);
							$data[$i]['user'] = $udata[0];
							
							// call_in data
							$data[$i]['call_in'] = $seldata[$i];	
						} 
						
						if(!empty($data))
						{
					      $response = $response->withJson($data, 201);  				    
						}
						else
						{
						  $data = array('error_code'=>'E0022','text' => "No Data Found!!!");
			              $response = $response->withJson($data, 401); 
						}
					 }
					 else
					 { 
						for($i = 0; $i < count($users); $i++)
						{
							// call_in code
							$UID = $users[$i]->id;
							$sel = "SELECT * FROM call_in WHERE callin_date = '".$date."' AND user = '".$UID."'"; 
							$stmt = $db->query($sel);
							$seldata = $stmt->fetchAll(PDO::FETCH_OBJ);
							
							// User Code
							$user = "SELECT * FROM users WHERE id = '".$UID."'";
							$userdata = $db->query($user);
							$udata = $userdata->fetchAll(PDO::FETCH_OBJ); 
							$report_to = $udata[0]->report_to;
							unset($udata[0]->report_to);
							if($report_to != ''){
								$udata[0]->report_to = (int)str_replace(' ', '', $report_to);
							}
							else
							{
								$udata[0]->report_to = $report_to;
							}
							unset($udata[0]->password);
							unset($udata[0]->access_key);
							$data[$i]['user'] = $udata[0];
							
							// call_in data
							$data[$i]['call_in'] = $seldata[0];
						}
						
						if(!empty($data))
						{
					      $response = $response->withJson($data, 201);  				    
						}
						else
						{
						  $data = array('error_code'=>'E0022','text' => "No Data Found!!!");
			              $response = $response->withJson($data, 401); 
						}   
					} 
			}
			else
			{
				$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
				$response = $response->withJson($data, 401); 
			}
		 
		 
	}
	else
	{
		$data = array('error_code'=>'E003','text' => "Invalid Admin Auth Token.");
		$response = $response->withJson($data, 401); 
	} 
	return $response;
});

$app->post('/addCallIn', function ($request, $response, $args) { 
	$parsedBody = $request->getParsedBody();
	$Auth = $request->getHeaderLine('Admin-Auth-Token');
	$check = checkAuthKey($Auth);
	 $db = getDB();
	if($check)
	{
	   $accesstoken =  explode('-',$Auth); 
	   $userID = $accesstoken[0]; 
	   $am = $parsedBody['location_am'];
	   $pm = $parsedBody['location_pm'];
	   $UserID = $parsedBody['user_id'];
	   if($UserID != '')
	   {
		   $auser_id = $parsedBody['user_id'];
	   }
	   else
	   {
		   $auser_id = $userID;
	   }
	   
	   $callin_date = date('Y-m-d');
	   if($parsedBody['callin_date'] != ''){
		   $callin_date = date('Y-m-d',strtotime($parsedBody['callin_date']));
	    } 
		
	   $sel = "SELECT * FROM users WHERE id = '".$userID."'"; 
	   $stmt = $db->query($sel);
	   $seldata = $stmt->fetchAll(PDO::FETCH_OBJ); 
		if($parsedBody['callin_date'] != ''){
		    $callintime = date('Y-m-d H:i:s',strtotime($parsedBody['callin_date']));
		}
		else
		{
		    $callintime = date('Y-m-d H:i:s');
		}
	   if($seldata[0]->role != '3' && $seldata[0]->userrole == 'agent' || $seldata[0]->userrole == 'senioragent'  ){
			 if($am != '' && $pm != '' && $userID != '') {
				  if(strlen($am) >= 6 && strlen($pm) >= 6){
					    $cal = "SELECT * FROM call_in WHERE callin_date = '".$callin_date."' AND user = '".$auser_id."'";
						$call = $db->query($cal);
						$calldata = $call->fetchAll(PDO::FETCH_OBJ);
						 if(!empty($calldata)){
							   
								$sql = $db->query("UPDATE call_in SET callin_time = '$callintime', location_am='$am', location_pm='$pm' WHERE callin_date = '".$callin_date."' AND user = '".$auser_id."'");
								$sel = "SELECT * FROM call_in WHERE callin_date = '".$callin_date."' AND user = '".$auser_id."'";
								$stmt = $db->query($sel);
								$seldata = $stmt->fetchAll(PDO::FETCH_OBJ);
								unset($seldata[0]->user);
								unset($seldata[0]->callin_date);
								$data['call_in'] = $seldata[0];
								$user = "SELECT * FROM users WHERE id = '".$auser_id."'";
								$userdata = $db->query($user);
								$udata = $userdata->fetchAll(PDO::FETCH_OBJ);
								$report_to = $udata[0]->report_to;
								unset($udata[0]->report_to);
								if($report_to != ''){
									$udata[0]->report_to = (int)str_replace(' ', '', $report_to);
								}
								else
								{
									$udata[0]->report_to = $report_to;
								}
								unset($udata[0]->password);
								unset($udata[0]->access_key); 
								$data['user'] = $udata[0];
								$response = $response->withJson($data, 201);
						 }
						 else
						 {  
								$sql = $db->query("INSERT INTO call_in (callin_time,location_am,location_pm,user,callin_date) VALUES('$callintime','$am','$pm', '$auser_id','$callin_date')");
								$sel = "SELECT * FROM call_in WHERE callin_date = '".$callin_date."' AND user = '".$auser_id."'";
								$stmt = $db->query($sel);
								$seldata = $stmt->fetchAll(PDO::FETCH_OBJ);
								unset($seldata[0]->user);
								unset($seldata[0]->callin_date);
								$data['call_in'] = $seldata[0];
								$user = "SELECT * FROM users WHERE id = '".$auser_id."'";
								$userdata = $db->query($user);
								$udata = $userdata->fetchAll(PDO::FETCH_OBJ);
								$report_to = $udata[0]->report_to;
								unset($udata[0]->report_to);
								if($report_to != ''){
									$udata[0]->report_to = (int)str_replace(' ', '', $report_to);
								}
								else
								{
									$udata[0]->report_to = $report_to;
								} 
								unset($udata[0]->password);
								unset($udata[0]->access_key); 
								$data['user'] = $udata[0]; 
								$response = $response->withJson($data, 201); 
						 } 
				  }
				  else
				  {
						$data = array('error_code'=>'E0020','text' => "Must Be At Least 6 Characters Long.");
						$response = $response->withJson($data, 401); 
				  } 
			 }
			 else
			 {
				$data = array('error_code'=>'E002','text' => "Please Enter Value.");
				$response = $response->withJson($data, 401);
			 } 
	   }
	   else
	   {
		$data = array('error_code'=>'E0022','text' => "Not Enough Permissions.");
		$response = $response->withJson($data, 401);  
	   }
	   
	}
	else
	{
		$data = array('error_code'=>'E003','text' => "Please Enter Correct Auth Token.");
		$response = $response->withJson($data, 401); 
	}  
	return $response;
});

$app->post('/callInsByStaff', function ($request, $response, $args) {
	 
	$parsedBody = $request->getParsedBody();
	$Auth = $request->getHeaderLine('Admin-Auth-Token');
	$check = checkAuthKey($Auth);
	$db = getDB();
	if($check)
	{
		$accesstoken =  explode('-',$Auth); 
	    $userID = $accesstoken[0]; 
		
		$sel = "SELECT * FROM users WHERE id = '".$userID."'";  
		$stmt = $db->query($sel);
		$seldata = $stmt->fetchAll(PDO::FETCH_OBJ);  
		if(($seldata[0]->userrole == 'agent' || $seldata[0]->userrole == 'senioragent') && $seldata[0]->role == '3')
		{  
		      if($parsedBody['date'] != ''){ 
			     $lastdate = date('Y-m-d', strtotime($parsedBody['date']. '- 30 days'));
			  }
			  else
			  {
				 $lastdate = date('Y-m-d', strtotime('today - 30 days'));   
			  }
			   $z=0;
			  for($a = 1; $a <= 30; $a++){
			    $data[$a] = date('Y-m-d H:i:s', strtotime($lastdate . ' +1 day'));
				$lastdate = $data[$a]; 
				  $dayname = date('D',strtotime($lastdate));
				  if($dayname != 'Sat' && $dayname != 'Sun'){   
					 $dataa = $db->query("SELECT * FROM call_in WHERE callin_date = '".$lastdate."' AND user = '".$userID."'");
					 $b = $dataa->fetchAll(PDO::FETCH_OBJ);
					 if($b[0]->id != '')
					 {
						 $report_to = $b[0]->user;
						 unset($b[0]->user);
						 $b[0]->user = (int)str_replace(' ', '', $report_to);
						 $display[$z] = $b[0];  
					 }
					 else
					 {
						 $display[$z]['callin_date'] = date('Y-m-d',strtotime($lastdate));  
					 } 
					 $z++; 
				  } 
				  
			  }
			  $display = array_reverse($display);
			  $response = $response->withJson($display, 201); 
		}
		else
		{
			$data = array('error_code'=>'E0022','text' => "Not Enough Permissions.");
			$response = $response->withJson($data, 401); 
		}  
	}
	else
	{
		$data = array('error_code'=>'E003','text' => "Please Enter Correct Admin Auth Token.");
		$response = $response->withJson($data, 401); 
	} 
	return $response;
	  
});

// Call IN Modual  Code END//

// Compny data end point //

$app->get('/getCompanies', function ($request, $response, $args) {  
	    $Auth = $request->getHeaderLine('Default-Member-Token');
		$adminauth = $request->getHeaderLine('Admin-Auth-Token');
		$db = getDB();  
		if($Auth != ''){ 
			if($Auth)
			{ 
				 $checkauth = checkmemberlogin($Auth);
				 if($checkauth)
				 {
					 $comp = "SELECT * FROM company";
					 $stmt = $db->query($comp);
					 $company = $stmt->fetchAll(PDO::FETCH_OBJ); 
					 $response = $response->withJson($company, 201); 
				 }
				 else
				 {
					$data = array('error_code'=>'E005','text' => "Invalid Default Member Token");
					$response = $response->withJson($data, 401);    
				 }
			}
			else
			{
				$data = array('error_code'=>'E006','text' => "Enter Default Member Token");
				$response = $response->withJson($data, 401);  
			} 
		}
		else
		{
			if($adminauth != '')
			{ 
			 $checkauth = checkAuthKey($adminauth);
			 if($checkauth)
			 {
				 $comp = "SELECT * FROM company";
				 $stmt = $db->query($comp);
				 $company = $stmt->fetchAll(PDO::FETCH_OBJ); 
				 $response = $response->withJson($company, 201); 
			 }
			 else
			 {
				$data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
				$response = $response->withJson($data, 401);    
			 }
			}
			else
			{
			$data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
			$response = $response->withJson($data, 401);  
			} 
		}
	 return $response;
});


 
// get data by user role
$app->post('/getUsersByUserRole', function ($request, $response, $args) { 
     $parsedBodya = $request->getParsedBody();  
	 $User_Role = $parsedBodya['user_role'];
	 $page = $parsedBodya['page'];
	 $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
	 $MemberAuth = $request->getHeaderLine('Default-Member-Token');
	 $db = getDB();
	 if($User_Role != '')
	 {
		 if($Auth != ''){
		   if($Auth){
				$checkauth = checkAuthKey($Auth);
				if($checkauth)
				{  
				    if($page == '' || $page == '0')
					{
						$pagea = 1;
					}
					else
					{
						$pagea = $page ;
					}
					$offset =  ($pagea * 10); 
					$offset = ($offset - 10);
					//$offset = $offset + 1;
					if($offset == 1)
					{
						$offset = 0;
					}
					 
					$sql = "SELECT * FROM users  WHERE userrole = '".$User_Role."' LIMIT ".$offset.", 10";
					$stmt = $db->query($sql);  
					$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
					if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
						 $UserID = $users[$i]->id;  
						 $usersa[$i] = $users[$i];
						 $Report_to = $usersa[$i]->report_to;
						 unset($usersa[$i]->report_to); 
						 if($Report_to != ''){
								$sqlu = "SELECT * FROM users  WHERE id = '".$Report_to."'";
								$stmtu = $db->query($sqlu);  
								$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
								unset($Users[0]->password); 
								unset($Users[0]->access_key);
								unset($Users[0]->report_to);
						    $usersa[$i]->Report_To_User = $Users[0];
						 }
						 unset($usersa[$i]->password); 
						 unset($usersa[$i]->access_key); 
						}
					$response = $response->withJson($usersa, 201);
					}
					else
					{
						$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						$response = $response->withJson($data, 401);   
					} 
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		   else
		   {
			    $data = array('error_code'=>'E006','text' => "Enter Admin Auth Token");
		        $response = $response->withJson($data, 401);  
		   } 
		 }
		 else{
			if($MemberAuth){
				$checkauth = checkmemberlogin($MemberAuth);
				if($checkauth)
				{  
				    if($page == '' || $page == '0')
					{
						$pagea = 1;
					}
					else
					{
						$pagea = $page ;
					}
					$offset =  ($pagea * 10); 
					$offset = ($offset - 10);
					//$offset = $offset + 1;
					if($offset == 1)
					{
						$offset = 0;
					}
					 
					$sql = "SELECT * FROM users  WHERE userrole = '".$User_Role."' LIMIT ".$offset.", 10";
					$stmt = $db->query($sql);  
					$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
					if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
						 $UserID = $users[$i]->id;  
						 $usersa[$i] = $users[$i];
						 $Report_to = $usersa[$i]->report_to;
						 unset($usersa[$i]->report_to); 
						 if($Report_to != ''){
								$sqlu = "SELECT * FROM users  WHERE id = '".$Report_to."'";
								$stmtu = $db->query($sqlu);  
								$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
								unset($Users[0]->password); 
								unset($Users[0]->access_key);
								unset($Users[0]->report_to);
						    $usersa[$i]->Report_To_User = $Users[0];
						 }
						 unset($usersa[$i]->password); 
						 unset($usersa[$i]->access_key); 
						}
					$response = $response->withJson($usersa, 201);
					}
					else
					{
						$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						$response = $response->withJson($data, 401);   
					} 
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Member Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		   else
		   {
			    $data = array('error_code'=>'E006','text' => "Enter Member Auth Token");
		        $response = $response->withJson($data, 401);  
		   } 
		 }
	 }
	 else
	 {
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401); 
	 }
	return $response;    
}); 

// get data by Member_Data role
$app->post('/getMemberDataByRole', function ($request, $response, $args) { 
     $parsedBodya = $request->getParsedBody();  
	 $Member_Role = $parsedBodya['member_role'];
	 $page = $parsedBodya['page']; 
	 $DefaultAuth = $request->getHeaderLine('Default-Member-Token');
	 $MemberAuth = $request->getHeaderLine('Member-Auth-Token');
	 $Auth = $request->getHeaderLine('Admin-Auth-Token');
	 $db = getDB();
	 
	 if($page == '' || $page == '0')
	{
		$pagea = 1;
	}
	else
	{
		$pagea = $page ;
	}
	$offset =  ($pagea * 10); 
	$offset = ($offset - 10);
	//$offset = $offset + 1;
	if($offset == 1)
	{
		$offset = 0;
	}
	  
	 if($DefaultAuth != '')
	 { 
			if($DefaultAuth){
				$checkauth = checkmemberlogin($DefaultAuth);
				if($checkauth)
				{    
					$sql = "SELECT * FROM member_data  WHERE Role = '".$Member_Role."' LIMIT ".$offset.", 10"; 
					$stmt = $db->query($sql);  
					$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
					if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
						 $UserID = $users[$i]->id;  
						 $usersa[$i] = $users[$i];
						 $Report_to = $usersa[$i]->Report_To;
						 unset($usersa[$i]->Report_To); 
						 if($Report_to != ''){
								$sqlu = "SELECT * FROM users  WHERE id = '".$Report_to."'";
								$stmtu = $db->query($sqlu);  
								$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
								unset($Users[0]->password);  
								unset($Users[0]->access_key);
								unset($Users[0]->report_to);
						    $usersa[$i]->Report_To_User = $Users[0];
						 }
						 unset($usersa[$i]->Password); 
						 unset($usersa[$i]->Member_Auth_Token); 
						}
					$response = $response->withJson($usersa, 201);
					}
					else
					{
						$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						$response = $response->withJson($data, 401);   
					} 
				}
				else
				{
					 $data = array('error_code'=>'E005','text' => "Invalid Default Member Token");
					 $response = $response->withJson($data, 401);   
				}     
		  }
		    elseif($MemberAuth)
		    {
			    $data = array('error_code'=>'E006','text' => "Enter Default Member Token");
		        $response = $response->withJson($data, 401);  
		    }  
	 }
	 elseif($MemberAuth)
	 {
	         if($MemberAuth){
				$checkauth = "SELECT * FROM `member_data` WHERE `Member_Auth_Token` = '$MemberAuth'";
				$stmtm = $db->query($checkauth);
				$memberdata = $stmtm->fetchAll(PDO::FETCH_OBJ);
				if(!empty($memberdata)){
				  if($checkauth)
				  {   
					$sql = "SELECT * FROM member_data  WHERE Role = '".$Member_Role."' LIMIT ".$offset.", 10"; 
					$stmt = $db->query($sql);  
					$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
					if($users){ 
						for($i = 0; $i < count($users); $i++)
						{
						 $UserID = $users[$i]->id;  
						 $usersa[$i] = $users[$i];
						 $Report_to = $usersa[$i]->Report_To;
						 unset($usersa[$i]->Report_To); 
						 if($Report_to != ''){
								$sqlu = "SELECT * FROM users  WHERE id = '".$Report_to."'";
								$stmtu = $db->query($sqlu);  
								$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
								unset($Users[0]->password);  
								unset($Users[0]->access_key);
								unset($Users[0]->report_to);
						    $usersa[$i]->Report_To_User = $Users[0];
						 }
						 unset($usersa[$i]->Password); 
						 unset($usersa[$i]->Member_Auth_Token); 
						}
					$response = $response->withJson($usersa, 201);
					}
					else
					{
						$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
						$response = $response->withJson($data, 401);   
					} 
				}
				  else
				  {
					 $data = array('error_code'=>'E005','text' => "Invalid Member Auth Token");
					 $response = $response->withJson($data, 401);   
				}     
				}
				else
				{ 
				  $data = array('error_code'=>'E005','text' => "Invalid Member Auth Token");
				  $response = $response->withJson($data, 401); 
			    }
		    }
		    elseif($MemberAuth)
		    {
			    $data = array('error_code'=>'E006','text' => "Enter Member Auth Token");
		        $response = $response->withJson($data, 401);  
		    }
	 }
	 elseif($Auth){
	   $checkauth = checkAuthKey($Auth);
	   $accesstoken =  explode('-',$Auth); 
	   $userID = $accesstoken[0]; 
	   if($checkauth){
			$sql = "SELECT * FROM member_data  WHERE Role = '".$Member_Role."' LIMIT ".$offset.", 10"; 
			$stmt = $db->query($sql);  
			$users = $stmt->fetchAll(PDO::FETCH_OBJ); 
			if($users){ 
				for($i = 0; $i < count($users); $i++)
				{
				 $UserID = $users[$i]->id;  
				 $usersa[$i] = $users[$i];
				 $Report_to = $usersa[$i]->Report_To;
				 unset($usersa[$i]->Report_To); 
				 if($Report_to != ''){
						$sqlu = "SELECT * FROM users  WHERE id = '".$Report_to."'";
						$stmtu = $db->query($sqlu);  
						$Users = $stmtu->fetchAll(PDO::FETCH_OBJ);
						unset($Users[0]->password);  
						unset($Users[0]->access_key);
						unset($Users[0]->report_to);
					$usersa[$i]->Report_To_User = $Users[0];
				 }
				 unset($usersa[$i]->Password); 
				 unset($usersa[$i]->Member_Auth_Token); 
				}
			$response = $response->withJson($usersa, 201);
			}
			else
			{
				$data = array('error_code'=>'P001','text' => "No More Data Found!!!");
				$response = $response->withJson($data, 401);   
			} 
	   }
	   else{
			   $data = array('error_code'=>'E005','text' => "Invalid Admin Auth Token");
			   $response = $response->withJson($data, 401);  
	   } 
	 }
	 else
	 {
		$data = array('error_code'=>'E002','text' => "Please Enter Value.");
		$response = $response->withJson($data, 401); 
	 }
	return $response;    
}); 

// Compny data end point //

$app->run();