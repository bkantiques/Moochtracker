<?php
session_start();

//If not logged in, send user to login page
if(!(isset($_SESSION['un']) && isset($_SESSION['userid']))) {
header('Location: index.php');
exit();
}


//include 'databaseConnection.php';
include 'pdotest.php'; 

$username = $_SESSION['un'];
$userid = $_SESSION['userid'];

//If post data for adding transaction is filled out, add it to database
if(($_POST["giveOrReceive"]=="give" || $_POST["giveOrReceive"]=="receive") && is_numeric($_POST["amount"]) && is_numeric($_SESSION["MoochID"])) {
	$giveOrReceive = $_POST["giveOrReceive"];
	$amount = $_POST["amount"];
	if($giveOrReceive == "receive")
		$amount *= -1;
	$moochID = $_SESSION["MoochID"];
	$note = trim($_POST["note"]);
	if($note != "" && $note != NULL) {
		$transactionQuery = $db->prepare("INSERT INTO Transactions(UserID, MoochID, Amount, DateTime, Note) VALUES (:userid, :moochID, :amount, now(), :note)");
		$transactionQuery->execute(array(':userid' => $userid, ':moochID' => $moochID, ':amount' => $amount, ':note' => $note));
	}
	else {
		$transactionQuery = $db->prepare("INSERT INTO Transactions(UserID, MoochID, Amount, DateTime) VALUES (:userid, :moochID, :amount, now())");
		$transactionQuery->execute(array(':userid' => $userid, ':moochID' => $moochID, ':amount' => $amount));
	}
	
}

//Get name and id of user's mooches
$moochQuery = $db->prepare("SELECT MoochID, Name FROM Mooches WHERE UserID=:userid");
$moochQuery->execute(array(':userid' => $userid));

?>
<!DOCTYPE html>
<html>
<head>
<title>Main Page</title>
<link rel="stylesheet" type="text/css" href="mainStyle.css">
</head>
<body>
<header><a href="main.php">Moochtracker</a></header>
<a id="logout" href="logout.php">Logout</a>
<h2>Mooches</h2>
<?php


$moochResult = $moochQuery->fetchAll(PDO::FETCH_ASSOC);
$moochRow = $moochResult[0];
if($moochRow == null)
	echo "<p>You have no mooches</p> \n";
else {
	echo "<p>Click on a mooch to view or add to transaction history</p>";
	echo "<table id='mooches'> \n";
	$i=1;

	while($moochRow != null) {
	$moochid= $moochRow["MoochID"];

	//Find amount owed by or to mooch
	$sumQuery = $db->prepare("SELECT SUM(Amount) FROM Transactions WHERE UserID=:userid AND MoochID=:moochid");
	$sumQuery->execute(array(':userid' => $userid, ':moochid' => $moochid));
	$sumResult = $sumQuery->fetchAll(PDO::FETCH_ASSOC);
	
	$sumRow = $sumResult[0];
	$sum = 0;
	if($sumRow)
		$sum += $sumRow['SUM(Amount)'];
	
	if($sum < 0)
		$sumStr = "Is owed \$" . abs($sum);
	else
		$sumStr = "Owes \$" . $sum;

	
	
	//Print out mooch list
	echo "<tr><td class='moochNameTotal' id='mooch" . $i . "' onmouseover='this.style.borderWidth=\"thick\"' onmouseout='if(prevMooch!=this.id)this.style.borderWidth=\"thin\"' onclick= 'displayTransactions(" . $moochRow['MoochID'] . ", " . "\"" . $moochRow['Name'] . "\", \"mooch" . $i . "\")' ><span class='moochName'>" . $moochRow['Name'] . "</span><span class='moochTotal'>" . $sumStr . "</span></td></tr> \n";
	
	$moochRow = $moochResult[$i];
	$i = $i + 1;
	}
	echo "</table> \n";
}

//Close database link
$db = null;
?>
<br>
<a href="addmooch.php">Add a mooch</a>
<span id="moochInfo"> 
</span>
<script>

	//initialize xmlhttp
	var xmlhttp;
	if (window.XMLHttpRequest)
		{// code for IE7+, Firefox, Chrome, Opera, Safari
  		xmlhttp=new XMLHttpRequest();
  		}
	else
  		{// code for IE6, IE5
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  		}

	//Stores ids to change border thickness when clicked
	var prevMooch = null;
	
	//mooch onclick function
	function displayTransactions(x , z, moochNum) {
	xmlhttp.open("POST", "moochInfo.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("MoochID=" + x + "&UserID=" + <?php echo $userid; ?> + "&MoochName=" + z);
	
	
	if(prevMooch != null)
		document.getElementById(prevMooch).style.borderWidth = "thin";
	prevMooch = moochNum;
	
	document.getElementById(moochNum).style.borderWidth = "thick";
}

//function when ajax response is ready
xmlhttp.onreadystatechange=function() 
	{
	if(xmlhttp.readyState==4 && xmlhttp.status==200)
		{
		document.getElementById("moochInfo").innerHTML=xmlhttp.responseText;
		}
	}

</script>

</body>
</html>
