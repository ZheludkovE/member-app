<?php

    // Get user by user ID
    $app->get('/getUserById[/{user_id}]', 'UserController:getUserById');
    
    // Get data by role
    $app->post('/getUsersByRole', 'UserController:getUsersByRole');

    // User Edit
    $app->post('/userEditById[/{user_id}]', 'UserController:userEditById');

    // User Add 
    $app->post('/addUser', '\UserController:addUser');

    // User reset password
    $app->get('/userResetPassword[/{email}]', 'UserController:userResetPassword');

    // Get data by user role
    $app->post('/getUsersByUserRole', '\UserController:getUsersByUserRole');
    
?>