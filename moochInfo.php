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
	echo "<p>You have no transactions with " . $moochName . "</p>";
else {
	$amount;
	$date;
	$note;
	$haveRows = true;
	$i = 1;
	echo "<h2>Transactions with " . $moochName . "</h2>";
	echo "<table><tr><td class='amount'>Amount</td><td class='date'>Date</td><td class='note'>Note</td></tr> \n";
	while($haveRows) {
		$amount = $transactionRow["Amount"];
		$date = $transactionRow["DateTime"];
		$note = htmlspecialchars($transactionRow["Note"]);
		if($amount < 0)
			echo "<tr><td class='amount'>Received \$" . abs($amount) . " </td>";
		else
			echo "<tr><td class='amount'>Gave \$$amount </td>";
		echo "<td class='date'>$date</td><td class='note'>$note</td></tr>";
		$transactionRow = $transactionResult[$i];
		$i++;
		if(!$transactionRow)
			$haveRows = false;	
	}
	echo "</table>";	
}
echo "<h2>Add a transaction</h2>";
echo "<form action='main.php' method='POST'>";
echo "<label class='moochForm'>You </label> <select name='giveOrReceive'><option name=value='give'>give</option><option value='receive'>receive from</option></select> <label class='moochForm'>" . htmlspecialchars($moochName) . "   \$</label> <input type='number' name='amount' min='.01' step='.01'>      <label class='moochForm' id='note'>Note: </label><textarea name='note' rows=4 cols=50 maxlength=250></textarea>  <input type='submit' id='addTransactionSubmit' value='Submit'>";
echo "</form>";

$db = null;
?>
