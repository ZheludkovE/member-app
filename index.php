<?php
include 'db.php';
require('../events/MCAPI.class.php');
require 'vendor/autoload.php';
require_once('../events/includes/PHPMailer-master/PHPMailerAutoload.php');
date_default_timezone_set('America/New_York');

/* Register all the classes include */
spl_autoload_register(function ($class_name) {
	set_include_path('src/controllers/');
	$arr = preg_split('/(?=[A-Z])/', $class_name);
	$splitName = strtolower($arr[1]);
    require_once $splitName . '.php';
});

$app = new Slim\App();
 
$app->get('/', function ($request, $response, $args) {
	$response->write("Welcome to UWUA");
	return $response;});

$app->get('/api-test', '\LoginController:test');
 
// Token
$app->post('/adminLogin', '\LoginController:adminLogin');
	    
// event location By ID
$app->get('/locationById[/{location_id}]', '\LocationController:locationById');  
	 
// Add Location
$app->post('/addLocation', '\LocationController:addLocation');

// get user by user ID
	$app->get('/getUserById[/{user_id}]', 'UserController:getUserById');

// get data by role
$app->post('/getUsersByRole', 'UserController:getUsersByRole');

// Get Event Attendees   By field               
$app->post('/eventAttendeesByField[/{ev_id}]', 'EventsController:eventAttendeesByField');

// User Edit
	$app->post('/userEditById[/{user_id}]', 'UserController:userEditById');

// get event by status
$app->post('/eventByStatus', '\EventsController:eventByStatus');

// User Add 
	$app->post('/addUser', '\UserController:addUser');
 
// get event by id
	$app->get('/eventById[/{ev_id}]', 'EventsController:eventById');
 
// Add Event
$app->post('/addEvent', '\EventsController:addEvent');

// Get Event Checkin
	$app->post('/eventCheckInMember[/{ev_id}]', 'EventsController:eventCheckInMember');
 
// Get Event Attendees   By User Role               
	$app->get('/eventAttendeesByUserRole[/{ev_id}]', '\EventsController:eventAttendeesByUserRole');

// event location
$app->post('/picketLocations', '\LocationController:picketLocations');
    
// Get Home Page Data
	$app->get('/getHomeData', '\MainController:getHomeData');
 
// Edit Event
	$app->post('/editEventById[/{ev_id}]', '\EventsController:editEventById');
	 
// Edit Location
	$app->post('/editLocationById[/{loc_id}]', '\LocationController:editLocationById'); 
 
// Add Picket
	$app->post('/addPicket', '\PicketController:addPicket');

// get Picket by id
	$app->get('/getPicketById[/{pk_id}]', '\PicketController:getPicketById');
 
// Edit Picket
	$app->post('/editPicketById[/{pk_id}]', 'PicketController:editPicketById');
 
// get Picket by status
	$app->post('/picketByStatus', '\PicketController:picketByStatus');
 
// Get Event Checkin   
	$app->post('/checkinEvent[/{ev_id}]', 'EventsController:checkinEvent');
 
// Delete Picket
	$app->get('/deletePicket[/{pk_id}]', '\PicketController:deletePicket');

// Delete Location
$app->get('/deletePicketLocation[/{loc_id}]', '\LocationController:\deletePicketLocation');
 
// Edit Member
$app->post('/editMember[/{member_id}]', 'MembersController:editMember'); 
	  
// get Picket picketsignups 
	$app->post('/picketSignups[/{picked_id}]', '\PicketController:picketSignups');

// get Picket completedslots  
	$app->post('/picketCompletedSlots', '\PicketController:picketCompletedSlots');

// Add picket signup
	$app->post('/addPicketSignup[/{picked_id}]', '\PicketController:addPicketSignup');
// Picket Attendence picketattendancesummary
	$app->post('/picketAttendanceSummary[/{picked_id}]', '\PicketController:picketAttendanceSummary');

 //picketattendancebydate date format : 'Y-m-d'
	$app->post('/picketAttendanceByDate[/{picked_id}]', '\PicketController:picketAttendanceByDate');

// picketcheckin
	$app->post('/picketCheckin[/{picked_id}]', '\PicketController:picketCheckin');

// User reset password
	$app->get('/userResetPassword[/{email}]', 'UserController:userResetPassword');

// meta (check app verson end point)
$app->get('/getMetadata[/{key}]', '\MainController:getMetadata');

// GET Member By Member ID 
	$app->get('/getMemberById[/{member_id}]', '\MembersController:getMemberById');

// add union member and point (add by admin)
   $app->post('/addMemberByAdmin', '\MembersController:addMemberByAdmin');




/*Code For New Member __________________________*/ 
// GET Member By Member ID Picket

// Check Member Default Code.
// function memberdefaultcheck($MAuth){
	
// 	$MDfault = 'M-0123456789'; 
//     if($MAuth == $MDfault)
// 	{
// 		return true;
// 	}
// 	else
// 	{
// 		return false;
// 	}
// }

// Add Member Code
	$app->post('/addMemberData', '\MembersController:addMemberData');


// Member Login
	$app->post('/memberLogin', '\LoginController:memberLogin');
 
// Get Member By ID
	$app->get('/getMemberDataById[/{member_id}]', 'MembersController:getMemberDataById');

 
$app->get('/memberResetPassword[/{email}]', 'MembersController:memberResetPassword');



// Edit Member in member_data table...
	$app->post('/editMemberData[/{member_id}]', 'MembersController:editMemberData');


/*Code For New Member END __________________________*/ 


// Call IN Modual  Code//
																					
	$app->post('/callIns', '\MainController:callIns');


	$app->post('/addCallIn', '\MainController:addCallIn');


	$app->post('/callInsByStaff', '\MainController:callInsByStaff');


// Call IN Modual  Code END//

// Compny data end point //

$app->get('/getCompanies', '\MembersController:getCompanies');



 
// get data by user role
	$app->post('/getUsersByUserRole', '\UserController:getUsersByUserRole');
 

// get data by Member_Data role
	$app->post('/getMemberDataByRole', '\MembersController:getMemberDataByRole');


// Compny data end point //

$app->run();