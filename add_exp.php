<?php
//
//旧版增加支出
//
  $InvID = $_GET['invid'];
?>
<form id="add_exp">
<input type="hidden" name="InvID" value="<?php echo $InvID; ?>">
<input type="hidden" name="action" value="submit">
<table>
  <tr>
    <th width="20px">ID</th>
    <th width="25px">类型</th>
    <th>创建日期</th>
    <th>收款方</th>
    <th id="th_RefID">RefID</th>
    <th>当地货币</th>
    <th>币别</th>
    <th>汇率</th>
    <th>人民币</th>
    <th id="th_Courier">快递</th>
    <th id="th_DeliverID">运单号</th>
    <th id="th_DeliverDate">收货日期</th>
    <th>备注</th>
    <th>操作</th>
  </tr>
  <tr>
    <td width="20px"><?php echo $InvID; ?></td>
    <td>
      <select id="ExpenseType" name="ExpenseType" onchange="update_expensetype(this.value);">
        <option value="Tax">税费</option>
        <option value="Postage">转运费</option>
        <option value="Express">快递费</option>
      </select>
    </td>
    <td><input type="text" name="CreateDate" style="width:70px" onkeydown="nextfocus();" /></td>
    <td><input type="text" id="Payee" name="Payee" style="width:70px" onkeydown="nextfocus();"/></td>
    <td id="td_RefID"><input type="text" id="RefID" name="RefID" style="width:80px" onkeydown="nextfocus();"/></td>
    <td><input type="text" id="ExpenseAmount" name="ExpenseAmount" style="width:45px" onkeydown="nextfocus();" onblur="cacl_cny();" /></td>
    <td>
      <select id="RateSelector" onchange="select_currency(this.value)" onblur="select_currency(this.value)">
        <option value="USD">USD</option>
        <option value="EUR">EUR</option>
        <option value="CNY">CNY</option>
      </select>
    </td>
    <td><input type="text" id="ExpenseRate" name="ExpenseRate" style="width:30px" onkeydown="nextfocus();" onblur="cacl_cny();" /></td>
    <td><input type="text" id="ExpenseCNY" name="ExpenseCNY" style="width:55px" onkeydown="nextfocus();" onfocus="cacl_cny();" /></td>
    <td>
      <select id="Courier" name="Courier" onchange="update_type();">
        <option value=""></option>
        <option value="UPS">UPS</option>
        <option value="USPS">USPS</option>
        <option value="DHL.DE">DHL.DE</option>
        <option value="EMS">EMS</option>
        <option value="STO">申通</option>
        <option value="YTO">圆通</option>
        <option value="YUNDA">韵达</option>
        <option value="SFE">顺丰</option>
        <option value="GTO">国通</option>
        <option value="UC56">优速</option>
        <option value="QUANFENG">全峰</option>

        <option value="Other">其他</option>
      </select>
    </td>
    <td><input type="text" name="DeliverID" style="width:80px" onkeydown="nextfocus();" /></td>
    <td><input type="text" name="DeliverDate" style="width:70px" onkeydown="nextfocus();" /></td>
    <td><textarea name="Memo"/></textarea></td>
    <td><input type="button" onclick="add_expense(<?php echo $InvID; ?>)" value="Submit"></td>
  </tr>
</table>
</form>
