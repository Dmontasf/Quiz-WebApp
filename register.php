<?php
require_once "config.php";
$db = getDB();

$username = "";
$password = "";
$confirmpassword = "";

$username_err = "";
$pw_err = "";
$confirm_pw_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a Username";
    } else if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "User names can only contain Letters, Numbers, and Underscore";
    } else {
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":username", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = "This username is already taken!";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
        }
        unset($stmt);
    }

    if (empty(trim($_POST["password"]))) {
        $pw_err = "Please enter a password";
    } else if (strlen(trim($_POST["password"])) < 8) {
        $pw_err = "Password must be at least 8 characters long!";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_pw_err = "Please confirm your password";
    } else {
        $confirmpassword = $_POST["confirm_password"];
        if (empty($pw_err) && $confirmpassword != $password) {
            $pw_err = "Passwords do not match!";
        }
    }

    if (empty($username_err) && empty($pw_err) && empty($confirm_pw_err)) {
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":username", $param_username);
            $stmt->bindParam(":password", $hashedpass);
            $param_username = trim($username);
            $hashedpass = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute()) {
                header("location: login.php");
                exit;
            } else {
                echo "Something went wrong!";
            }
        }
        unset($stmt);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow p-4">
        <h2 class="mb-3">Sign Up</h2>
        <p>Please fill out this form to create an account.</p>
        <form action="register.php" method="post" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text"
                       class="form-control <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                       name="username" value="<?= htmlspecialchars($username) ?>">
                <div class="invalid-feedback"><?= $username_err ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password"
                       class="form-control <?= (!empty($pw_err)) ? 'is-invalid' : ''; ?>"
                       name="password" value="<?= htmlspecialchars($password) ?>">
                <div class="invalid-feedback"><?= $pw_err ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password"
                       class="form-control <?= (!empty($confirm_pw_err)) ? 'is-invalid' : ''; ?>"
                       name="confirm_password" value="<?= htmlspecialchars($confirmpassword) ?>">
                <div class="invalid-feedback"><?= $confirm_pw_err ?></div>
            </div>

            <div class="d-grid">
                <input type="submit" class="btn btn-primary" value="Register">
            </div>
            <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
