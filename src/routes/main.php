<?php
    
    // Get Home Page Data
    $app->get('/getHomeData', '\MainController:getHomeData');
    
    // Meta (check app verson end point)
    $app->get('/getMetadata[/{key}]', '\MainController:getMetadata');

    // Call In Modual  Code
	$app->post('/callIns', '\MainController:callIns');

	$app->post('/addCallIn', '\MainController:addCallIn');

	$app->post('/callInsByStaff', '\MainController:callInsByStaff');

?>