<?php
include_once 'connectdb.php';
session_start();

include_once "header.php";

function fill_product($pdo){
  $output='';
  $select=$pdo->prepare("SELECT * FROM tbl_product ORDER BY product ASC");
  $select->execute();
  $result=$select->fetchAll();
  foreach($result as $row){
    $output.='<option value="'.$row['pid'].'">'.$row['product'].'</option>';
  }
  return $output;
}

if (isset($_POST['btnsaveorder'])) {
  $orderdate      = date('Y-m-d');
  $subtotal       = $_POST['txtsubtotal'];
  $discount       = $_POST['txtdiscount'];
  $sgst           = $_POST['txtsgst'];
  $cgst           = $_POST['txtcgst'];
  $total          = $_POST['txttotal'];
  $payment_type   = $_POST['rb'];
  $due            = $_POST['txtdue'];
  $paid           = $_POST['txtpaid'];
  

  $arr_pid     = $_POST['pid_arr'];
  $arr_barcode = $_POST['barcode_arr'];
  $arr_name    = $_POST['product_arr'];
  $arr_stock   = $_POST['stock_c_arr'];
  $arr_qty     = $_POST['quantity_arr'];
  $arr_price   = $_POST['price_c_arr'];
  $arr_total   = $_POST['saleprice_arr'];

    

  // Insert invoice data into tbl_invoice table
  $insert = $pdo->prepare("INSERT INTO tbl_invoice(order_date, subtotal, discount, sgst, cgst, total, payment_type, due, paid) VALUES (:order_date, :subtotal, :discount, :sgst, :cgst, :total, :payment_type, :due, :paid)");
  $insert->bindParam(':order_date', $orderdate);
  $insert->bindParam(':subtotal', $subtotal);
  $insert->bindParam(':discount', $discount);
  $insert->bindParam(':sgst', $sgst);
  $insert->bindParam(':cgst', $cgst);
  $insert->bindParam(':total', $total);
  $insert->bindParam(':payment_type', $payment_type);
  $insert->bindParam(':due', $due);
  $insert->bindParam(':paid', $paid);
  $insert->execute();

  $invoice_id = $pdo->lastInsertId();

  if($invoice_id != null){
    // Process invoice details and update stock
    for($i = 0; $i < count($arr_pid); $i++){
      $rem_qty = $arr_stock[$i] - $arr_qty[$i];
      if($rem_qty < 0){
        echo "Order is not completed"; // Handle this case appropriately
      }else{
        $update = $pdo->prepare("UPDATE tbl_product SET stock = :rem_qty WHERE pid = :pid");
        $update->bindParam(':rem_qty', $rem_qty);
        $update->bindParam(':pid', $arr_pid[$i]);
        $update->execute();
      }
      
      // Insert invoice details into tbl_invoice_details table
      $insert_detail = $pdo->prepare("INSERT INTO tbl_invoice_details (invoice_id, barcode, product_id, product_name, qty, rate, saleprice, order_date) VALUES (:invid, :barcode, :pid, :name, :qty, :rate, :saleprice, :order_date)");
      $insert_detail->bindParam(':invid', $invoice_id);
      $insert_detail->bindParam(':barcode', $arr_barcode[$i]);
      $insert_detail->bindParam(':pid', $arr_pid[$i]);
      $insert_detail->bindParam(':name', $arr_name[$i]);
      $insert_detail->bindParam(':qty', $arr_qty[$i]);
      $insert_detail->bindParam(':rate', $arr_price[$i]);
      $insert_detail->bindParam(':saleprice', $arr_total[$i]);
      $insert_detail->bindParam(':order_date', $orderdate);
      
      if(!$insert_detail->execute()){
        print_r($insert_detail->errorInfo()); // Print error information if execution fails
      }
    }
  }
}

$select = $pdo->prepare("SELECT * FROM tbl_taxdis");
$select->execute();
$row = $select->fetch(PDO::FETCH_OBJ);

?>



<style type="text/css">

