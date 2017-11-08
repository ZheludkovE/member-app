<?php 
  
    class MembersController {

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
        
        // add union member and point (add by admin)
        function addMemberByAdmin($request, $response, $args) {
            $parsedBody = $request->getParsedBody();
            $Auth = $request->getHeaderLine('Admin-Auth-Token');
            $db = getDB();
            if($Auth != '') //Auth-Token
            {
                $auth = new AuthController();
                $check = $auth->checkAuthKey($Auth);
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
        }

        // Edit Member
        function editMember($request, $response, $args) { 
            $parsedBody = $request->getParsedBody();
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            $member_id = $args['member_id'];
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
       }

        // Add Member Code
        function addMemberData($request, $response, $args) { 
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
            }

            // Get Member By ID
            function getMemberDataById($request, $response, $args) {  
                
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
            
        }

        function memberResetPassword($request, $response, $args) { 
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
    }

    function editMemberData($request, $response, $args) { 
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
    }

    function getMemberById($request, $response, $args) {  
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
            $auth = new AuthController();
            $check = $auth->checkAuthKey($Auth);
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
       
   }

        function getCompanies($request, $response, $args) {  
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
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
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
        }
        function getMemberDataByRole($request, $response, $args) { 
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
                    $auth = new MembersController();
                    $checkauth = $auth->checkmemberlogin($DefaultAuth);
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
            $auth = new AuthController();
            $checkauth = $auth->checkAuthKey($Auth);
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
            }
}