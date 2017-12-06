<?php 
    class MainController {
        function callIns($request, $response, $args) { 
            $parsedBody = $request->getParsedBody();
            $Auth = $request->getHeaderLine('Admin-Auth-Token');
            $auth = new AuthController();
            $checkauth = $auth->checkAuthKey($Auth);
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
        }

        function callInsByStaff($request, $response, $args) {
            
           $parsedBody = $request->getParsedBody();
           $Auth = $request->getHeaderLine('Admin-Auth-Token');
           $auth = new AuthController();
           $checkauth = $auth->checkAuthKey($Auth);
           if($check)
           {
               $userID = $auth->getUserIdByToken($Auth); 
               
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
             
       }

        function addCallIn($request, $response, $args) { 
            $parsedBody = $request->getParsedBody();
            $Auth = $request->getHeaderLine('Admin-Auth-Token');
            $auth = new AuthController();
            $checkauth = $auth->checkAuthKey($Auth);
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
        }

        function getHomeData($request, $response, $args){ 
            $Auth = $request->getHeaderLine('Admin-Auth-Token');   
                if($Auth)
                { 
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey1($Auth);
                    $userID = $auth->getUserIdByToken($Auth);
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
        }

        function getMetadata($request, $response, $args) { 
            $metaAuth = $request->getHeaderLine('Default-Member-Token'); 
            $defaultAuth = $request->getHeaderLine('Default-Admin-Token');
            $Auth = $request->getHeaderLine('Admin-Auth-Token');
            $member_id = $args['member_id']; 
            $platform = $args['key'];
            if($metaAuth) //Default-Member-Token
            {     $auth = new AuthController();
                  $check = $auth->checkmemberlogin($metaAuth); 
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
                  $auth = new AuthController();
                  $check = $auth->checkapilogin($defaultAuth); 
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
       }
    
    }
?>