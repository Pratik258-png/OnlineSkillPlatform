
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Student';
$email = $_SESSION['email'] ?? '';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* Show useful SQL error instead of a bind_param fatal error */
function prepareQuery($conn, $sql) {
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die(
            '<div style="font-family:Arial;background:#fff0f0;color:#900;padding:20px;">' .
            '<h3>Database query error</h3><pre>' .
            e(mysqli_error($conn)) .
            '</pre></div>'
        );
    }

    return $stmt;
}

function getSingleCount($conn, $sql, $user_id) {
    $stmt = prepareQuery($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0);
}

/* Total enrolled courses */
$total_courses = getSingleCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM enrollments
     WHERE user_id = ?",
    $user_id
);

/* Completed modules in enrolled courses */
$completed_modules = getSingleCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM module_progress mp
     INNER JOIN enrollments en
       ON en.user_id = mp.user_id
      AND en.course_id = mp.course_id
     WHERE mp.user_id = ?
       AND mp.completed = 1",
    $user_id
);

/* Total modules in enrolled courses */
$total_modules = getSingleCount(
    $conn,
    "SELECT COUNT(*)
     AS total
     FROM modules_content mc
     INNER JOIN enrollments en
       ON en.course_id = mc.course_id
     WHERE en.user_id = ?",
    $user_id
);

$remaining_modules = max(0, $total_modules - $completed_modules);

$overall_progress = $total_modules > 0
    ? min(100, round(($completed_modules / $total_modules) * 100))
    : 0;

