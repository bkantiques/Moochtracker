<?php
session_start();
if(isset($_SESSION['un']) && isset($_SESSION['userid'])) {
header('Location: main.php');
exit();
}

include 'databaseConnection.php';
include 'funcs.php'; 

if(($_POST["un"])!=NULL && ($_POST["un"])!="" && ($_POST["pwd"])!=NULL && ($_POST["pwd"])!= "") {
	$un = sanitize($_POST["un"]);
	$pwd = sanitize($_POST["pwd"]);
	$login_query = "SELECT Userid FROM Users WHERE Username='" . $un . "' AND Password='" . $pwd . "'";
	$login_result = mysql_query($login_query);
	$userid= mysql_fetch_array($login_result);
	if($userid) {
		$_SESSION['un'] = $un;
		$_SESSION['userid'] = $userid['Userid'];
		header('Location: main.php');
		exit;
	}
	else
		$incorrect = true;
}

mysql_close($link);
?>

<html>
<head>
<title>MoochTracker</title>
<link rel="stylesheet" type="text/css" href="otherStyle.css">
</head>
<body>
<header><a href="main.php">Moochtracker</a></header>
<?php
	if($incorrect)
		echo "<p>Incorrect username or password</p>";
?>
<form name="login" action="index.php" method="post">
Username: <input type="text" name="un" required><br><br>
Password: <input type="password" name="pwd" required><br><br>
<input type="submit" value="Submit"><br><br>
<br><a href="register.php">Register</a>
</form>
</body>
</html>
