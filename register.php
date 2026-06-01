<?php
    include "connect.php";

    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $name = mysqli_real_escape_string($conn, $_POST['name']); 
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);


        $checkEmail = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query("$checkEmail");

        if($result->num_rows > 0){
            echo "<script>alert('Email already has an account'); 
            window.location.href = 'login.php';
            </script>";
        }else{
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users(name,email,password) VALUES ('$name', '$email', '$hashed_password')"; 
            
            if($conn->query($sql)===TRUE){
                echo "Account Created";
                
            }   
            else{
                echo "Error".$sql.$conn->error;
            }    
        }
         
    }

?>