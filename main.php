<?php
session_start();

if(!(isset($_SESSION['un']) && isset($_SESSION['userid']))) {
header('Location: index.php');
exit();
}


include 'databaseConnection.php';

include 'funcs.php'; 


$un = $_SESSION['un'];
$userid = $_SESSION['userid'];

if(($_POST["giveOrReceive"]=="give" || $_POST["giveOrReceive"]=="receive") && is_numeric($_POST["amount"]) && is_numeric($_SESSION["MoochID"])) {
	$giveOrReceive = sanitize($_POST["giveOrReceive"]);
	$amount = sanitize($_POST["amount"]);
	if($giveOrReceive == "receive")
		$amount *= -1;
	$moochID = $_SESSION["MoochID"];
	$note = sanitize($_POST["note"]);
	if($note != "" && $note != NULL)
	$transactionQuery = "INSERT INTO Transactions(UserID, MoochID, Amount, DateTime, Note) VALUES ($userid, $moochID, $amount, now(), '$note')";
	else
	$transactionQuery = "INSERT INTO Transactions(UserID, MoochID, Amount, DateTime) VALUES ($userid, $moochID, $amount, now())";
	mysql_query($transactionQuery);
	
}

$moochquery = "SELECT MoochID, Name FROM Mooches WHERE UserID='$userid'";
$moochresult = mysql_query($moochquery);
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



$moochrow = mysql_fetch_array($moochresult);
if(!$moochrow)
	echo "<p>You have no mooches</p> \n";
else {
	echo "<p>Click on a mooch to view or add to transaction history</p>";
	echo "<table id='mooches'> \n";
	$moochbool = true;
	$i=1;
	while($moochbool) {
	//Find amount owed by or to mooch
	$sumQuery = "SELECT SUM(Amount) FROM Transactions WHERE UserID='$userid' AND MoochID='" . $moochrow['MoochID'] . "'";
	$sumResult = mysql_query($sumQuery);
	$sumRow = mysql_fetch_array($sumResult);
	$sum = 0;
	if($sumRow)
		$sum += $sumRow['SUM(Amount)'];
	
	if($sum < 0)
		$sumStr = "Is owed \$" . abs($sum);
	else
		$sumStr = "Owes \$" . $sum;

	
	
	//Print out mooch list
	echo "<tr><td class='moochNameTotal' id='mooch" . $i . "' onmouseover='this.style.borderWidth=\"thick\"' onmouseout='if(prevMooch!=this.id)this.style.borderWidth=\"thin\"' onclick= 'displayTransactions(" . $moochrow['MoochID'] . ", " . $userid . ", " . "\"" . $moochrow['Name'] . "\", \"mooch" . $i . "\")' ><span class='moochName'>" . $moochrow['Name'] . "</span><span class='moochTotal'>" . $sumStr . "</span></td></tr> \n";
	$i = $i + 1;
	$moochrow = mysql_fetch_array($moochresult);
	if(!$moochrow) 
		$moochbool = false;
	}
	echo "</table> \n";
}
mysql_close($link);
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
	function displayTransactions(x , y, z, moochNum) {
	xmlhttp.open("POST", "moochInfo.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("MoochID=" + x + "&UserID=" + y + "&MoochName=" + z);
	
	
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
