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

    $ExpenseType = $_POST['ExpenseType'];
    $Payee = trim($_POST['Payee']);
    $RefID = trim($_POST['RefID']);
    $ExpenseAmount = $_POST['ExpenseAmount'];
    $ExpenseRate = $_POST['ExpenseRate'];
    $ExpenseCNY = $_POST['ExpenseCNY'];
    $Courier = trim($_POST['Courier']);
    $DeliverID = trim($_POST['DeliverID']);
    $DeliverDate = $_POST['DeliverDate'];
    
    $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
    mysql_query("SET NAMES UTF8;", $conn);
    mysql_query("SET time_zone = '+08:00';", $conn);
    
    if ($Courier == 'NA')
    {
      $Inv_Status = "Delivered";
      $strsql = "UPDATE Inventory SET `Status` = 'Delivered' WHERE `InvID` = $InvID;";

      $r=mysql_db_query($mysql_database, $strsql, $conn);
    }
    $Memo = trim($_POST['Memo']);
    $Memo = str_replace(' ','',$Memo);
    $Memo = str_replace(',','，',$Memo);

    $strsql="INSERT INTO Expense (`InvID`, `CreateTime`, `ExpenseType`, `Payee`, `RefID`, `ExpenseAmount`, `ExpenseRate`, `ExpenseCNY`, `Courier`, `DeliverID`, `DeliverDate`, `Memo`) VALUES ($InvID, '$CreateDate', '$ExpenseType', '$Payee', '$RefID', '$ExpenseAmount', '$ExpenseRate', '$ExpenseCNY', '$Courier', '$DeliverID', '$DeliverDate', '$Memo');";
    $r=mysql_db_query($mysql_database, $strsql, $conn);

    $strsql = "SELECT SUM(`ExpenseCNY`) FROM  `Expense` WHERE InvID = ".$InvID.";";
    $r=mysql_db_query($mysql_database, $strsql, $conn);
    $sumexp = mysql_fetch_row($r);
  
    $strsql="UPDATE Inventory SET `Expense` = (SELECT SUM(`ExpenseCNY`) FROM  `Expense` WHERE InvID = ".$InvID.") WHERE InvID = ".$InvID.";";
    $r=mysql_db_query($mysql_database, $strsql, $conn);
    
    switch ($ExpenseType)
    {
      case "Tax":
        $ExpenseType = "Buy";
        break;
      case "Postage":
        if ($DeliverDate =='')
        {
          $strsql = "UPDATE Inventory SET `Status` = 'InTransit' WHERE `InvID` = $InvID;";
          $r=mysql_db_query($mysql_database, $strsql, $conn);
        }
        else
        {
          $strsql = "UPDATE Inventory SET `Status` = 'Delivered' WHERE `InvID` = $InvID;";
          $r=mysql_db_query($mysql_database, $strsql, $conn);
        }
        break;
      case "Express":
        $ExpenseType = "Express";
        $strsql = "UPDATE Inventory SET `Status` = 'Sold' WHERE `InvID` = $InvID;";
        $r=mysql_db_query($mysql_database, $strsql, $conn);
        break;
    }
    
		//mysql_free_result($r);
  }
  echo $sumexp[0];
?>