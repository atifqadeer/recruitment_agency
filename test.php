<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$db = "sms";
// $db = "dbs202487";
$con='';
$sql='';
$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
if(! $conn )  
{  
  die('Could not connect: ' . mysqli_error());  
}  
echo 'Connected successfully';  
	$message = isset($_GET['message']) ? $_GET['message'] : null;
	$phoneNumber = isset($_GET['phoneNumber']) ? $_GET['phoneNumber'] : null ;
	if($message!='' && $phoneNumber!='')
	{
// 		$sql = "INSERT INTO sms_rec (applicant_id, user_id, msg_id,message,phone_number,date,time,status,is_read)
// VALUES ('17903', '1', 'z1643245489875','message receive text aftab','07960007442','2021-12-29','13:44:50','incoming','0')";
$sql = "INSERT INTO sms_rec (message, phone, description)
VALUES ('$message', '$phoneNumber', 'test by aftab from crm')";
	}
	

if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}


?>
