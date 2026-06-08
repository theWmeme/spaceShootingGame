<?php
session_start();
include "connect.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user){
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