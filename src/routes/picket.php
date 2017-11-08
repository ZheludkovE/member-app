<?php

    // Add Picket
	$app->post('/addPicket', '\PicketController:addPicket');
    
    // Get Picket by id
    $app->get('/getPicketById[/{pk_id}]', '\PicketController:getPicketById');
     
    // Edit Picket
    $app->post('/editPicketById[/{pk_id}]', 'PicketController:editPicketById');
     
    // Get Picket by status
    $app->post('/picketByStatus', '\PicketController:picketByStatus');
     
    // Delete Picket
    $app->get('/deletePicket[/{pk_id}]', '\PicketController:deletePicket');

    // Get Picket picketsignups 
	$app->post('/picketSignups[/{picked_id}]', '\PicketController:picketSignups');
    
    // Get Picket completedslots  
    $app->post('/picketCompletedSlots', '\PicketController:picketCompletedSlots');
    
    // Add picket signup
    $app->post('/addPicketSignup[/{picked_id}]', '\PicketController:addPicketSignup');

    // Picket Attendence picketattendancesummary
    $app->post('/picketAttendanceSummary[/{picked_id}]', '\PicketController:picketAttendanceSummary');
    
    //picketattendancebydate date format : 'Y-m-d'
    $app->post('/picketAttendanceByDate[/{picked_id}]', '\PicketController:picketAttendanceByDate');
    
    // Picket Checkin
    $app->post('/picketCheckin[/{picked_id}]', '\PicketController:picketCheckin');

?>