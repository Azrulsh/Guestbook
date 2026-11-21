<!--kemaskini.php-->
<!--To update data of ubah.php into database.-->
<?php
include ("dbase.php");

$nama = $_POST['nama'];
$email = $_POST['email'];
$komen = $_POST['komen'];
$id = $_POST['id'];

//Dapatkan Tarikh Dan Masa Masuk
$tarikh = date("d-m-Y", time());
$masa = date("H:i:s", time());

$query = "UPDATE book SET nama = ?, email = ?, tarikh = ?, masa = ?, komen = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $email, $tarikh, $masa, $komen, $id);

$result = mysqli_stmt_execute($stmt) or die ("Could not execute query in kemaskini.php");
if($result){
 echo "<script type = 'text/javascript'> window.location='papar.php' </script>";
}
?>
