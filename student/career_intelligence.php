<?php
session_start();
include "../includes/db.php";

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

/* =========================
   COURSE ID
========================= */
$course_id = intval(
    $_GET['course_id']
    ?? $_SESSION['current_course_id']
    ?? $_SESSION['quiz_course_id']
    ?? 0
);

if ($course_id > 0) {
    $_SESSION['current_course_id'] = $course_id;
}

/* =========================
   VARIABLES
========================= */
$message = "";

$career_goal = "";
$learning_style = "";
$interests = "";

$career_match = 0;
$job_readiness = 0;
$skill_score = 0;
$quiz_score = 0;
$project_score = 50;
$communication_score = 50;

$skill_gaps = [];
$recommendations = [];
$roadmap = [];

$current_course_name = "";

/* =========================
   GET COURSE NAME
========================= */
if ($course_id > 0) {

    $course_sql = "SELECT * FROM courses WHERE id = $course_id LIMIT 1";
    $course_result = mysqli_query($conn, $course_sql);

    if ($course_result && mysqli_num_rows($course_result) > 0) {

        $course = mysqli_fetch_assoc($course_result);

        $current_course_name =
            $course['course_name']
            ?? $course['title']
            ?? "Course";
    }
}

/* =========================
   GET STUDENT PROFILE
========================= */
$profile_sql = "
    SELECT *
    FROM student_profiles
    WHERE user_id = $user_id
    LIMIT 1
";

$profile_result = mysqli_query($conn, $profile_sql);

if ($profile_result && mysqli_num_rows($profile_result) > 0) {

    $profile = mysqli_fetch_assoc($profile_result);

    $career_goal = $profile['career_goal'] ?? "";
    $learning_style = $profile['learning_style'] ?? "";
    $interests = $profile['interests'] ?? "";
}

/* =========================
   SAVE CAREER PROFILE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $career_goal = mysqli_real_escape_string(
        $conn,
        $_POST['career_goal'] ?? ""
    );

    $learning_style = mysqli_real_escape_string(
        $conn,
        $_POST['learning_style'] ?? ""
    );

    $interests = mysqli_real_escape_string(
        $conn,
        $_POST['interests'] ?? ""
    );

    $check_profile = mysqli_query(
        $conn,
        "SELECT id FROM student_profiles
         WHERE user_id = $user_id
         LIMIT 1"
    );

    if ($check_profile && mysqli_num_rows($check_profile) > 0) {

        $update_sql = "
            UPDATE student_profiles
            SET
                career_goal = '$career_goal',
                learning_style = '$learning_style',
                interests = '$interests'
            WHERE user_id = $user_id
        ";

        mysqli_query($conn, $update_sql);

    } else {

        $insert_sql = "
            INSERT INTO student_profiles
            (
                user_id,
                career_goal,
                learning_style,
                interests
            )
            VALUES
            (
                $user_id,
                '$career_goal',
                '$learning_style',
                '$interests'
            )
        ";

        mysqli_query($conn, $insert_sql);
    }

    $message = "Career profile updated successfully!";
}

/* =========================
   GET AVAILABLE CAREERS
========================= */
$careers = [];

$career_sql = "
    SELECT DISTINCT career_name
    FROM career_roadmap
    ORDER BY career_name ASC
";

$career_result = mysqli_query($conn, $career_sql);

if ($career_result) {

    while ($row = mysqli_fetch_assoc($career_result)) {

        $careers[] = $row['career_name'];
    }
}

/* =========================
   DEFAULT CAREER
========================= */
if (empty($career_goal) && !empty($careers)) {
    $career_goal = $careers[0];
}

/* =========================
   GET ROADMAP FOR CAREER
========================= */
if (!empty($career_goal)) {

    $safe_career = mysqli_real_escape_string(
        $conn,
        $career_goal
    );

    $roadmap_sql = "
        SELECT *
        FROM career_roadmap
        WHERE career_name = '$safe_career'
        ORDER BY skill_order ASC
    ";

    $roadmap_result = mysqli_query($conn, $roadmap_sql);

    if ($roadmap_result) {

        while ($row = mysqli_fetch_assoc($roadmap_result)) {

            $roadmap[] = $row;
        }
    }
}

