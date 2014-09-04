<?php
  require("conn.php");

  if($_POST['action']== "submit")
  {

    $InvID = $_POST['InvID'];
    if ($_POST['CreateDate'] =='')
    {
      $CreateDate = date("Y-m-d");
    }
    else
    {
      $CreateDate = $_POST['CreateDate'];
    }

    $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
    mysql_query("SET NAMES UTF8;", $conn);
    mysql_query("SET time_zone = '+08:00';", $conn);

    $Amount = $_POST['Amount'];
    $TransactionID = trim($_POST['TransactionID']);
    $ShippingDate = $_POST['ShippingDate'];
    $PayeeID = trim($_POST['PayeeID']);
    $Courier = trim($_POST['Courier']);
    $DeliverID = trim($_POST['DeliverID']);
    $DeliverDate = $_POST['DeliverDate'];
    $Postage = $_POST['Postage'];
    $Memo = trim($_POST['Memo']);
    $Memo = str_replace(' ','',$Memo);
    $Memo = str_replace(',','，',$Memo);

    $strsql="INSERT INTO Revenue (`InvID`, `CreateTime`, `Amount`, `TransactionID`, `TransactionTime`, `PayeeID`, `Memo`) VALUES ($InvID, '$CreateDate', '$Amount', '$TransactionID', '$CreateDate', '$PayeeID', '$Memo');";
    $r=mysql_db_query($mysql_database, $strsql, $conn);
    if ($Courier != "NA" && $Postage > 0)
    {
      $strsql="INSERT INTO Expense (`InvID`, `CreateTime`, `ExpenseType`, `Payee`, `RefID`, `ExpenseAmount`, `ExpenseRate`, `ExpenseCNY`, `Courier`, `DeliverID`, `DeliverDate`, `Memo`) VALUES ($InvID, '$ShippingDate', 'Express', 'taobao.com', '$TransactionID', '$Postage', '1', '$Postage', '$Courier', '$DeliverID', '$DeliverDate', '');";
      $r=mysql_db_query($mysql_database, $strsql, $conn);

      $strsql="UPDATE Inventory SET `Expense` = (SELECT SUM(`ExpenseCNY`) FROM  `Expense` WHERE InvID = ".$InvID.") WHERE InvID = ".$InvID.";";
      $r=mysql_db_query($mysql_database, $strsql, $conn);
    }
    
    $strsql = "SELECT SUM(`Amount`) FROM  `Revenue` WHERE InvID = ".$InvID.";";
    $r=mysql_db_query($mysql_database, $strsql, $conn);
    $sumrev = mysql_fetch_row($r);

    $strsql="UPDATE Inventory SET `Status` = 'Sold' WHERE InvID = ".$InvID.";";
    $r=mysql_db_query($mysql_database, $strsql, $conn);
    
    $strsql="UPDATE Inventory SET `Revenue` = (SELECT SUM(`Amount`) FROM  `Revenue` WHERE InvID = ".$InvID.") WHERE InvID = ".$InvID.";";
    $r=mysql_db_query($mysql_database, $strsql, $conn);

  }
  if ($sumrev[0] != "")
  {
    echo $sumrev[0];
  }
  else
  {
    echo "0";
  }
?>