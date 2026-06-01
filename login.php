<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Space Shooting Game Login In Page</title>
  <link rel="stylesheet" href="style.css">
  
</head>
<body class="login-page">
    <div class="container" id="container">
      <div class="form-box" id="login-form">
        <form action="loginWork.php" method="POST">
          <h2>Login</h2>
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit">Login</button>
          <p style="margin:12px 0 0;text-align:center;color:#c9d2e6">Don't have an account? <a href="#" id="show-signup">Sign Up</a></p>
        </form>
      </div>

      <div class="form-box" id="sign-up-form">
        <form action="register.php" method="POST">
          <h2>Sign Up</h2>
          <input type="text" name="name" placeholder="Name" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit">Sign Up</button>
        </form>
        <p>If you already have an account, you can <a href="#" id="show-login">Login</a></p>
      </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
