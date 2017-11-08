<?php

    // Get Event Attendees By field
    $app->post('/eventAttendeesByField[/{ev_id}]', 'EventsController:eventAttendeesByField');

    // Get event by status
    $app->post('/eventByStatus', '\EventsController:eventByStatus');

    // Get event by id
	$app->get('/eventById[/{ev_id}]', 'EventsController:eventById');
 
    // Add Event
    $app->post('/addEvent', '\EventsController:addEvent');

    // Get Event Checkin
	$app->post('/eventCheckInMember[/{ev_id}]', 'EventsController:eventCheckInMember');
 
    // Get Event Attendees   By User Role               
    $app->get('/eventAttendeesByUserRole[/{ev_id}]', '\EventsController:eventAttendeesByUserRole');
    
    // Edit Event
    $app->post('/editEventById[/{ev_id}]', '\EventsController:editEventById');
    
    // Get Event Checkin   
	$app->post('/checkinEvent[/{ev_id}]', 'EventsController:checkinEvent');

?>