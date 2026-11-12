
<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (
        empty($name) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 6
    ) {

        $error = "Enter valid information. Password must have at least 6 characters.";

    } else {

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->execute([$email]);

        if ($check->fetch()) {

            $error = "Email already exists.";

        } else {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users(name,email,password)
                 VALUES(?,?,?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hash
            ]);

            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["user_name"] = $name;

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

<div class="auth-card">

    <h1>Car Parking System</h1>
    <h3>Create Account</h3>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password"
               name="password"
               minlength="6"
               required>

        <button type="submit">
            Register
        </button>

    </form>

    <p>
        Already have an account?
        <a href="reader.php">Login</a>
    </p>

</div>

</body>
</html>