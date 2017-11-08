<?php

    // Token
    $app->post('/adminLogin', '\LoginController:adminLogin');

    // Member Login
    $app->post('/memberLogin', '\LoginController:memberLogin');

?>