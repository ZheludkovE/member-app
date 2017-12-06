<?php 
    function __autoload($AuthController) {
        require_once $AuthController . '.php';
    }

    class LoginController {

        public function test() {
            $auth = new AuthController();
            $random_string = $auth->generateRandomString();
            echo $random_string;
        }

        function adminLogin($request, $response, $args) {  
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
                            $auth = new AuthController();
                            $random = $auth->generateRandomString();
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
        return $response; 
    }

    function memberLogin($request, $response, $args) {
        $MAuth = $request->getHeaderLine('Default-Member-Token');  
        $parsedBody = $request->getParsedBody();
        error_log(print_r($parsedBody, true)); 
        if($MAuth)
        {   
           //error_log($request);
            $memberauth = new MembersController();
            $checkmdefautl = $memberauth->memberdefaultcheck($MAuth); 
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
    }
}
?>