/* =========================
   GET STUDENT SKILLS
========================= */
$student_skills = [];

$skills_sql = "
    SELECT *
    FROM student_skills
    WHERE user_id = $user_id
    ORDER BY skill_score DESC
";

$skills_result = mysqli_query($conn, $skills_sql);

if ($skills_result) {

    while ($row = mysqli_fetch_assoc($skills_result)) {

        $student_skills[] = $row;
    }
}

/* =========================
   SKILL MATCH FUNCTION
========================= */
function skillMatches($studentSkill, $requiredSkill)
{
    $studentSkill = strtolower(trim($studentSkill));
    $requiredSkill = strtolower(trim($requiredSkill));

    if (
        $studentSkill === $requiredSkill ||
        strpos($studentSkill, $requiredSkill) !== false ||
        strpos($requiredSkill, $studentSkill) !== false
    ) {
        return true;
    }

    $aliases = [

        "python basics" => [
            "python",
            "python basics"
        ],

        "html/css" => [
            "html",
            "css",
            "html/css"
        ],

        "javascript" => [
            "javascript",
            "js"
        ],

        "git/github" => [
            "git",
            "github",
            "git/github"
        ],

        "rest api" => [
            "rest api",
            "api",
            "rest"
        ],

        "php/node.js" => [
            "php",
            "node.js",
            "node"
        ],

        "mysql" => [
            "mysql",
            "sql"
        ],

        "sql" => [
            "sql",
            "mysql"
        ],

        "data visualization" => [
            "data visualization",
            "visualization",
            "power bi"
        ],

        "machine learning" => [
            "machine learning",
            "ml"
        ],

        "deep learning" => [
            "deep learning",
            "dl"
        ],

        "pandas" => [
            "pandas",
            "python pandas"
        ],

        "statistics" => [
            "statistics",
            "statistical"
        ],

        "oop" => [
            "oop",
            "object oriented programming"
        ]
    ];

    foreach ($aliases as $key => $values) {

        if (
            in_array($studentSkill, $values) &&
            in_array($requiredSkill, $values)
        ) {
            return true;
        }
    }

    return false;
}

/* =========================
   CALCULATE SKILL SCORE
========================= */
$total_required_skills = count($roadmap);

$matched_skills = 0;

if ($total_required_skills > 0) {

    foreach ($roadmap as $required) {

        $required_skill = $required['skill_name'];

        foreach ($student_skills as $student) {

            if (
                skillMatches(
                    $student['skill_name'],
                    $required_skill
                )
            ) {

                $matched_skills++;
                break;
            }
        }
    }

    $skill_score =
        ($matched_skills / $total_required_skills) * 100;
}

/* =========================
   SKILL GAPS
========================= */
if ($total_required_skills > 0) {

    foreach ($roadmap as $required) {

        $required_skill = $required['skill_name'];

        $found = false;

        foreach ($student_skills as $student) {

            if (
                skillMatches(
                    $student['skill_name'],
                    $required_skill
                )
            ) {

                $found = true;
                break;
            }
        }

        if (!$found) {

            $skill_gaps[] = $required_skill;
        }
    }
}

/* =========================
   GET QUIZ RESULTS
========================= */
$quiz_total = 0;
$quiz_count = 0;

$quiz_sql = "
    SELECT
        r.percentage,
        c.course_name
    FROM results r
    LEFT JOIN courses c
        ON r.course_id = c.id
    WHERE r.user_id = $user_id
";

$quiz_result = mysqli_query($conn, $quiz_sql);

if ($quiz_result) {

    while ($row = mysqli_fetch_assoc($quiz_result)) {

        $percentage = floatval(
            $row['percentage'] ?? 0
        );

        $quiz_total += $percentage;
        $quiz_count++;
    }
}

if ($quiz_count > 0) {

    $quiz_score = $quiz_total / $quiz_count;
}

/* =========================
   CAREER MATCH
========================= */
$career_match =
    ($skill_score * 0.60)
    +
    ($quiz_score * 0.40);

/* =========================
   JOB READINESS
========================= */
$job_readiness =
    ($skill_score * 0.45)
    +
    ($quiz_score * 0.30)
    +
    ($project_score * 0.15)
    +
    ($communication_score * 0.10);

