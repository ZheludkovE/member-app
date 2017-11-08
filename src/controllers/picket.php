<?php 
    class PicketController {

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
        
        function addPicket($request, $response, $args) {  
            $parsedBody = $request->getParsedBody(); 
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($Auth)
            { 
                if(!empty($parsedBody)){
                   $auth = new AuthController(); 
                   $checkauth = $auth->checkAuthKey($Auth);
                   $userID = $auth->getUserIdByToken($Auth);
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
       }

       function getPicketById($request, $response, $args){ 
        $pk_id = $args['pk_id'];  
        $Auth = $request->getHeaderLine('Admin-Auth-Token');  
       if($pk_id)
       { 
           if($Auth)
           { 
               $auth = new AuthController(); 
               $checkauth = $auth->checkAuthKey($Auth);
               $userID = $auth->getUserIdByToken($Auth);
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
       
   }

   function editPicketById($request, $response, $args) {  
    $parsedBody = $request->getParsedBody();
    $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
     $pk_id = $args['pk_id']; 
    if($Auth)
    { 
        if(!empty($parsedBody)){
            $auth = new AuthController(); 
            $checkauth = $auth->checkAuthKey($Auth);
            $userID = $auth->getUserIdByToken($Auth);
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
   
}

function picketByStatus($request, $response, $args){                 // 0 : All , 1 : Open , 2 : Past
    $parsedBodya = $request->getParsedBody();  
    $status = $parsedBodya['status'];
    $page = $parsedBodya['page'];
    $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
    if($status != '' && $page != '')
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
}

        function deletePicket($request, $response, $args) {  
            $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
            $pk_id = $args['pk_id']; 
            if($Auth)
            {  
                $auth = new AuthController(); 
                $checkauth = $auth->checkAuthKey($Auth);
                $userID = $auth->getUserIdByToken($Auth);
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
        
        }

        function picketSignups($request, $response, $args){ 
            
               $Auth = $request->getHeaderLine('Admin-Auth-Token');   
               $parsedBody = $request->getParsedBody();
               $Picket_Id = $args['picked_id'];
               $query = $parsedBody['query'];
               $page = $parsedBody['page'];
               $signedup = $parsedBody['signedup']; 
               if($Auth)
               { 
                    $auth = new AuthController(); 
                    $checkauth = $auth->checkAuthKey($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
               
           }

           function picketCompletedSlots($request, $response, $args){  
            $Auth = $request->getHeaderLine('Admin-Auth-Token');   
            $parsedBody = $request->getParsedBody();    //print_r($parsedBody);  
            $Picket_Id = $parsedBody['picket_id'];
            $Loc_Id = $parsedBody['location_id'];
            if($Auth)
            { 
                $auth = new AuthController(); 
                $checkauth = $auth->checkAuthKey($Auth);
                $userID = $auth->getUserIdByToken($Auth);
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
            
        }

        function addPicketSignup($request, $response, $args){ 
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
                $auth = new AuthController();
                $checkauth = $auth->checkAuthKey($Auth); 
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
        }

        function picketAttendanceSummary($request, $response, $args){  
            	$Auth = $request->getHeaderLine('Admin-Auth-Token');   
            	$parsedBody = $request->getParsedBody();    //print_r($parsedBody); 
            	$Pkid = $args['picked_id']; 
            	$week = $parsedBody['week']; 
            	$location = $parsedBody['location'];  
            	if($Auth)
            	{ 
            		$auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
                
            }

            function picketAttendanceByDate($request, $response, $args){  
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
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
                
            }

            function picketCheckin($request, $response, $args){  
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
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
            }

}

?>