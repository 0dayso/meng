<?php
  $InvID = $_GET['invid'];
?>
<form id="add_rev">
<input type="hidden" name="InvID" value="<?php echo $InvID; ?>">
<input type="hidden" name="action" value="submit">
<table style="text-align:center">
  <tr>
    <th>ID</th>
    <th>交易日期</th>
    <th>成交金额</th>
    <th>交易号</th>
    <th>旺旺ID</th>
    <th>承运人</th>
    <th>运单号</th>
    <th>运费</th>
    <th>发货时间</th>
    <th>送抵时间</th>
    <th>备注</th>
    <th>操作</th>
  </tr>
  <tr>
    <td width="20px"><?php echo $InvID; ?></td>
    <td><input type="text" name="CreateDate" style="width:70px" onkeydown="nextfocus();" /></td>
    <td><input type="text" name="Amount" style="width:60px" onkeydown="nextfocus();" onblur="toCurrency(this);"/></td>
    <td><input type="text" name="TransactionID" style="width:100px" onkeydown="nextfocus();"/></td>
    <td><input type="text" name="PayeeID" style="width:70px" onkeydown="nextfocus();"/></td>
    <td>
      <select id="Courier" name="Courier" onchange="update_type();">
        <option value=""></option>
        <option value="NA">自提</option>
        <option value="STO">申通</option>
        <option value="ZTO">中通</option>
        <option value="SFE">顺丰</option>
        <option value="Other">其他</option>
      </select>
    </td>
    <td><input type="text" name="DeliverID" style="width:100px" onkeydown="nextfocus();" /></td>
    <td><input type="text" name="Postage" style="width:60px" onkeydown="nextfocus();" onblur="toCurrency(this);"/></td>
    <td><input type="text" name="ShippingDate" style="width:70px" onkeydown="nextfocus();" /></td>
    <td><input type="text" name="DeliverDate" style="width:70px" onkeydown="nextfocus();" /></td>
    <td><textarea name="Memo"/></textarea></td>
    <td><input type="button" onclick="add_revenue(<?php echo $InvID; ?>)" value="Submit"></td>
  </tr>
</table>
</form>