/* =========================
   ROUND SCORES
========================= */
$skill_score = round($skill_score, 2);
$quiz_score = round($quiz_score, 2);
$career_match = round($career_match, 2);
$job_readiness = round($job_readiness, 2);

/* =========================
   RECOMMENDATIONS
========================= */
if ($career_match >= 80) {

    $recommendations[] =
        "Excellent career match. Continue advanced learning.";

} elseif ($career_match >= 60) {

    $recommendations[] =
        "Good career match. Improve the missing skills.";

} else {

    $recommendations[] =
        "Your career match is currently low. Focus on the recommended roadmap.";
}

/* Skill gap recommendations */
foreach ($skill_gaps as $gap) {

    $recommendations[] =
        "Learn " . $gap . " to improve your career readiness.";
}

/* =========================
   SAVE CAREER RECOMMENDATION
========================= */
if (!empty($career_goal)) {

    $safe_career = mysqli_real_escape_string(
        $conn,
        $career_goal
    );

    $reason =
        "Career match calculated using skills and quiz performance.";

    $recommended_skills =
        implode(", ", $skill_gaps);

    $safe_reason = mysqli_real_escape_string(
        $conn,
        $reason
    );

    $safe_skills = mysqli_real_escape_string(
        $conn,
        $recommended_skills
    );

    mysqli_query(
        $conn,
        "DELETE FROM career_recommendations
         WHERE user_id = $user_id"
    );

    mysqli_query(
        $conn,
        "INSERT INTO career_recommendations
        (
            user_id,
            career_name,
            match_percentage,
            reason,
            recommended_skills
        )
        VALUES
        (
            $user_id,
            '$safe_career',
            $career_match,
            '$safe_reason',
            '$safe_skills'
        )"
    );
}

/* =========================
   SAVE JOB READINESS
========================= */
$improvement_areas =
    implode(", ", $skill_gaps);

$safe_improvement =
    mysqli_real_escape_string(
        $conn,
        $improvement_areas
    );

$check_readiness = mysqli_query(
    $conn,
    "SELECT id
     FROM job_readiness
     WHERE user_id = $user_id
     LIMIT 1"
);

if ($check_readiness && mysqli_num_rows($check_readiness) > 0) {

    mysqli_query(
        $conn,
        "UPDATE job_readiness
         SET
            readiness_score = $job_readiness,
            skill_score = $skill_score,
            quiz_score = $quiz_score,
            project_score = $project_score,
            communication_score = $communication_score,
            improvement_areas = '$safe_improvement'
         WHERE user_id = $user_id"
    );

} else {

    mysqli_query(
        $conn,
        "INSERT INTO job_readiness
        (
            user_id,
            readiness_score,
            skill_score,
            quiz_score,
            project_score,
            communication_score,
            improvement_areas
        )
        VALUES
        (
            $user_id,
            $job_readiness,
            $skill_score,
            $quiz_score,
            $project_score,
            $communication_score,
            '$safe_improvement'
        )"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Career Intelligence</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #f8fafc
        );

    color: #1e293b;
}

.container {

    width: 92%;

    max-width: 1200px;

    margin: 30px auto;
}

.header {

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color: white;

    padding: 30px;

    border-radius: 20px;

    box-shadow:
        0 15px 35px
        rgba(0,0,0,0.15);

    margin-bottom: 25px;
}

.header h1 {

    margin: 0 0 8px;

    font-size: 32px;
}

.header p {

    margin: 0;

    opacity: 0.9;
}

.back-btn {

    display: inline-block;

    margin-bottom: 20px;

    text-decoration: none;

    color: white;

    background: rgba(255,255,255,0.18);

    padding: 10px 18px;

    border-radius: 10px;
}

.success {

    background: #dcfce7;

    color: #166534;

    padding: 14px 18px;

    border-radius: 10px;

    margin-bottom: 20px;

    border-left: 5px solid #22c55e;
}

.card {

    background: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 25px;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,0.08);
}

.card h2 {

    margin-top: 0;

    color: #312e81;
}

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(220px, 1fr)
        );

    gap: 20px;
}

label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;
}

select,
textarea {

    width: 100%;

    padding: 12px;

    border: 1px solid #cbd5e1;

    border-radius: 10px;

    font-size: 15px;
}

