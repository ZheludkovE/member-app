<?php

    // Edit Member
    $app->post('/editMember[/{member_id}]', 'MembersController:editMember'); 

    // Get Member By Member ID 
	$app->get('/getMemberById[/{member_id}]', '\MembersController:getMemberById');
    
    // Add union member and point (add by admin)
    $app->post('/addMemberByAdmin', '\MembersController:addMemberByAdmin');

    // Add Member Code
	$app->post('/addMemberData', '\MembersController:addMemberData');

    // Get Member By ID
	$app->get('/getMemberDataById[/{member_id}]', 'MembersController:getMemberDataById');
    
    // Member reset password
    $app->get('/memberResetPassword[/{email}]', 'MembersController:memberResetPassword');

    // Edit Member in member_data table...
	$app->post('/editMemberData[/{member_id}]', 'MembersController:editMemberData');

    // Get all companies 
    $app->get('/getCompanies', '\MembersController:getCompanies');

    // Get data by Member_Data role
	$app->post('/getMemberDataByRole', '\MembersController:getMemberDataByRole');

?>