<?php
session_start();

if(!(isset($_SESSION['un']) && isset($_SESSION['userid']))) {
header('Location: index.php');
exit();
}

include 'databaseConnection.php';

$userid = $_POST["UserID"];
$moochid = $_POST["MoochID"];
if(isset($_SESSION["MoochID"]))
	unset($_SESSION["MoochID"]);
$_SESSION["MoochID"] = $moochid;
$moochNameQuery = $db->prepare("SELECT Name FROM Mooches WHERE MoochID=:moochid");
$moochNameQuery->execute(array(":moochid" => $moochid));
$moochNameResult = $moochNameQuery->fetchAll(PDO::FETCH_ASSOC);
$moochNameRow = $moochNameResult[0];
$moochName = $moochNameRow["Name"];

//Get transactions between user and selected mooch
$transactionQuery = $db->prepare("SELECT Amount, DateTime, Note FROM Transactions WHERE UserID=:userid AND MoochID=:moochid"); 
$transactionQuery->execute(array(":userid" => $userid, ":moochid" => $moochid));
$transactionResult = $transactionQuery->fetchAll(PDO::FETCH_ASSOC);
$transactionRow = $transactionResult[0];
if(!$transactionRow)
	echo "<h3>YOU HAVE NO TRANSACTION WITH " . $moochName . "</h3>";
else {
	$amount;
	$date;
	$note;
	$haveRows = true;
	$i = 1;
	echo "<h3>TRANSACTIONS WITH " . $moochName . "</h3>";
	echo "<table class='table table-bordered'><thead><tr><th class='amount'>Amount</th><th class='date'>Date</th><th class='note'>Note</th></tr></thead> \n";
	while($haveRows) {
		$amount = $transactionRow["Amount"];
		$amount= $amount/100;
		$date = $transactionRow["DateTime"];
		$timest = strtotime($date);
		$date = date("D M j, Y    g:i A", $timest);
		$note = htmlspecialchars($transactionRow["Note"]);
		if($amount < 0) {
			$amount= number_format(abs($amount), 2);
			echo "<tr><td class='amount'>Received \$" . $amount . " </td>";}
		else {
			$amount= number_format($amount, 2);
			echo "<tr><td class='amount'>Gave \$$amount </td>";}
		echo "<td class='date'>$date</td><td class='note'>$note</td></tr>";
		$transactionRow = $transactionResult[$i];
		$i++;
		if(!$transactionRow)
			$haveRows = false;	
	}
	echo "</table>";	
}
echo "<h3>ADD A TRANSACTION</h3>";
echo "<form class='form-inline' role='form' action='main.php' method='POST'>";
echo "<div class='form-group'><select name='giveOrReceive' class='form-control'><option value='give'>You give</option><option value='receive'>You receive</option></select></div> <div class='form-group'><div class='input-group'><span class='input-group-addon'>\$</span><input type='number' class='form-control' name='amount' min='.01' step='.01'></div></div'>      <div class='form-group'><label class='moochForm' id='note'>Note: </label><textarea name='note' rows=1 class='form-control'></textarea></div><input type='submit' class='btn btn-primary' id='addTransactionSubmit' value='SUBMIT TRANSACTION'>";
echo "</form>";

$db = null;
?>