textarea {

    min-height: 100px;

    resize: vertical;
}

.submit-btn {

    margin-top: 20px;

    border: none;

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color: white;

    padding: 13px 25px;

    border-radius: 10px;

    font-size: 16px;

    cursor: pointer;
}

.analytics {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(200px, 1fr)
        );

    gap: 20px;
}

.analytics-card {

    background: white;

    padding: 25px;

    border-radius: 18px;

    text-align: center;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,0.08);
}

.analytics-card h3 {

    margin: 0 0 10px;

    color: #64748b;

    font-size: 15px;
}

.analytics-card .number {

    font-size: 32px;

    font-weight: bold;

    color: #4f46e5;
}

.skill-list {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(200px, 1fr)
        );

    gap: 12px;
}

.skill-item {

    padding: 14px;

    background: #f8fafc;

    border-radius: 10px;

    border-left: 4px solid #4f46e5;
}

.gap-item {

    padding: 14px;

    margin-bottom: 10px;

    background: #fff7ed;

    border-left: 4px solid #f97316;

    border-radius: 10px;
}

.recommendation {

    padding: 15px;

    background: #eef2ff;

    border-radius: 10px;

    margin-bottom: 10px;

    border-left: 4px solid #6366f1;
}

.roadmap {

    display: flex;

    flex-wrap: wrap;

    gap: 15px;
}

.roadmap-step {

    flex: 1;

    min-width: 170px;

    padding: 18px;

    background: #f8fafc;

    border-radius: 12px;

    border-top: 5px solid #6366f1;
}

.roadmap-step strong {

    display: block;

    margin-bottom: 8px;

    color: #312e81;
}

/* =========================
   CERTIFICATE SECTION
========================= */

.certificate-box {

    background:
        linear-gradient(
            135deg,
            #fffbeb,
            #fef3c7
        );

    border: 2px solid #f59e0b;

    border-radius: 20px;

    padding: 35px;

    text-align: center;

    margin-top: 30px;

    box-shadow:
        0 12px 30px
        rgba(245,158,11,0.15);
}

.certificate-box h2 {

    color: #92400e;

    margin-top: 0;

    font-size: 28px;
}

.certificate-box p {

    color: #78350f;

    font-size: 16px;

    margin-bottom: 25px;
}

.certificate-btn {

    display: inline-block;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #d97706,
            #b45309
        );

    color: white;

    padding: 14px 28px;

    border-radius: 12px;

    font-size: 17px;

    font-weight: bold;

    transition: 0.3s;
}

.certificate-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(180,83,9,0.3);
}

.empty {

    color: #64748b;

    padding: 15px;

    background: #f8fafc;

    border-radius: 10px;
}

