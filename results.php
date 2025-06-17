<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$username = $_SESSION['username'];
$db = getDB();

$quiz_id = $_POST['quiz_id'];
$user_answers = $_POST['answers'];
$total_questions = count($user_answers);
$correct_count = 0;
$feedback = [];

foreach ($user_answers as $question_id => $selected_answer_id) {
    // Get the correct answer
    $sql = "SELECT ID, Text, Is_Correct FROM answers WHERE Questions_ID = :qid";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':qid', $question_id, PDO::PARAM_INT);
    $stmt->execute();
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $correct_answer_id = null;
    foreach ($answers as $answer) {
        if ($answer['Is_Correct']) {
            $correct_answer_id = $answer['ID'];
            break;
        }
    }

    $is_correct = ($selected_answer_id == $correct_answer_id);
    if ($is_correct) {
        $correct_count++;
    }

    $feedback[] = [
        'question_id' => $question_id,
        'answers' => $answers,
        'selected' => $selected_answer_id,
        'correct' => $correct_answer_id,
        'is_correct' => $is_correct
    ];
}
$user_id = $_SESSION['id'];
$sql_insert = "INSERT INTO quiz_results (user_id, quiz_id, score, total_questions) VALUES (:user_id, :quiz_id, :score, :total_questions)";
$stmt_insert = $db->prepare($sql_insert);
$stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_insert->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
$stmt_insert->bindParam(':score', $correct_count, PDO::PARAM_INT);
$stmt_insert->bindParam(':total_questions', $total_questions, PDO::PARAM_INT);
$stmt_insert->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Results for <?= htmlspecialchars($username) ?></h2>
    <p><strong>Score:</strong> <?= $correct_count ?> out of <?= $total_questions ?></p>
    <hr>

    <?php foreach ($feedback as $index => $item): ?>
        <div class="mb-3">
            <p><strong>Q<?= $index + 1 ?>:</strong>
                <?php
                $stmt = $db->prepare("SELECT Text FROM questions WHERE ID = :qid");
                $stmt->bindParam(':qid', $item['question_id']);
                $stmt->execute();
                echo htmlspecialchars($stmt->fetchColumn());
                ?>
            </p>
            <?php foreach ($item['answers'] as $answer): ?>
                <?php
                $classes = "p-2 rounded";
                if ($answer['ID'] == $item['correct']) {
                    $classes .= " bg-success text-white";
                } elseif ($answer['ID'] == $item['selected']) {
                    $classes .= " bg-danger text-white";
                } else {
                    $classes .= " bg-light";
                }
                ?>
                <div class="<?= $classes ?>">
                    <?= htmlspecialchars($answer['Text']) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <hr>
    <?php endforeach; ?>

    <div class="text-center mt-5">
        <a href="quiz_select.php" class="btn btn-primary btn-lg">Choose Another Quiz</a>
    </div>

</div>
</body>
</html>

