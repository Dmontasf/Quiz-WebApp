<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$db = getDB();
$stmt = $db->query("SELECT ID, Subject FROM quizzes");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="quiz-container text-center">
    <h2 class="mb-4">Select a Quiz</h2>

    <?php foreach ($quizzes as $quiz): ?>
        <form action="quiz.php" method="get" class="d-grid gap-2 mb-3">
            <input type="hidden" name="quiz_id" value="<?= $quiz['ID'] ?>">
            <button type="submit" class="btn btn-primary btn-lg">
                <?= htmlspecialchars($quiz['Subject']) ?>
            </button>
        </form>
    <?php endforeach; ?>

    <a href="logout.php" class="btn btn-outline-secondary mt-4">Logout</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