.tableFixHead{
overflow: scroll;
height : 520px;
}
.tableFixHead thead th {
position: sticky;
top:0;
z-index: 1;

}
table {border-collapse: collapse; width: 100px;}
th,td{padding:8px 16px;}
th{background:   #eee;} 

</style>



  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) --> 
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <!-- <h1 class="m-0">Point of Sales</h1> -->
          </div><!-- /.col -->
          <div class="col-sm-6">
           
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
          
     

           
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-12">
          <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">POS</h5>
              </div>
              <div class="card-body">


              <div class="row">

<div class="col-md-8">
<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text"><i class="fa fa-barcode"></i></span>
    </div>
    
    <input type="text" class="form-control" placeholder="Scan Barcode" autocomplete="off" name="txtbarcode" id="txtbarcode_id">


    <form action="" method="post" name="">


</div>
    <select class="form-control select2" data-dropdown-css-class="select2-purple" style="width: 100%;">
   
        <option> Select or Search </option><?php echo fill_product($pdo);?>
        <!-- <option>Alaska</option>
        <option>California</option>
        <option>Delaware</option>
        <option>Tennessee</option>
        <option>Texas</option>
        <option>Washington</option> -->
    </select>

</br>
<div class="tableFixHead">
    <table id="producttable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock</th>
                <th>Price</th>
                <th>QTY</th>
                <th>Total</th>
                <th>Del</th>
            </tr>
        </thead>
        <tbody class="details" id="itemtable"></tbody>
        <tr data-widget="expandable-table" aria-expanded="false">
        </tr>
    </table>
</div> 

</div>      



         
<div class="col-md-4"> 

<div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">SUBTOTAL(₱)</span>
                  </div>
                  <input type="text" class="form-control" name="txtsubtotal"  id="txtsubtotal_id" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div>

                <div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text">DISCOUNT(%)</span>
    </div>
    <input type="text" class="form-control" name="txtdiscount"id="txtdiscount_p"value="<?php echo $row->discount; ?>">

    <div class="input-group-append">
        <span class="input-group-text">%</span>
    </div>
</div>


                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">DISCOUNT(₱)</span>
                  </div>
                  <input type="text" class="form-control" id="txtdiscount_n"  readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div> 

                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">SGST(%)</span>
                  </div>
                  <input type="text" class="form-control" name="txtsgst" id="txtsgst_id_p" value="<?php echo $row->sgst; ?>" readonly >
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
                </div> 


                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">CGST(%)</span> 
                  </div>
                  <input type="text" class="form-control" name="txtcgst"id="txtcgst_id_p" value="<?php echo $row->cgst; ?>"readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
                </div> 

                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">SGST(₱)</span>
                  </div>
                  <input type="text" class="form-control" id="txtsgst_id_n" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div> 

                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">CGST(₱)</span>
                  </div>
                  <input type="text" class="form-control" id="txtcgst_id_n" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div>  

<hr style="height:2px; border-width:0; color:black; background-color:black; ">

<div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Total(₱)</span>
                  </div>
                  <input type="text" class="form-control form-control-lg total" name="txttotal" id="txttotal" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div>  

                <hr style="height:2px; border-width:0; color:black; background-color:black; ">

                <div class="form-group clearfix">
                      <div class="icheck-success d-inline">
                        <input type="radio" name="rb" value="Cash" checked id="radioSuccess1">
                        <label for="radioSuccess1">
                          CASH
                        </label>
                      </div>
                      <div class="icheck-primary d-inline">
                        <input type="radio" name="rb"value="Card"  id="radioSuccess2">
                        <label for="radioSuccess2">
                          CARD
                        </label>
                      </div>
                      <div class="icheck-danger d-inline">
                        <input type="radio" name="rb" value="Check"  id="radioSuccess3">
                        <label for="radioSuccess3">
                          CHECK
                        </label>
                      </div>
                      <hr style="height:2px; border-width:0; color:black; background-color:black; ">
 


                      <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">DUE(₱)</span>
                  </div>
                  <input type="text" class="form-control" name="txtdue"id="txtdue" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
                </div> 



                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">PAID(₱)</span>
                  </div>
                  <input type="text" class="form-control" name="txtpaid" id="txtpaid" >
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
             </div> 

             
             <hr style="height:2px; border-width:0; color:black; background-color:black; ">

