<?php 
    class EventsController {
        function eventAttendeesByField($request, $response, $args) {  
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            $parsedBody = $request->getParsedBody();   // print_r($parsedBody);   
            $evID = $args['ev_id'];

            if($Auth)
            {  
                   $auth = new AuthController(); 
                   $checkauth = $auth->checkAuthKey($Auth);
                   $userID = $auth->getUserIdByToken($Auth);
                   //    $accesstoken =  explode('-',$Auth); 
                //    $userID = $accesstoken[0]; 
                //    $db = getDB();
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
       }

    // get event by status
    function eventByStatus($request, $response, $args){                 // 0 : All , 1 : Open , 2 : Past
        $parsedBodya = $request->getParsedBody();  // print_r($parsedBodya);   
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
   }

        function eventById($request, $response, $args){ 
            $ev_id = $args['ev_id'];
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($ev_id)
            { 
                if($Auth)
                {   
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey1($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
        }

        function addEvent($request, $response, $args) {
            $parsedBody = $request->getParsedBody();   // print_r($parsedBody);   
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($Auth)
            { 
                if(!empty($parsedBody)){
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    $userID = $auth->getUserIdByToken($Auth); 
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
       }

       // Get Event Checkin
       function eventCheckInMember($request, $response, $args) { 
            $evID = $args['ev_id']; 
            $parsedBody = $request->getParsedBody();     //print_r($parsedBody);   
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
             if($Auth)
             {
                  if(!empty($parsedBody)){
                        $auth = new AuthController();
                        $checkauth = $auth->checkAuthKey($Auth);
                        $userID = $auth->getUserIdByToken($Auth);
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
        }

        function eventAttendeesByUserRole($request, $response, $args) {  
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            $evID = $args['ev_id'];
            if($Auth)
            {  
                   $auth = new AuthController();
                   $checkauth = $auth->checkAuthKey($Auth);
                   $userID = $auth->getUserIdByToken($Auth);
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
       }

       function editEventById($request, $response, $args) { 
        $parsedBody = $request->getParsedBody();     
        $Auth = $request->getHeaderLine('Admin-Auth-Token');  
        $evID = $args['ev_id'];
        if($Auth)
        { 
            if(!empty($parsedBody)){
               $auth = new AuthController();
               $checkauth = $auth->checkAuthKey($Auth);
               $userID = $auth->getUserIdByToken($Auth);
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
        
        return $response; 
    }

    function checkinEvent($request, $response, $args) {   
        $evID = $args['ev_id']; 
        $parsedBody = $request->getParsedBody();
        $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
        $db = getDB(); 
         if($Auth)
         { 
             $auth = new AuthController();
            $checkauth = $auth->checkAuthKey($Auth); 
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
         
     return $response; }
}

?>