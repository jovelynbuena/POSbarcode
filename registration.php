<?php
include_once 'connectdb.php';
session_start();

if ($_SESSION['useremail'] == "" || $_SESSION['role'] == "User") {
    header('location:../index.php');
    exit(); // Added exit to prevent further execution
}

if ($_SESSION['role'] == "Admin") {
    include_once "header.php";
} else {
    include_once "headeruser.php";
}

error_reporting(0);

$id = isset($_GET['id']) ? $_GET['id'] : null; // Used isset to avoid undefined index notice

if (isset($id)) {
    $delete = $pdo->prepare("DELETE FROM `tbl_user` WHERE `tbl_user`.`userid` = :userid");
    $delete->bindParam(':userid', $id);

    if ($delete->execute()) {
        $_SESSION['status'] = "Account deleted successfully";
        $_SESSION['status_code'] = "success";

    } else {
        $_SESSION['status'] = "Something went wrong";
        $_SESSION['status_code'] = "warning";
    }
}

if (isset($_POST['btnsave'])) {
    $username = $_POST['txtname'];
    $useremail = $_POST['txtemail'];
    $userpassword = $_POST['txtpassword'];
    $userrole = $_POST['txtselect_option'];
    $useraddress = $_POST['txtaddress'];
    $userage = $_POST['txtage'];
    $usercontact = $_POST['txtcontact'];

    if ($userage < 18) {
        $_SESSION['status'] = "Sorry, 18 years old below are not allowed to create an account";
        $_SESSION['status_code'] = "warning";
    } else {
        $selectEmail = $pdo->prepare("SELECT useremail FROM tbl_user WHERE useremail = :useremail");
        $selectEmail->bindParam(':useremail', $useremail);
        $selectEmail->execute();

        if ($selectEmail->rowCount() > 0) {
            $_SESSION['status'] = "Email already exists. Please create a new email";
            $_SESSION['status_code'] = "warning";
        } else {
            $selectPassword = $pdo->prepare("SELECT userpassword FROM tbl_user WHERE userpassword = :userpassword");
            $selectPassword->bindParam(':userpassword', $userpassword);
            $selectPassword->execute();

            if ($selectPassword->rowCount() > 0) {
                $_SESSION['status'] = "Password already exists. Please create a new password";
                $_SESSION['status_code'] = "warning";
            } else {
                $insert = $pdo->prepare("INSERT INTO tbl_user (username, useremail, userpassword, role, address, age, contact) VALUES (:username, :useremail, :userpassword, :role, :address, :age, :contact)");

                $insert->bindParam(':username', $username);
                $insert->bindParam(':useremail', $useremail);
                $insert->bindParam(':userpassword', $userpassword);
                $insert->bindParam(':role', $userrole);
                $insert->bindParam(':address', $useraddress);
                $insert->bindParam(':age', $userage);
                $insert->bindParam(':contact', $usercontact);

                if ($insert->execute()) {
                    $_SESSION['status'] = "Insert successfully into the database";
                    $_SESSION['status_code'] = "success";
                } else {
                    $_SESSION['status'] = "Error inserting into the database";
                    $_SESSION['status_code'] = "error";
                }
            }
        }
    }
}

if (isset($_POST['btnupdate'])) {
    $userid = $_POST['userid'];
    $username = $_POST['txtname'];
    $useremail = $_POST['txtemail'];
    $userpassword = $_POST['txtpassword'];
    $userrole = $_POST['txtselect_option'];
    $useraddress = $_POST['txtaddress'];
    $userage = $_POST['txtage'];
    $usercontact = $_POST['txtcontact'];

    $update = $pdo->prepare("UPDATE tbl_user SET username=:username, useremail=:useremail, userpassword=:userpassword, role=:role, address=:address, age=:age, contact=:contact WHERE userid=:userid");

    $update->bindParam(':userid', $userid);
    $update->bindParam(':username', $username);
    $update->bindParam(':useremail', $useremail);
    $update->bindParam(':userpassword', $userpassword);
    $update->bindParam(':role', $userrole);
    $update->bindParam(':address', $useraddress);
    $update->bindParam(':age', $userage);
    $update->bindParam(':contact', $usercontact);

    if ($update->execute()) {
        $_SESSION['status'] = "Update successfully";
        $_SESSION['status_code'] = "success";
    } else {
        $_SESSION['status'] = "Error updating the record";
        $_SESSION['status_code'] = "error";
    }
}
?>

