<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET['quiz_id'])) {
    echo "No quiz selected.";
    exit;
}

$quiz_id = $_GET['quiz_id'];
$db = getDB();

// Fetch 10 random questions for this quiz
$sql_questions = "SELECT ID, Text FROM questions WHERE QUIZ_ID = :quiz_id ORDER BY RAND() LIMIT 10";
$stmt_questions = $db->prepare($sql_questions);
$stmt_questions->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
$stmt_questions->execute();
$questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

// Prepare array to hold answers
$all_data = [];

foreach ($questions as $question) {
    $question_id = $question['ID'];
    $question_text = $question['Text'];

    // Fetch 3 random answers for each question
    $sql_answers = "SELECT ID, Text FROM answers WHERE Questions_ID = :qid ORDER BY RAND() LIMIT 3";
    $stmt_answers = $db->prepare($sql_answers);
    $stmt_answers->bindParam(':qid', $question_id, PDO::PARAM_INT);
    $stmt_answers->execute();
    $answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

    $all_data[] = [
        'id' => $question_id,
        'text' => $question_text,
        'answers' => $answers
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
<div class="container quiz-container mt-5">
    <h2 class="text-center mb-4">Take the Quiz</h2>
    <form action="results.php" method="post">
        <?php foreach ($all_data as $index => $q): ?>
            <div class="mb-4 p-3 bg-white rounded shadow-sm">
                <p class="fw-bold">Q<?= $index + 1 ?>: <?= htmlspecialchars($q['text']) ?></p>
                <?php foreach ($q['answers'] as $a): ?>
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="radio"
                                name="answers[<?= $q['id'] ?>]"
                                id="answer_<?= $a['ID'] ?>"
                                value="<?= $a['ID'] ?>"
                                required
                        >
                        <label class="form-check-label" for="answer_<?= $a['ID'] ?>">
                            <?= htmlspecialchars($a['Text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <div class="text-center">
            <button type="submit" class="btn btn-success px-4 py-2">Submit Quiz</button>
        </div>
    </form>
</div>
</body>
</html>
