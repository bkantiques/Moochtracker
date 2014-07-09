<?php
session_start();

if(isset($_SESSION['un']) && isset($_SESSION['userid'])) {
header('Location: main.php');
exit();
}

include 'databaseConnection.php';

include 'funcs.php';

if(($_POST["username"] != NULL) && (trim($_POST["username"]) != "")) {
$un = sanitize($_POST["username"]);
$unquery="SELECT COUNT(Username) FROM Users WHERE Username='$un'";
$unresult = mysql_query($unquery);

$row=mysql_fetch_array($unresult);
$userswithusername= $row['COUNT(Username)'];


if($userswithusername > 0) {
	$unTaken = true;
}

else if($userswithusername == 0) {


$pw = sanitize($_POST["password"]);
$sql="INSERT INTO Users (Username, Password) VALUES ('$un', '$pw')";

$result = mysql_query($sql);

// Check result
// This shows the actual query sent to MySQL, and the error. Useful for debugging.
if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
		}
else {
	$useridquery = "SELECT Userid FROM Users WHERE Username='$un'";
	$useridresult = mysql_query($useridquery);
	$useridrow = mysql_fetch_array($useridresult);
	$userid = $useridrow['Userid'];
	$_SESSION['userid'] = $userid;
	$_SESSION['un'] = $un;
	
	header('Location: main.php');
	echo "problem2";
	exit();
		}	

	}
}

else {
	$noInfo = true;
}
mysql_close($link);

?>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" type="text/css" href="otherStyle.css">
</head>
<body>
<header><a href="main.php">Moochtracker</a></header>
<?php
if($unTaken)
	echo "<p>Username taken. Please try another username.</p>";
else if($noInfo)
	echo "<p>Please enter registration information</p>";
?>
<form action="http://moochtracker.com/register.php" method="POST">
Username: <input type="text" name="username" required><br><br>
Password: <input type="password" name="password" required><br><br>
<input type="submit" value="Submit"><br>
</form>

</body>
</html>
