<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correctAnswers = [
        'q1' => 'a',  // 9%
        'q2' => 'b',  // 1.5 kg
        'q3' => 'c',  // Glass
        'q4' => 'a',  // Reduces landfill use
        'q5' => 'd',  // Photosynthesis
        'q6' => 'a',  // Reusing materials
        'q7' => 'a',  // Germany
        'q8' => 'c',  // Food waste
        'q9' => 'c',  // 500 years
        'q10' => 'c'  // Reducing consumption
    ];

    $score = 0;
    for ($i = 1; $i <= 10; $i++) {
        $question = "q$i";
        if (isset($_POST[$question]) && $_POST[$question] === $correctAnswers[$question]) {
            $score++;
        }
    }

    // Output JavaScript alert and redirect
    echo "<script>alert('Your Score: $score/10\\nGreat effort! Want to learn more?'); window.location.href = 'quiz.html';</script>";
    exit();
}
?>
