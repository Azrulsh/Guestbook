<!-- buang.php -->
<!-- To delete one particular data. -->

<?php

include ("dbase.php");

$idURL = $_GET['id'];

$query = "DELETE FROM book WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $idURL);
$result = mysqli_stmt_execute($stmt) or die ("Could not execute query in buang.php");

if($result){
echo "<script type= 'text/javascript'> window.location='papar.php'</script>";
}
?>
