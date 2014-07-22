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
<link rel="stylesheet" type="text/css" href="mainStyle.css">
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

	<nav class="navbar navbar-inverse navbar-static-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>	
		
			</div>
		
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class ="nav navbar-nav navbar-right">
					<li class="active"><a href="#">Main Page</a></li>
					<li><a href="addmooch.php">Add A Mooch</a></li>
					<li><a href="logout.php">Logout</a></li>
				</ul>
			</div>
			<div class="text-center">
				<h1>MOOCHTRACKER</h1>
			</div>
		</div>
	</nav>
	
	


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
		$sum += $sumRow['SUM(Amount)'];
	
	if($sum < 0)
		$sumStr = "Is owed \$" . abs($sum);
	else
		$sumStr = "Owes \$" . $sum;

	
	
	//Print out mooch list
	echo "<tr><td class='moochNameTotal' id='mooch" . $i . "' onmouseover='this.style.borderWidth=\"thick\"' onmouseout='if(prevMooch!=this.id)this.style.borderWidth=\"thin\"' onclick= 'displayTransactions(" . $moochRow['MoochID'] . ", \"mooch" . $i . "\")' ><span class='moochName'>" . htmlspecialchars($moochRow['Name']) . "</span><span class='moochTotal'>" . $sumStr . "</span></td></tr> \n";
	
	$moochRow = $moochResult[$i];
	$i = $i + 1;
	}
	echo "</table> \n";
}

//Close database link
$db = null;
?>
<br>
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
	function displayTransactions(x , moochNum) {
	xmlhttp.open("POST", "moochInfo.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("MoochID=" + x + "&UserID=" + <?php echo $userid; ?>);
	
	
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
