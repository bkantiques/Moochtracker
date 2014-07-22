<?php
session_start();

//If not logged in, send user to login page
if(!(isset($_SESSION['un']) && isset($_SESSION['userid']))) {
header('Location: index.php');
exit();
}


include 'databaseConnection.php';

$username = $_SESSION['un'];
$userid = $_SESSION['userid'];

//If post data for adding transaction is filled out, add it to database
if(($_POST["giveOrReceive"]=="give" || $_POST["giveOrReceive"]=="receive") && is_numeric($_POST["amount"]) && is_numeric($_SESSION["MoochID"])) {
	$giveOrReceive = $_POST["giveOrReceive"];
	$amount = $_POST["amount"] * 100;
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
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">


<title>Main Page</title>
<!-- link rel="stylesheet" type="text/css" href="mainStyle.css" -->
</head>
<body>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
    <!-- Latest compiled and minified JavaScript -->
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

	<div class="container-fluid">	
		<div class="page header">
			<h1 class="text-center" id="logo">MOOCHTRACKER</h1>
			<div class="text-right hidden-xs hidden-sm">
				<a class="btn btn-default" href="logout.php">LOGOUT</a>
			</div>
			<div class="text-center visible-xs visible-sm">
				<a class="btn btn-default" href="logout.php">LOGOUT</a>
			</div>
		</div>

	


<?php


$moochResult = $moochQuery->fetchAll(PDO::FETCH_ASSOC);
$moochRow = $moochResult[0];
if($moochRow == null)
	echo "<p>You have no mooches</p> \n";
else {
	echo "<div class='col-lg-3 col-md-4 col-sm-5'>";
	echo "<h3>MOOCHES<br><small>Click on a mooch to view history</small></h3>";
	echo "<table class='table table-hover table-bordered' id='mooches'> \n";
	$i=1;

	//Loop through each of user's mooches and print out table
	while($moochRow != null) {
	$moochid= $moochRow["MoochID"];

	//Find amount owed by or to mooch
	$sumQuery = $db->prepare("SELECT SUM(Amount) FROM Transactions WHERE UserID=:userid AND MoochID=:moochid");
	$sumQuery->execute(array(':userid' => $userid, ':moochid' => $moochid));
	$sumResult = $sumQuery->fetchAll(PDO::FETCH_ASSOC);
	
	$sumRow = $sumResult[0];
	$sum = 0;
	if($sumRow)
		$sum += ($sumRow['SUM(Amount)'])/100;
		
	
	if($sum < 0) {
		$sum = number_format(abs($sum), 2);
		$sumStr = "Is owed \$" . $sum;}
	else {
		$sum = number_format($sum, 2);
		$sumStr = "Owes \$" . $sum;}
		

	
	
	//Print out mooch list
	echo "<tr><td class='moochNameTotal' id='mooch" . $i . "' onclick= 'displayTransactions(" . $moochRow['MoochID'] . ", \"mooch" . $i . "\")' ><span class='moochName'>" . htmlspecialchars($moochRow['Name']) . "</span><span class='moochTotal pull-right'>" . $sumStr . "</span></td></tr> \n";
	
	$moochRow = $moochResult[$i];
	$i = $i + 1;
	}
	echo "</table> \n";
	echo "<a class='btn btn-primary btn-block' href='addmooch.php'>ADD A MOOCH</a></div> \n";
}

//Close database link
$db = null;
?>
<div id="moochInfo" class="col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-lg-8 col-md-7 col-sm-6"> 
</div>
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
	function displayTransactions(x , moochNum) {
	xmlhttp.open("POST", "moochInfo.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("MoochID=" + x + "&UserID=" + <?php echo $userid; ?>);
	
	
	if(prevMooch != null)
		document.getElementById(prevMooch).className = "moochNameTotal";
	prevMooch = moochNum;
	
	document.getElementById(moochNum).className = "moochNameTotal active";
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
</div>
</body>
</html>
