<?php
$servername ="localhost";
$db_name="iwanderph_db";
$password="";
$username="root";

$conn= new mysqli($servername,$username,$password,$db_name);

if($conn->connect_error){
    die("Connection failed".$conn->connect_error);
}else{
}
?>