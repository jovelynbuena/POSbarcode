<?php
ob_start();
include_once 'connectdb.php';
session_start();

include_once "header.php";

$id = $_GET['id'];

// Fetch the product data from the database
$select  = $pdo->prepare("select * from tbl_product where pid =$id");
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

// Store the existing image filename in a variable
$existing_image = $row['image'];

// Initialize variables for form inputs
$barcode_db = $row['barcode'];
$product_db = $row['product'];
$category_db = $row['category'];
$description_db = $row['description'];
$stock_db = $row['stock'];
$purchaseprice_db = $row['purchaseprice'];
$saleprice_db = $row['saleprice'];

// Check if the form is submitted
if (isset($_POST['btneditproduct'])) {
    // Retrieve form inputs
    $product_txt = $_POST['txtproductname'];
    $category_txt = $_POST['txtselect_option'];
    $description_txt = $_POST['txtdescription'];
    $stock_txt = $_POST['txtstock'];
    $purchaseprice_txt = $_POST['txtpurchaseprice'];
    $saleprice_txt = $_POST['txtsaleprice'];

    // Check if a new image is uploaded
    if (isset($_FILES['myfile']) && isset($_FILES['myfile']['name']) && !empty($_FILES['myfile']['name'])) {
        $f_name = $_FILES['myfile']['name'];
        $f_tmp = $_FILES['myfile']['tmp_name'];
        $f_size = $_FILES['myfile']['size'];
        $f_extension = explode('.', $f_name);
        $f_extension = strtolower(end($f_extension));
        $f_newfile = uniqid() . '.' . $f_extension;
        $store = 'productimages/' . $f_newfile;

        // Prepare the update query with the new image filename
        $update = $pdo->prepare("update tbl_product set product=:product, category=:category, description=:description, stock=:stock, purchaseprice=:pprice, saleprice=:sprice,image=:image where pid=$id");

        // Bind parameters
        $update->bindParam(':product', $product_txt);
        $update->bindParam(':category', $category_txt);
        $update->bindParam(':description', $description_txt);
        $update->bindParam(':stock', $stock_txt);
        $update->bindParam(':pprice', $purchaseprice_txt);
        $update->bindParam(':sprice', $saleprice_txt);
        $update->bindParam(':image', $f_newfile);

        // Check if the update query executes successfully
        if ($update->execute()) {
            // Move the uploaded file to the destination directory
            if (move_uploaded_file($f_tmp, $store)) {
                // Delete the old image file if it exists
                if (!empty($existing_image)) {
                    unlink('productimages/' . $existing_image);
                }
                $_SESSION['status'] = "Product Updated with New Image successfully";
                $_SESSION['status_code'] = "success";
            } else {
                $_SESSION['status'] = "Failed to upload image";
                $_SESSION['status_code'] = "error";
            }
        } else {
            $_SESSION['status'] = "Update failed";
            $_SESSION['status_code'] = "error";
        }
    } else {
        // If no new image is uploaded, update only the product information without changing the image
        $update = $pdo->prepare("update tbl_product set product=:product, category=:category, description=:description, stock=:stock, purchaseprice=:pprice, saleprice=:sprice where pid=$id");

        // Bind parameters
        $update->bindParam(':product', $product_txt);
        $update->bindParam(':category', $category_txt);
        $update->bindParam(':description', $description_txt);
        $update->bindParam(':stock', $stock_txt);
        $update->bindParam(':pprice', $purchaseprice_txt);
        $update->bindParam(':sprice', $saleprice_txt);

        // Check if the update query executes successfully
        if ($update->execute()) {
            $_SESSION['status'] = "Product Updated successfully";
            $_SESSION['status_code'] = "success";
        } else {
            $_SESSION['status'] = "Update failed";
            $_SESSION['status_code'] = "error";
        }
    }
}

// Re-fetch the product data from the database after update
$select  = $pdo->prepare("select * from tbl_product where pid =$id");
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

// Update variables with latest product data
$barcode_db = $row['barcode'];
$product_db = $row['product'];
$category_db = $row['category'];
$description_db = $row['description'];
$stock_db = $row['stock'];
$purchaseprice_db = $row['purchaseprice'];
$saleprice_db = $row['saleprice'];
$image_db = $row['image'];

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <!-- <h1 class="m-0">Admin Dashboard</h1> -->
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
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Edit Product</h5>
                        </div>
                        <form action="" method="post" name="formeditproduct" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Barcode</label>
                                            <input type="text" class="form-control" value="<?php echo $barcode_db = $row['barcode']; ?>" placeholder="Enter Barcode" name="txtbarcode" autocomplete="off" disabled>
                                        </div>
                                        <div class="form-group">
                                            <label>Product Name</label>
                                            <input type="text" class="form-control" value="<?php echo $product_db; ?>" placeholder="Enter Product Name" name="txtproductname" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select class="form-control" name="txtselect_option" required>
                                                <option value="" disabled selected>Select Category</option>
                                                <?php
                                                $select = $pdo->prepare("select * from tbl_category order by catid desc");
                                                $select->execute();
                                                while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                                                    extract($row);
                                                ?>
                                                    <option value="<?php echo $row['category']; ?>" <?php echo ($row['category'] == $category_db) ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $row['category']; ?>
                                                    </option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea class="form-control" placeholder="Enter Description" name="txtdescription" rows="4" required><?php echo $description_db; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Stock Quantity</label>
                                            <input type="number" min="1" step="any" class="form-control" value="<?php echo $stock_db; ?>" placeholder="Enter Stock" name="txtstock" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <label>Purchase Prize</label>
                                            <input type="number" min="1" step="any" class="form-control" value="<?php echo $purchaseprice_db; ?>" placeholder="Enter stock" name="txtpurchaseprice" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <label>Sale Price</label>
                                            <input type="number" min="1" step="any" class="form-control" value="<?php echo $saleprice_db; ?>" placeholder="Enter Stock" name="txtsaleprice" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <label>Product Image</label> <br>
                                            <img src="productimages/<?php echo $image_db; ?>" class="img-rounded" width="50px" height="50px">
                                            <input type="file" class="input-group" name="myfile">
                                            <p>Upload Image</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success" name="btneditproduct">Update Product</button>
                                </div>
                            </div>
                        </form>
                    </div>
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
include_once "footer.php";
if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    echo "<script>
        Swal.fire({
            icon: '" . $_SESSION['status_code'] . "',
            title: '" . $_SESSION['status'] . "'
        });
    </script>";
    unset($_SESSION['status']);
}
?>
