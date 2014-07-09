<?php
session_start();

if(!(isset($_SESSION['un']) && isset($_SESSION['userid']))) {
header('Location: index.php');
exit();
}



include 'databaseConnection.php';

include 'funcs.php';

$userid = sanitize($_POST["UserID"]);
$moochid = sanitize($_POST["MoochID"]);
if(isset($_SESSION["MoochID"]))
	unset($_SESSION["MoochID"]);
$_SESSION["MoochID"] = $moochid;
$moochName = sanitize($_POST["MoochName"]);
$transactionQuery = "SELECT Amount, DateTime, Note FROM Transactions WHERE UserID=$userid AND MoochID=$moochid"; 
$transactionResult = mysql_query($transactionQuery);
$transactionRow = mysql_fetch_array($transactionResult);
if(!$transactionRow)
	echo "<p>You have no transactions with " . $moochName . "</p>";
else {
	$amount;
	$date;
	$note;
	$haveRows = true;
	echo "<h2>Transactions with " . $moochName . "</h2>";
	echo "<table><tr><td class='amount'>Amount</td><td class='date'>Date</td><td class='note'>Note</td></tr> \n";
	while($haveRows) {
		$amount = $transactionRow["Amount"];
		$date = $transactionRow["DateTime"];
		$note = $transactionRow["Note"];
		if($amount < 0)
			echo "<tr><td class='amount'>Received \$" . abs($amount) . " </td>";
		else
			echo "<tr><td class='amount'>Gave \$$amount </td>";
		echo "<td class='date'>$date</td><td class='note'>$note</td></tr>";
		$transactionRow = mysql_fetch_array($transactionResult);
		if(!$transactionRow)
			$haveRows = false;	
	}
	echo "</table>";	
}
echo "<h2>Add a transaction</h2>";
echo "<form action='main.php' method='POST'>";
echo "<label class='moochForm'>You </label> <select name='giveOrReceive'><option name=value='give'>give</option><option value='receive'>receive from</option></select> <label class='moochForm'>" . $moochName . "   \$</label> <input type='number' name='amount' min='.01' step='.01'>      <label class='moochForm' id='note'>Note: </label><textarea name='note' rows=4 cols=50 maxlength=250></textarea>  <input type='submit' id='addTransactionSubmit' value='Submit'>";
echo "</form>";

mysql_close($link);
?>
