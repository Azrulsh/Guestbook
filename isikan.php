<!-- isikan.php -->
<!-- To insert data of masuk.php into database. -->
<?php

include("dbase.php");

//Dapatkan Tarikh Dan Masa Masuk
$nama = $_POST['nama'];
$email = $_POST['email'];
$komen = $_POST['komen'];
$tarikh = date("d-m-Y", time());
$masa = date("H:i:s", time());
$query = "INSERT INTO book (nama,email,tarikh,masa,komen) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $tarikh, $masa, $komen);

if (mysqli_stmt_execute($stmt)) {
      
   echo "<script type='text/javascript'> window.location='papar.php' </script>";
	
} else {
    echo "Could not insert guestbook entry.";
}




?>