/* Quiz statistics */
$stmt = prepareQuery(
    $conn,
    "SELECT COUNT(*) AS total,
            COALESCE(AVG(percentage), 0) AS avg_score
     FROM results
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$quiz_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$total_quizzes = (int)($quiz_stats['total'] ?? 0);
$average_score = round((float)($quiz_stats['avg_score'] ?? 0), 1);

/* Enrolled courses */
$stmt = prepareQuery(
    $conn,
    "SELECT
        c.id AS course_id,
        c.course_name AS course_name,
        en.enrolled_at,
        (
            SELECT COUNT(*)
            FROM modules_content mc
            WHERE mc.course_id = c.id
        ) AS total_modules,
        (
            SELECT COUNT(*)
            FROM module_progress mp
            WHERE mp.user_id = en.user_id
              AND mp.course_id = c.id
              AND mp.completed = 1
        ) AS completed_modules,
        (
            SELECT r.percentage
            FROM results r
            WHERE r.user_id = en.user_id
              AND r.course_id = c.id
            LIMIT 1
        ) AS quiz_percentage
     FROM enrollments en
     INNER JOIN courses c ON c.id = en.course_id
     WHERE en.user_id = ?
     ORDER BY en.enrolled_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$courses_result = mysqli_stmt_get_result($stmt);
$courses = [];

while ($row = mysqli_fetch_assoc($courses_result)) {
    $courses[] = $row;
}

mysqli_stmt_close($stmt);

/* Quiz results */
$stmt = prepareQuery(
    $conn,
    "SELECT
        r.course_id,
        r.score,
        r.percentage,
        r.created_at,
        c.course_name AS course_name
     FROM results r
     LEFT JOIN courses c ON c.id = r.course_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$results = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Progress | OnlineSkill</title>

<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#0b1020;color:#f1f5f9}
header{background:#111a30;padding:18px 6%;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid #273653}
.logo{font-size:23px;font-weight:bold;color:#7dd3fc}
a{color:#7dd3fc;text-decoration:none}
a:hover{text-decoration:underline}
.container{max-width:1150px;margin:30px auto;padding:0 18px}
.welcome{background:linear-gradient(120deg,#172554,#172033);padding:26px;border-radius:16px;margin-bottom:22px}
.welcome h1{margin:0 0 10px;font-size:28px}
.muted{color:#a8b5cc}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin:22px 0}
.card,.panel{background:#121b2e;border:1px solid #263653;border-radius:14px;padding:21px}
.card .number{font-size:30px;font-weight:bold;margin:12px 0}
.card .label{color:#a8b5cc}
.panel{margin-top:22px;overflow:hidden}
.panel h2{margin-top:0;font-size:21px}
.progress-track{height:10px;background:#273449;border-radius:20px;overflow:hidden;margin:12px 0}
.progress-fill{height:100%;background:#38bdf8;border-radius:20px}
.course{border-top:1px solid #263653;padding:20px 0}
.course-top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.badge{display:inline-block;padding:6px 10px;border-radius:20px;background:#1e3a5f;color:#bae6fd;font-size:12px}
.button{display:inline-block;background:#0284c7;color:white;padding:10px 15px;border-radius:8px;margin:5px 5px 0 0}
.button.secondary{background:#273449}
table{width:100%;border-collapse:collapse;min-width:550px}
th,td{padding:13px;text-align:left;border-bottom:1px solid #263653}
th{color:#7dd3fc}
.table-wrap{overflow-x:auto}
.empty{color:#a8b5cc;padding:15px 0}
footer{text-align:center;color:#8190aa;padding:30px 10px}
@media(max-width:600px){.welcome h1{font-size:23px}.card .number{font-size:26px}}
</style>
</head>

<body>
<header>
    <div class="logo">🎓 OnlineSkill</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        &nbsp; | &nbsp;
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">

<section class="welcome">
    <h1>📊 My Learning Progress</h1>
    <p>Welcome, <strong><?= e($name) ?></strong> 👋</p>
    <p class="muted"><?= e($email) ?></p>
    <p class="muted">Track your courses, modules, quiz results and learning journey.</p>

    <div style="margin-top:22px">
        <div class="course-top">
            <strong>Overall Module Progress</strong>
            <strong><?= $overall_progress ?>%</strong>
        </div>
        <div class="progress-track">
            <div class="progress-fill" style="width:<?= $overall_progress ?>%"></div>
        </div>
        <span class="muted">
            <?= $completed_modules ?> completed out of <?= $total_modules ?> modules
        </span>
    </div>
</section>

<section class="cards">
    <div class="card">
        <div>📚</div>
        <div class="number"><?= $total_courses ?></div>
        <div class="label">Enrolled Courses</div>
    </div>

    <div class="card">
        <div>✅</div>
        <div class="number"><?= $completed_modules ?></div>
        <div class="label">Completed Modules</div>
    </div>

    <div class="card">
        <div>📖</div>
        <div class="number"><?= $remaining_modules ?></div>
        <div class="label">Modules Remaining</div>
    </div>

    <div class="card">
        <div>📝</div>
        <div class="number"><?= $total_quizzes ?></div>
        <div class="label">Quiz Records</div>
    </div>

    <div class="card">
        <div>🎯</div>
        <div class="number"><?= $average_score ?>%</div>
        <div class="label">Average Quiz Score</div>
    </div>
</section>

<section class="panel">
    <h2>📚 My Courses</h2>

    <?php if (count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
            <?php
            $course_total = (int)$course['total_modules'];
            $course_completed = (int)$course['completed_modules'];
            $course_percent = $course_total > 0
                ? min(100, round(($course_completed / $course_total) * 100))
                : 0;
            ?>

            <div class="course">
                <div class="course-top">
                    <h3><?= e($course['course_name']) ?></h3>
                    <span class="badge"><?= $course_percent ?>% Complete</span>
                </div>

                <div class="progress-track">
                    <div class="progress-fill" style="width:<?= $course_percent ?>%"></div>
                </div>

                <p class="muted">
                    Modules completed: <?= $course_completed ?> / <?= $course_total ?>
                </p>

                <p class="muted">
                    Enrolled on: <?= e(date('d M Y', strtotime($course['enrolled_at']))) ?>
                </p>

                <?php if ($course['quiz_percentage'] !== null): ?>
                    <p>Quiz Score: <strong><?= e($course['quiz_percentage']) ?>%</strong></p>
                <?php else: ?>
                    <p class="muted">Quiz not attempted yet.</p>
                <?php endif; ?>

                <a class="button" href="course_catalog.php">Explore Courses</a>
                <a class="button secondary" href="../certificates/certificate.php?course_id=<?= (int)$course['course_id'] ?>">Certificate</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty">You have not enrolled in any courses yet.</p>
        <a class="button" href="course_catalog.php">Browse Courses</a>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>📝 Quiz Results</h2>

    <?php if (mysqli_num_rows($results) > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Date</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($result = mysqli_fetch_assoc($results)): ?>
                    <tr>
                        <td><?= e($result['course_name']) ?></td>
                        <td><?= e($result['score']) ?></td>
                        <td><?= e($result['percentage']) ?>%</td>
                        <td><?= e(date('d M Y', strtotime($result['created_at']))) ?></td>
                        <td>
                            <a href="../certificates/certificate.php?course_id=<?= (int)$result['course_id'] ?>">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No quiz results available yet. Complete a quiz to see your score here.</p>
    <?php endif; ?>
</section>

<div style="margin-top:22px">
    <a class="button" href="dashboard.php">← Back to Dashboard</a>
    <a class="button secondary" href="course_catalog.php">Browse Courses</a>
</div>

</div>

<footer>© <?= date('Y') ?> OnlineSkillPlatform | Learn • Practice • Track • Certify</footer>
</body>
</html>
