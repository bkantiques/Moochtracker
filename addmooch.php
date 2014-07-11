<?php
session_start();

include 'databaseConnection.php';

if((!isset($_SESSION["un"])) || (!isset($_SESSION["userid"]))) {
	header('Location: logout.php');
	exit(); 
	}

if(isset($_POST['mooch_name'])) {
	$moochName = trim($_POST['mooch_name']);
	$userid = $_SESSION['userid'];
	//Check if you have any friends with this name already
	$friendCheckQuery = $db->prepare("SELECT COUNT(*) FROM Mooches WHERE UserID=:userid AND Name=:moochName");
	$friendCheckQuery->execute(array(":userid" => $userid, ":moochName" => $moochName));
	$friendCheckResult = $friendCheckQuery->fetchAll(PDO::FETCH_ASSOC);
	$friendCheckRow = $friendCheckResult[0];
	if($friendCheckRow['COUNT(*)'] == 1)
		echo "You already have a mooch named " . htmlspecialchars($moochName);
	else if($friend_check_row['COUNT(*)'] == 0) {
		if(trim($_POST['mooch_username']) != "" && $_POST['mooch_username'] != NULL) {
			$moochUsername = trim($_POST['mooch_username']);
			if($moochUsername == $_SESSION['un'])
				echo "Can't have a mooch with your own username";
			else {
			$usernameCheckQuery = $db->prepare("SELECT Userid FROM Users WHERE Username=:mooch_username");
			$usernameCheckQuery->execute(array(":mooch_username" => $moochUsername));
			$usernameCheckResult = $usernameCheckQuery->fetchAll(PDO::FETCH_ASSOC);
			$usernameCheckRow = $usernameCheckResult[0];
				
			if(!$usernameCheckRow)
				echo "No users with that username";
			else {
		
				$moochUserId = $usernameCheckRow['Userid'];
				$useridCheckQuery = $db->prepare("SELECT COUNT(*) FROM Mooches WHERE MoochUserID=:mooch_userid AND UserID=:userid");
				$useridCheckQuery->execute(array(":mooch_userid" => $moochUserId, "userid" => $userid));
				$useridCheckResult = $useridCheckQuery->fetchAll(PDO::FETCH_ASSOC);
				$useridCheckRow = $useridCheckResult[0];
				$userid_num = $useridCheckRow['COUNT(*)'];
				if($userid_num == 1)
					echo "You already have a mooch with that username";
				else {
					$insertQuery = $db->prepare("INSERT INTO Mooches (UserID, Name, MoochUserID) VALUES (:userid, :mooch_name, :mooch_userid)");
					$insertQuery->execute(array("userid" => $userid, ":mooch_name" => $moochName, ":mooch_userid" => $moochUserId));
					header('Location: main.php');
					exit();
							}
			
				
				
				
				}
			
			}
			}
			else {
				$nameInsertQuery = $db->prepare("INSERT INTO Mooches (UserID, Name) VALUES (:userid, :mooch_name)");
				$nameInsertQuery->execute(array("userid" => $userid, ":mooch_name" => $moochName));;
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
