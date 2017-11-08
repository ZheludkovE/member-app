<?php 
    class AuthController {

    public $userID;
    public $checkauth;

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

    function generateRandomString($length = 20) { 
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        } 
        return $randomString; 
    }

    function checkAuthKey($Auth){
        $params = $Auth;
          $AccessKey = explode('-',$params['AccessKey']);
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

      /* Why this method is used? */
      function getUserIdByToken($Auth) {
        $accesstoken =  explode('-',$Auth); 
        $userID = $accesstoken[0];
        $db = getDB();
       
        return $userID;
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

}
   
?>