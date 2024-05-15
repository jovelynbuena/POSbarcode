<?php
ob_start();
include_once 'connectdb.php';
session_start();

if($_SESSION['useremail']==""){
    header('location:../index.php');
    exit; // Add exit to stop further execution
}

include_once "header.php";

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $select  = $pdo->prepare("SELECT * FROM tbl_user WHERE userid = ?");
    $select->execute([$id]);

    $row = $select->fetch(PDO::FETCH_ASSOC);

    
    if(isset($_POST['btnedit'])) {
        $name = $_POST['txtname'];
        $email = $_POST['txtemail'];
        $contact = $_POST['txtcontact'];
        $age = $_POST['txtage'];
        $address = $_POST['txtaddress'];
        $role = $_POST['txtselect_option'];
        $password = $_POST['txtpassword'];
    
        // Check if the user's email already exists
        $checkEmail = $pdo->prepare("SELECT * FROM tbl_user WHERE useremail = ?");
        $checkEmail->execute([$email]);
        $existingEmail = $checkEmail->fetch(PDO::FETCH_ASSOC);
    
        // Check if the age is under a certain value
        if($age < 18) { // Assuming the minimum age is 18
            $_SESSION['status'] = "Age should be at least 18";
            $_SESSION['status_code'] = "warning";
        }
        // Check if the email already exists
        elseif($existingEmail && $existingEmail['userid'] != $id) {
            $_SESSION['status'] = "Email already exists";
            $_SESSION['status_code'] = "warning";
        }
        else {
            // Proceed with updating the user's information
            $update = $pdo->prepare("UPDATE tbl_user SET username = ?, useremail = ?, userpassword = ?, address = ?, age = ?, contact = ?, role = ? WHERE userid = ?");
            $success = $update->execute([$name, $email, $password, $address, $age, $contact, $role, $id]);
           
            if($success) {
                $_SESSION['status'] = "User updated successfully";
                $_SESSION['status_code'] = "success";
                header('refresh:1; http://localhost/posbarcode/ui/registration.php');
            } else {
                $_SESSION['status'] = "Failed to update user";
                $_SESSION['status_code'] = "error";
            }
        }
    }
}
?>


<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Edit Form</h5>
                        </div>

                        <form action="" method="post" name="formedit">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Name</label>
                                    <input type="text" class="form-control" value="<?php echo $row['username']; ?>" placeholder="Enter Name" name="txtname" required>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email address</label>
                                    <input type="email" class="form-control" value="<?php echo $row['useremail']; ?>" placeholder="Enter email" name="txtemail" required>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputPassword1">Password</label>
                                <input type="password" class="form-control" value="<?php echo $row['userpassword']; ?>" placeholder="Password" name="txtpassword" required>
</div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Contact</label>
                                    <input type="text" class="form-control" value="<?php echo $row['contact']; ?>" placeholder="Enter Contact" name="txtcontact" required>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Age</label>
                                    <input type="text" class="form-control" value="<?php echo $row['age']; ?>" placeholder="Enter Age" name="txtage" required>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Address</label>
                                    <input type="text" class="form-control" value="<?php echo $row['address']; ?>"  placeholder="Enter Address" name="txtaddress" required>
                                </div>

                                <div class="form-group">
                                    <label>Role</label>
                                    <select class="form-control" name="txtselect_option" required>
                                        <option value="" disabled>Select Role</option>
                                        <option value="Admin" <?php echo ($row['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="User" <?php echo ($row['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success" name="btnedit">Edit Form</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once "footer.php";

if(isset($_SESSION['status']) && $_SESSION['status'] != '') {
    echo "<script>
        Swal.fire({
            icon: '".$_SESSION['status_code']."',
            title: '".$_SESSION['status']."'
        });
    </script>";
    unset($_SESSION['status']);
}
?>
