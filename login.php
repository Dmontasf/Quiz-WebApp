<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]===true) {
    header('location:quiz_select.php');
    exit;
}

require_once "config.php";
$db = getDB();
$username = "";
$password = "";

$username_err = "";
$pw_err = "";
$login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST['password']))) {
        $pw_err = "Please enter your password!";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($username_err) && empty($pw_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":username", $param_username);
            $param_username = trim($username);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $username = $row['username'];
                        $hashed_pw = $row['password'];
                        if (password_verify($password, $hashed_pw)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION['username'] = $username;
                            header('location: quiz_select.php');
                        } else {
                            $login_err = "Invalid password";
                        }
                    }
                } else {
                    $login_err = "User not found, please register for an account.";
                }
            }
        }
        unset($stmt);
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow p-4 login-card w-100" style="max-width: 400px;">
        <h2 class="text-center mb-3">Log In</h2>
        <p class="text-center text-muted">Please fill out this form to log in</p>

        <?php if(!empty($login_err)): ?>
            <div class="alert alert-danger"><?= $login_err ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" name="username" value="<?= $username ?>">
                <div class="invalid-feedback"><?= $username_err ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control <?= (!empty($pw_err)) ? 'is-invalid' : ''; ?>" name="password" value="<?= $password ?>">
                <div class="invalid-feedback"><?= $pw_err ?></div>
            </div>

            <div class="d-grid mb-3">
                <input type="submit" class="btn btn-primary" value="Log In">
            </div>
            <p class="text-center">Don't have an account? <a href="register.php">Sign up now!</a></p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
