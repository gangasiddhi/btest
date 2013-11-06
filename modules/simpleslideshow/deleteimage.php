<?php 
    $image = $_GET['image'];
    
    //prevent delete the server
    if (!preg_match('/^[a-z0-9.\-_%]+?\.(png|jpe?g|gif)$/i', $image))
        return;
    
    if (file_exists('./slides/' . $image)) 
        unlink('./slides/' . $image);
    
?>