<!-- Add edit form here -->
<form id="editForm" action="" method="post" style="display: none;">
    <input type="hidden" id="editUserId" name="userid">
    <input type="text" class="form-control" placeholder="Enter Name" name="txtname" id="txtname" required>
    <input type="email" class="form-control" placeholder="Enter email" name="txtemail" id="txtemail" required>
    <!-- Add other form fields here -->
    <button type="submit" class="btn btn-primary" name="btnupdate">Update</button>
</form>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Registration</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="row">
                            
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">


                </div>
                <!-- /.col-md-6 -->
                <div class="col-lg-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Registration</h5>
                        </div>
                        <div class="card-body">


                            <div class="row">

                                <div class="col-lg-4">

                                    <form action="" method="post">

                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Name</label>
                                            <input type="text" class="form-control" placeholder="Enter Name" name="txtname" required>
                                        </div>


                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Email address</label>
                                            <input type="email" class="form-control" placeholder="Enter email" name="txtemail" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Contact</label>
                                            <input type="text" class="form-control" placeholder="Enter Contact" name="txtcontact" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Age</label>
                                            <input type="text" class="form-control" placeholder="Enter Age" name="txtage" required>
                                        </div>


                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Address</label>
                                            <input type="text" class="form-control" placeholder="Enter Address" name="txtaddress" required>
                                        </div>


                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Password</label>
                                            <input type="password" class="form-control" placeholder="Password" name="txtpassword" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Role </label>

                                    <select class="form-control" name="txtselect_option" required>
                                        <option value="" disabled selected>Select Role</option>
                                        <option>Admin</option>
                                        <option>User</option>

                                    </select>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="btnsave">Save</button>
                                </div>
                                
                            </form>




                        </div>


                        <div class="col-lg-8">

                            <table class="table table-striped table-hover">


                                <thead>


                                    <tr>
                                        <td>#</td>
                                        <td>Name</td>
                                        <td>Email</td>
                                        <td>Password</td>
                                        <td>Role</td>
                                        <td>Address</td>
                                        <td>Age</td>
                                        <td>Contact</td>
                                        <td>Edit</td>
                                        <td>Delete</td>



                                    </tr>

                                </thead>

                                <?php

                                $select = $pdo->prepare("select * from tbl_user order by userid ASC");
                                $select->execute();

                                while ($row = $select->fetch(PDO::FETCH_OBJ)) {

                                    echo '
                                    <tr>
                                    <td>' . $row->userid . '</td>
                                    <td>' . $row->username . '</td>
                                    <td>' . $row->useremail . '</td>
                                    <td>' . $row->userpassword . '</td>
                                    <td>' . $row->role . '</td>
                                    <td>' . $row->address . '</td>
                                    <td>' . $row->age . '</td>
                                    <td>' . $row->contact . '</td>

                                    <td>
                                <a href="edit.php?id='.$row->userid.'" class="btn btn-success " role="button"><span class="fa fa-edit" style="color:#ffffff" data-toggle="tooltip" title="Edit Registration"></span></a>
</td>
                                    <td>
                                    <a href="registration.php?id=' . $row->userid . '" class="btn btn-danger delete-btn" data-id="' . $row->userid . '"><i class="fa fa-trash-alt"></i></a>
                                    </td>
                                    </tr>';

                                }


                        
                                ?>


                                <tbody>

                                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                                    <script>
                                        $(document).ready(function() {
                                            $('.delete-btn').click(function(e) {
                                                e.preventDefault();

                                                var userId = $(this).data('id');

                                                Swal.fire({
                                                    title: 'Confirmation',
                                                    text: 'Are you sure you want to delete this? Once deleted, you will not be able to recover this account!',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d63032',
                                                    confirmButtonText: 'Yes, delete it!'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = 'registration.php?id=' + userId;
                                                    }
                                                });
                                            });
                                        });
                                    </script>

                                </tbody>


                            </table>




                        </div>





                    </div>



                </div>


            </div>
        </div>


    </div>
    <!-- /.col-md-6 -->
</div>
<!-- /.container-fluid -->
</div>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->



<?php

include_once "footer.php";


?>


<?php

if (isset($_SESSION['status']) &&  $_SESSION['status'] != '') {

?>



    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['status_code']; ?>',
            title: '<?php echo $_SESSION['status']; ?>'
        })
    </script>

<?php
    unset($_SESSION['status']);
}


?>
