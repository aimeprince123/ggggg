<?php
$_SESSION["user_id"] = $pdo->lastInsertId();
$_SESSION["user_name"] = $name;

header("Location: reader.php");
exit;
?>
