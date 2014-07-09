<?php
session_start();

//Check if already logged in
if(isset($_SESSION['un']) && isset($_SESSION['userid'])) {
header('Location: main.php');
exit();
}

//Include 'databaseConnection.php';
include 'pdotest.php';

//Check if username is taken
if(($_POST["username"] != NULL) && (trim($_POST["username"]) != "") && ($_POST["password"] != NULL) && (trim($_POST["password"]) != "" )) {
$username = trim($_POST["username"]);
$usernameQuery= $db->prepare("SELECT Username FROM Users WHERE Username=:username");
$usernameQuery->execute(array(':username' => $username));

$usersWithUsername= $usernameQuery->rowCount();

//If taken, set bool to print error message later
if($usersWithUsername > 0) {
	$unTaken = true;
}

//Otherwise insert username and password into database
else if($usersWithUsername == 0) {
$password = trim($_POST["password"]);
$insertQuery= $db->prepare("INSERT INTO Users (Username, Password) VALUES (:username, :password)");
$insertQuery->execute(array(':username' => $username, ':password' => $password));

//Get user id   
	$userIdQuery = $db->prepare("SELECT Userid FROM Users WHERE Username=:username");
	$userIdQuery->execute(array(':username' => $username));
	$userIdResult = $userIdQuery->fetchAll(PDO::FETCH_ASSOC);
	$userIdRow = $userIdResult[0];
	$userId = $userIdRow["Userid"];
	
//Store id and username in session
	$_SESSION['userid'] = $userId;
	$_SESSION['un'] = $username;

//Send user to main page	
	header('Location: main.php');
	exit();
	}	

}

//If username and password are not set in post, set bool to print message later
else {
	$noInfo = true;
}
?>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" type="text/css" href="otherStyle.css">
</head>
<body>
<header><a href="main.php">Moochtracker</a></header>
<?php
//Message if username is taken
if($unTaken)
	echo "<p>Username taken. Please try another username.</p>";
//Message if nothing sent in post
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
