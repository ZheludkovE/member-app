<?php 
  
    class LocationController {
        
        // event location By ID
        function locationById($request, $response, $args) { 
            $locationid = $args['location_id'];
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($locationid)
            { 
                if($Auth)
                { 
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    // $accesstoken =  explode('-',$Auth); 
                    // $userID = $accesstoken[0];
                    // $db = getDB();
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
            return $response; 
        }
        
        // Add Location
        function addLocation($request, $response, $args) {
            $parsedBody = $request->getParsedBody();     //print_r($parsedBody);   
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($Auth)
            { 
                if(!empty($parsedBody)){
                   $auth = new AuthController();
                   $checkauth = $auth->checkAuthKey($Auth);
                   $userID = $auth->getUserIdByToken($Auth);
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
       }

       // event location
       function picketLocations($request, $response, $args) { 
            $parsedBodya = $request->getParsedBody(); //print_r($parsedBodya);   
            $active = $parsedBodya['active'];
            $page = $parsedBodya['page'];
            $query = $parsedBodya['query'];
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            if($Auth)
            {       
                    $auth = new AuthController();
                    $checkauth = $auth->checkAuthKey($Auth);
                    // $accesstoken =  explode('-',$Auth); 
                    // $userID = $accesstoken[0];
                    // $db = getDB();
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
            
            return $response;   
        }

        function editLocationById($request, $response, $args) { 
            $parsedBody = $request->getParsedBody();   
            $Auth = $request->getHeaderLine('Admin-Auth-Token');  
            $LocID = $args['loc_id'];
            if($Auth)
            { 
                if(!empty($parsedBody)){
                   $auth = new AuthController(); 
                   $checkauth = $auth->checkAuthKey($Auth);
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
            
            return $response; 
        }

        function deletePicketLocation($request, $response, $args) {  
            $Auth = $request->getHeaderLine('Admin-Auth-Token'); 
             $loc_id = $args['loc_id']; 
            if($Auth)
            {  
                $auth = new AuthController();
                $checkauth = $auth->checkAuthKey($Auth);
            //    $accesstoken =  explode('-',$Auth); 
            //    $userID = $accesstoken[0];
            //    $db = getDB();
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
       }
    }
