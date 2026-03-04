<?php
    declare(strict_types=1);
    // Start the session
    session_start();
    // Destroy all session data
    session_destroy();
    // Redirect to home page
    header("Location: home_Page.php");
    $_SESSION = [];
    session_destroy();
    // exit the script
    exit();
    
?>
