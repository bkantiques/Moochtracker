<?php
session_start();

include 'databaseConnection.php';

include 'funcs.php'; 

if(isset($_POST['mooch_name'])) {
	$mooch_name = sanitize($_POST['mooch_name']);
	$userid = sanitize($_SESSION['userid']);
	$friend_check_query = "SELECT COUNT(*) FROM Mooches WHERE UserID='$userid' AND Name='$mooch_name'";
	$friend_check_result = mysql_query($friend_check_query);
	$friend_check_row = mysql_fetch_array($friend_check_result);
	//var_dump($friend_check_row);
	if($friend_check_row['COUNT(*)'] === "1")
		echo "You already have a mooch named " . $mooch_name;
	else if($friend_check_row['COUNT(*)'] === "0") {
		if($_POST['mooch_username'] != "" && $_POST['mooch_username'] != NULL) {
			$mooch_username = sanitize($_POST['mooch_username']);
			if($mooch_username == $_SESSION['un'])
				echo "Can't have a mooch with your own username";
			else {
			$username_check_query = "SELECT Userid FROM Users WHERE Username='$mooch_username'";
			$username_check_result = mysql_query($username_check_query);
			$username_check_row = mysql_fetch_array($username_check_result);
				
			if($username_check_row == FALSE)
				echo "No users with that username";
			else {
		
				$mooch_userid = $username_check_row['Userid'];
				$userid_check_query = "SELECT COUNT(*) FROM Mooches WHERE MoochUserID='$mooch_userid' AND UserID='$userid'";
				$userid_check_result = mysql_query($userid_check_query);
				$userid_check_row = mysql_fetch_array($userid_check_result);
				$userid_num = $userid_check_row['COUNT(*)'];
				if($userid_num === "1")
					echo "You already have a mooch with that username";
				else {
					$insert_query = "INSERT INTO Mooches (UserID, Name, MoochUserID) VALUES ('$userid', '$mooch_name', '$mooch_userid')";
					mysql_query($insert_query);
					header('Location: main.php');
					exit();
							}
			
				
				
				
				}
			
			}
			}
			else {
				$name_insert_query = "INSERT INTO Mooches (UserID, Name) VALUES ('$userid', '$mooch_name')";
				mysql_query($name_insert_query);
				header('Location: main.php');
				echo 'Problem';
				}
			
			
		}
}


mysql_close($link);
?>

<html>
<head>
<title>Add Mooch</title>
<link rel="stylesheet" type="text/css" href="otherStyle.css">
</head>
<body>
<header><a href="main.php">Moochtracker</a></header>
<form action="addmooch.php" method="post">
Mooch's name: <input type="text" name="mooch_name" required>
Mooch's username (optional): <input type="text" name="mooch_username">
<input type="submit" value="Submit">
</form>
</body>
</html>
