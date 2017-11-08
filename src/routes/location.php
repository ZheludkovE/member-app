<?php

    // Event location By ID
    $app->get('/locationById[/{location_id}]', '\LocationController:locationById');  

    // Add Location
    $app->post('/addLocation', '\LocationController:addLocation');

    // Event location
    $app->post('/picketLocations', '\LocationController:picketLocations');

    // Edit Location
    $app->post('/editLocationById[/{loc_id}]', '\LocationController:editLocationById');

    // Delete Location
    $app->get('/deletePicketLocation[/{loc_id}]', '\LocationController:\deletePicketLocation');

?>