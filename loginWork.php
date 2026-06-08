<?php
session_start();
include "connect.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
          $user = $result->fetch_assoc();

          if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            header("Location: game.php");
            exit;
          }
          else{
            echo "<script>alert('Wrong Password'); 
            window.location.href = 'login.php';
            </script>";
          }
        }
        else{
          echo "<script>alert('no user with that email exists!'); 
            window.location.href = 'login.php';
            </script>";
        }  
         
    }
 ?>