<div class="card-footer">
 

  

  
 <div class="card-footer">
                        <div class="text-center">
                                    <button type="submit" class="btn btn-success" name="btnsaveorder">Save Order</button>
                                </div>
 
 </div>

</div>
</div>

           </form>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  


  <?php

include_once"footer.php";


?>


<script>
    // Initialize Select2 Elements
    $('.select2').select2()

    var productarr = [];
    $(function () {
        $('#txtbarcode_id').on('change', function () {
            var barcode = $('#txtbarcode_id').val();
            $.ajax({
                url: "getproduct.php",
                method: "GET",
                dataType: "json",
                data: { id: barcode },
                success: function (data) {
                    if ($.inArray(data["pid"], productarr) !== -1) {
                        // Update quantity and total for the existing product
                        var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
                        $('#qty_id' + data["pid"]).val(actualqty);
                        var saleprice = actualqty * data["saleprice"];
                        $('#saleprice_id' + data["pid"]).html(saleprice);
                        $('#saleprice_idd' + data["pid"]).val(saleprice);
                        // $("#txtbarcode_id").val("");
                    } else {
                        // Add a new row for the product
                        addRow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);
                        productarr.push(data["pid"]);
                        $("#txtbarcode_id").val("");
                        calculate(0,0)

                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        addrow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);

        function addRow(pid, product, saleprice, stock, barcode) {
            var newRow = '<tr>' +

            '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' +barcode+ '"></td>' +

'<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]"  <span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '"><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '"> </td>' +

'<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + pid + '">' + stock + '<span><input type="hidden" class="form-control stock_C" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

'<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + pid + '">' + saleprice + '<span><input type="hidden" class="form-control price_C" name="price_c_arr[]" id="price_idd' + pid + '" value="' + saleprice + '"></td>' +

'<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="' + 1 + '" size="1"></td>' +

'<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name=netamt_arr[]" id="saleprice_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +

//  '<td style="text-align:left; vertical-align:middle; font-size:17px;"><center><name="remove" class="btnremove" data-id="'+pid+'"><span class="fas fa-trash" style="color:red"></span></center></td>'+

'<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + pid + '"><span class="fas fa-trash"></span></center></td>' +


'</tr>'; // '<td style="text-align:left; vertical-align:middle;">' +
                // '<center>' +
                // '<button name="remove" class="btnremove" data-id="' + pid + '">' +
                // '<span class="fas fa-trash" style="color:red"></span>' +
                // '</button>' +
                // '</center>' +
                // '</td>' +
                // '</tr>';


                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="'+pid+'"><span class="fas fa-trash"></span></center></td>' +
              
                '</tr>';
            $('#itemtable').append(newRow);

            calculate(0,0)
        }
    });








    var productarr = [];
    $(function () {
        $('.select2').on('change', function () {
            var productid = $('.select2').val();
            $.ajax({
                url: "getproduct.php",
                method: "GET",
                dataType: "json",
                data: { id: productid },
                success: function (data) {
                    if ($.inArray(data["pid"], productarr) !== -1) {
                        // Update quantity and total for the existing product
                        var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
                        $('#qty_id' + data["pid"]).val(actualqty);
                        var saleprice = actualqty * data["saleprice"];
                        $('#saleprice_id' + data["pid"]).html(saleprice);
                        $('#saleprice_idd' + data["pid"]).val(saleprice);
                        // $("#txtbarcode_id").val("");
                        calculate(0,0)

                    } else {
                        // Add a new row for the product
                        addRow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);
                        productarr.push(data["pid"]);
                        // $("#txtbarcode_id").val("");
                        calculate(0,0)
 
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        

        function addRow(pid, product, saleprice, stock, barcode) {
            var newRow = '<tr>' +
            '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' + barcode + '">' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;">' +
                '<span class="badge badge-dark">' + product + '</span>' +
                '<input type="hidden" class="form-control product" name="pid_arr[]" value="' + pid + '">' +
                '<input type="hidden" class="form-control pid" name="product_arr[]" value="' + product + '">' +
                '</td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;">' +
                '<span class="badge badge-primary stocklbl" name="stock_arr[]"  id="stock_id' + pid + '">' + stock + '</span>' +
                '<input type="hidden" class="form-control stock_c" name="stock_c[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;">' +
                '<span class="badge badge-warning price" name="price_arr[]"  id="price_id' + pid + '">' + saleprice + '</span>' +
                '<input type="hidden" class="form-control stock_c" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="1" size="1"></td>' +
                '<td style="text-align:left; vertical-align:middle; font-size:17px;">' +
                '<span class="badge badge-success totalamt" name="netamt_arr[]"  id="saleprice_id' + pid + '">' + saleprice + '</span>' +
                '<input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +
                
                // '<td style="text-align:left; vertical-align:middle;">' +
                // '<center>' +
                // '<button name="remove" class="btnremove" data-id="' + pid + '">' +
                // '<span class="fas fa-trash" style="color:red"></span>' +
                // '</button>' +
                // '</center>' +
                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="'+pid+'"><span class="fas fa-trash"></span></center></td>' +
              
                '</tr>';

                $('#itemtable').append(newRow);
            calculate(0,0)
            $("#txtbarcode_id").val("");
        }
    });

    $(document).on("keyup change", "#itemtable .qty", function(){
    var quantity = $(this).val();
    var tr = $(this).closest('tr');
    var price = parseFloat(tr.find(".price").text());
    var oldTotal = parseFloat(tr.find(".totalamt").text());

    if (parseInt(quantity) > parseInt(tr.find(".stock_c").val())) {
        // Quantity exceeds stock
        Swal.fire({
            icon: 'warning',
            title: 'WARNING!',
            text: 'Quantity IS NOT AVAILABLE'
        });
        tr.find(".qty").val(1); // Set quantity back to 1
        quantity = 1; // Update the quantity variable
    }
    
    // Calculate new total
    var newTotal = quantity * price;
    tr.find(".totalamt").text(newTotal.toFixed(2));
    tr.find(".saleprice").val(newTotal.toFixed(2));

    calculate(0,0); // Recalculate totals
});




function calculate(dis, paid) {
    var subtotal = 0;
    var discountPercentage = dis; // Rename the discount variable to avoid confusion
    var sgst = 0;
    var cgst = 0;
    var total = 0;
    var paid_amt = paid;
    var due = 0;

    $(".saleprice").each(function() {
        subtotal += ($(this).val() * 1);
    });
    $("#txtsubtotal_id").val(subtotal.toFixed(2));

    sgst = parseFloat($("#txtsgst_id_p").val());
    cgst = parseFloat($("#txtcgst_id_p").val());
    discountPercentage = parseFloat($("#txtdiscount_p").val()); // Rename the discount variable to avoid confusion

    sgst = sgst / 100;
    sgst = sgst * subtotal;

    cgst = cgst / 100;
    cgst = cgst * subtotal;

    var discountAmount = (discountPercentage / 100) * subtotal; // Calculate discount amount from percentage

    $("#txtdiscount_n").val(discountAmount.toFixed(2)); // Display discount amount in currency format

    $("#txtsgst_id_n").val(sgst.toFixed(2));
    $("#txtcgst_id_n").val(cgst.toFixed(2));

    total = sgst + cgst + subtotal - discountAmount; // Deduct discount amount from total
    due = total - paid_amt; // Subtract paid amount from total to get due amount

    $("#txttotal").val(total.toFixed(2));
    $("#txtdue").val(due.toFixed(2));
}


$("#txtdiscount_p").keyup(function() {
    var discount = $(this).val();
    var paid = $("#txtpaid").val();
    calculate(discount, paid);
});

$("#txtpaid").keyup(function() {
    var paid = $(this).val();
    var discount = $("#txtdiscount_n").val();
    calculate(discount, paid);
});

$(document).on('click', '.btnremove', function() {
    var removed = $(this).attr("data-id");
    $(this).closest('tr').remove(); // Remove the closest table row
    productarr = jQuery.grep(productarr, function(value) {
        return value != removed;
    });
    calculate(0, 0); // Recalculate totals after removal
});





</script>

