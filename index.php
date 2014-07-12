<?php
session_start();

//If logged in, send user to main page 
if(isset($_SESSION['un']) && isset($_SESSION['userid'])) {
header('Location: main.php');
exit();
}

include 'databaseConnection.php';

//if username and password are posted, check if they are in database
if(($_POST["username"] != NULL) && (trim($_POST["username"]) != "") && ($_POST["password"] != NULL) && (trim($_POST["password"]) != "")) {
	$username = trim($_POST["username"]);
	$password = trim($_POST["password"]);
	$loginQuery = $db->prepare("SELECT Userid, PasswordHash FROM Users WHERE Username=:username");
	$loginQuery->execute(array(':username' => $username));
	$loginResult = $loginQuery->fetchAll(PDO::FETCH_ASSOC);
	$loginRow = $loginResult[0];
	
	if($loginRow != NULL) {
		$passwordHash = $loginRow['PasswordHash'];
		if(password_verify($password, $passwordHash)) {
			$userId = $loginRow["Userid"];
			$_SESSION['un'] = $username;
			$_SESSION['userid'] = $userId;
			header('Location: main.php');
			exit;
		}
		else
			$incorrect = true;
	}
//If they are not in database set bool for incorrect username or password
	else
		$incorrect = true;
}

//Close PDO link
$db = null;
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
Username: <input type="text" name="username" required><br><br>
Password: <input type="password" name="password" required><br><br>
<input type="submit" value="Submit"><br><br>
<br><a href="register.php">Register</a>
</form>
</body>
</html>