</style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <a href="dashboard.php"
           class="back-btn">

            ← Back to Dashboard

        </a>

        <h1>🎯 Career Intelligence</h1>

        <p>
            Analyze your skills, quiz performance,
            career match and job readiness.
        </p>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="success">

            ✅ <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- CAREER PROFILE -->

    <div class="card">

        <h2>👨‍🎓 Career Profile</h2>

        <form method="POST">

            <div class="form-grid">

                <div>

                    <label>
                        Career Goal
                    </label>

                    <select name="career_goal"
                            required>

                        <option value="">
                            Select Career
                        </option>

                        <?php foreach ($careers as $career): ?>

                            <option
                                value="<?php echo htmlspecialchars($career); ?>"
                                <?php
                                if ($career_goal == $career)
                                    echo "selected";
                                ?>
                            >

                                <?php
                                echo htmlspecialchars($career);
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label>
                        Learning Style
                    </label>

                    <select name="learning_style">

                        <option value="">
                            Select Learning Style
                        </option>

                        <option value="Visual"
                            <?php
                            if ($learning_style == "Visual")
                                echo "selected";
                            ?>>
                            Visual
                        </option>

                        <option value="Practical"
                            <?php
                            if ($learning_style == "Practical")
                                echo "selected";
                            ?>>
                            Practical
                        </option>

                        <option value="Reading"
                            <?php
                            if ($learning_style == "Reading")
                                echo "selected";
                            ?>>
                            Reading
                        </option>

                        <option value="Video"
                            <?php
                            if ($learning_style == "Video")
                                echo "selected";
                            ?>>
                            Video
                        </option>

                    </select>

                </div>


                <div>

                    <label>
                        Interests
                    </label>

                    <textarea
                        name="interests"
                        placeholder="Example: AI, Web Development, Data Science..."
                    ><?php
                    echo htmlspecialchars($interests);
                    ?></textarea>

                </div>

            </div>


            <button
                type="submit"
                class="submit-btn">

                💾 Save Career Profile

            </button>

        </form>

    </div>


    <!-- ANALYTICS -->

    <div class="analytics">

        <div class="analytics-card">

            <h3>🎯 Career Match</h3>

            <div class="number">

                <?php echo $career_match; ?>%

            </div>

        </div>


        <div class="analytics-card">

            <h3>💼 Job Readiness</h3>

            <div class="number">

                <?php echo $job_readiness; ?>%

            </div>

        </div>


        <div class="analytics-card">

            <h3>🧠 Skill Score</h3>

            <div class="number">

                <?php echo $skill_score; ?>%

            </div>

        </div>


        <div class="analytics-card">

            <h3>📝 Quiz Score</h3>

            <div class="number">

                <?php echo $quiz_score; ?>%

            </div>

        </div>

    </div>


    <!-- SKILL GAPS -->

    <div class="card">

        <h2>📊 Skill Gap Analysis</h2>

        <?php if (!empty($skill_gaps)): ?>

            <div class="skill-list">

                <?php foreach ($skill_gaps as $gap): ?>

                    <div class="gap-item">

                        ⚠️
                        <strong>
                            <?php
                            echo htmlspecialchars($gap);
                            ?>
                        </strong>

                        <br>

                        <small>
                            Recommended skill
                        </small>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                🎉 Excellent! No major skill gaps
                detected for your selected career.

            </div>

        <?php endif; ?>

    </div>


    <!-- RECOMMENDATIONS -->

    <div class="card">

        <h2>💡 Career Recommendations</h2>

        <?php foreach ($recommendations as $recommendation): ?>

            <div class="recommendation">

                💡
                <?php
                echo htmlspecialchars($recommendation);
                ?>

            </div>

        <?php endforeach; ?>

    </div>


    <!-- CAREER ROADMAP -->

    <div class="card">

        <h2>
            🗺️
            <?php
            echo htmlspecialchars($career_goal);
            ?>
            Career Roadmap
        </h2>

        <?php if (!empty($roadmap)): ?>

            <div class="roadmap">

                <?php foreach ($roadmap as $step): ?>

                    <div class="roadmap-step">

                        <strong>

                            Step
                            <?php
                            echo htmlspecialchars(
                                $step['skill_order']
                            );
                            ?>

                        </strong>

                        <div>

                            <?php
                            echo htmlspecialchars(
                                $step['skill_name']
                            );
                            ?>

                        </div>

                        <?php if (!empty($step['description'])): ?>

                            <small>

                                <?php
                                echo htmlspecialchars(
                                    $step['description']
                                );
                                ?>

                            </small>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                Select a career to view the roadmap.

            </div>

        <?php endif; ?>

    </div>


    <!-- =========================
         CERTIFICATE SECTION
    ========================== -->

    <?php if ($course_id > 0): ?>

        <div class="certificate-box">

            <h2>
                🎓 Ready for Your Certificate?
            </h2>

            <p>

                Your learning, assessment and
                career analysis are complete.

            </p>

            <?php if (!empty($current_course_name)): ?>

                <p>

                    <strong>
                        Course:
                        <?php
                        echo htmlspecialchars(
                            $current_course_name
                        );
                        ?>
                    </strong>

                </p>

            <?php endif; ?>


            <a
                href="../certificates/certificate.php?course_id=<?php echo $course_id; ?>"
                class="certificate-btn"
                target="_blank"
            >

                🏆 View & Print Certificate

            </a>

        </div>

    <?php else: ?>

        <div class="card">

            <div class="empty">

                ℹ️ Course information is not available.
                Please open Career Intelligence from
                the Quiz Result page.

            </div>

        </div>

    <?php endif; ?>


</div>

</body>

</html>