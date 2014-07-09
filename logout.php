<?php
session_start();

if(isset($_SESSION["un"])) 
session_destroy();

header('Location: index.php');
exit();
?>
