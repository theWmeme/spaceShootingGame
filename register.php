<?php
    session_start();
    include "connect.php";

    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $name = $_POST['name'] ?? ''; 
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';


        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        $existing = $checkStmt->fetch();

        if($existing){
            echo "<script>alert('Email already has an account'); 
            window.location.href = 'login.php';
            </script>";
        }else{
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users(name, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$name, $email, $hashed_password]);
                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['username'] = $name;
                header("Location: game.php");
                exit;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }    
        }
         
    }

?>