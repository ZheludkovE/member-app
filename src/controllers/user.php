<?php 
    class UserController {

        //User add
        function addUser($request, $response, $args) {  
            $parsedBody = $request->getParsedBody();  //  print_r($parsedBody);  
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($Auth)
            { 
                if(!empty($parsedBody)){
                   $auth = new AuthController();
                   $checkauth = $auth->checkAuthKey($Auth);
                //    $accesstoken =  explode('-',$Auth); 
                //    $userID = $accesstoken[0];
                //    $db = getDB();
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
       }

       // User Edit
       function userEditById($request, $response, $args) {   
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
                    $auth = new AuthController();
                    $userID = $auth->getUserIdByToken($Auth);
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
                
                return $response;  
        }

        // get user by user ID
        function getUserById($request, $response, $args){
            
           $user_id = $args['user_id'];
           $Auth = $request->getHeaderLine('Admin-Auth-Token');  
           if($user_id)
           { 
               if($Auth)
               {    
                   $auth = new AuthController();
                   $checkauth = $auth->checkAuthKey($Auth);
                   $userID = $auth->getUserIdByToken($Auth);
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
            
       }

        // get data by role
        function getUsersByRole($request, $response, $args) { 
            $parsedBodya = $request->getParsedBody(); // print_r($parsedBodya);  
            $role = $parsedBodya['role'];
            $page = $parsedBodya['page'];
            $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
            if($role != '' && $page != '')
            {
                 if($Auth){
                       $auth = new AuthController();
                       $checkauth = $auth->checkAuthKey($Auth);
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
       }

       function userResetPassword($request, $response, $args) { 
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
	 
        }

        function getUsersByUserRole($request, $response, $args) { 
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
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
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
                       $memberauth = new MembersController();
                       $checkauth = $memberauth->checkmemberlogin($MemberAuth);
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
       }
    }
