
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Student';
$email = $_SESSION['email'] ?? '';
$user_id = (int) $_SESSION['user_id'];

/* Get passed Viva results */
$sql = "SELECT
            v.id,
            v.course_id,
            v.total_questions,
            v.attempted_questions,
            v.correct_answers,
            v.total_score,
            v.percentage,
            v.status,
            v.created_at,
            c.course_name
        FROM viva_results v
        LEFT JOIN courses c ON c.id = v.course_id
        WHERE v.user_id = ?
          AND UPPER(v.status) = 'PASS'
        ORDER BY v.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
$certificates = [];

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $query_result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($query_result)) {
        $certificates[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | OnlineSkill</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #0b1120;
    color: #f1f5f9;
}

header {
    background: #111c32;
    padding: 18px 6%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 1px solid #263650;
}

header h2 {
    margin: 0;
    color: #60a5fa;
}

a {
    text-decoration: none;
}

.logout {
    color: white;
    background: #dc2626;
    padding: 10px 16px;
    border-radius: 8px;
}

.container {
    max-width: 1150px;
    margin: auto;
    padding: 30px 20px;
}

.welcome {
    background: linear-gradient(120deg, #172554, #312e81);
    padding: 28px;
    border-radius: 16px;
    margin-bottom: 25px;
}

.welcome h1 {
    margin-top: 0;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: #111c32;
    border: 1px solid #263650;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 5px;
    overflow-wrap: anywhere;
}

.card h3 {
    color: #93c5fd;
}

.card p {
    color: #cbd5e1;
    line-height: 1.6;
}

.action-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 11px 15px;
    border-radius: 8px;
    color: white;
    background: #2563eb;
}

.action-btn:hover {
    background: #1d4ed8;
}

/* My Certificates compact toggle card */
#certificates-section {
    margin-top: 15px;
}

#certificates-toggle {
    width: 100%;
    max-width: 350px;
    text-align: left;
    cursor: pointer;
    background: #111c32;
    border: 1px solid #263650;
    color: #f1f5f9;
    border-radius: 12px;
    padding: 16px 20px;
    font-family: inherit;
}

#certificates-toggle:hover {
    border-color: #60a5fa;
    background: #172554;
}

#certificates-toggle h3 {
    color: #93c5fd;
    margin: 0 0 8px;
    font-size: 18px;
}

#certificates-toggle p {
    margin: 0;
    font-size: 13px;
    color: #cbd5e1;
}

#certificates-content {
    margin-top: 15px;
}

.certificate-card,
.empty {
    background: #111c32;
    border: 1px solid #263650;
    border-radius: 10px;
    padding: 14px 16px;
    max-width: 600px;
    margin-bottom: 12px;
    overflow-wrap: anywhere;
}

.certificate-card h3,
.empty h3 {
    font-size: 16px;
    margin: 0 0 8px;
    color: #93c5fd;
}

.certificate-card p {
    color: #cbd5e1;
    font-size: 13px;
    line-height: 1.4;
    margin: 5px 0;
}

.status {
    color: #86efac !important;
    font-weight: bold;
}

.certificate-card .action-btn,
.empty .action-btn {
    margin-top: 8px;
    padding: 8px 12px;
    font-size: 13px;
}

.empty {
    color: #cbd5e1;
}

.empty p {
    font-size: 13px;
}

footer {
    text-align: center;
    padding: 25px;
    color: #94a3b8;
}

@media (max-width: 500px) {
    .container {
        padding: 20px 14px;
    }

    .welcome {
        padding: 20px;
    }

    #certificates-toggle {
        max-width: 100%;
    }
}
</style>
</head>

<body>

<header>
    <h2>🎓 OnlineSkill</h2>
    <a class="logout" href="../logout.php">🚪 Logout</a>
</header>

<main class="container">

    <div class="welcome">
        <h1>🎓 Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
        <p><?= htmlspecialchars($email) ?></p>
        <p>You are logged in as <strong>Student</strong>.</p>
        <p>📖 Learn • Practice • Track • Certify • AI</p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>📚 Courses</h3>
            <p>Browse available courses and continue learning.</p>
            <a class="action-btn" href="course_catalog.php">
                📚 View Courses →
            </a>
        </div>

        <div class="card">
            <h3>📊 My Progress</h3>
            <p>View your saved quiz results and learning records.</p>
            <a class="action-btn" href="save_progress.php">
                📊 View Progress →
            </a>
        </div>

        <div class="card">
            <h3>📝 AI Important Notes</h3>
            <p>Access your learning notes and study material.</p>
            <a class="action-btn" href="ai_notes.php">
                💡 View Notes →
            </a>
        </div>

        <div class="card">
            <h3>💼 Career Intelligence</h3>
            <p>Explore your skills and career opportunities.</p>
            <a class="action-btn" href="career_intelligence.php">
                🚀 Explore Career →
            </a>
        </div>

    </div>

    <!-- MY CERTIFICATES TOGGLE -->
    <section id="certificates-section">

        <button
            type="button"
            id="certificates-toggle"
            onclick="toggleCertificates()"
            aria-expanded="false"
            aria-controls="certificates-content">

            <h3>
                🎓 My Certificates
                <span id="cert-arrow">▼</span>
            </h3>

            <p>Click to view your earned certificates.</p>
        </button>

        <div id="certificates-content" hidden>

            <?php if (count($certificates) > 0): ?>

                <?php foreach ($certificates as $row): ?>
                    <?php
                    $course_id = (int) $row['course_id'];
                    $viva_id = (int) $row['id'];
                    $course_name = $row['course_name'] ?? 'Course';

                    $viva_date = date(
                        'd M Y, h:i A',
                        strtotime($row['created_at'])
                    );
                    ?>

                    <div class="certificate-card">

                        <h3>
                            🏆 <?= htmlspecialchars($course_name) ?>
                        </h3>

                        <p class="status">Status: PASS</p>

                        <p>
                            <strong>Viva Record ID:</strong>
                            <?= $viva_id ?>
                        </p>

                        <p>
                            <strong>Score:</strong>
                            <?= htmlspecialchars((string)$row['total_score']) ?>
                            &nbsp; | &nbsp;
                            <strong>Percentage:</strong>
                            <?= htmlspecialchars((string)$row['percentage']) ?>%
                        </p>

                        <p>
                            <strong>Questions:</strong>
                            <?= (int)$row['attempted_questions'] ?>
                            / <?= (int)$row['total_questions'] ?>
                            &nbsp; | &nbsp;
                            <strong>Correct:</strong>
                            <?= (int)$row['correct_answers'] ?>
                        </p>

                        <p>
                            <strong>Viva Date:</strong>
                            <?= htmlspecialchars($viva_date) ?>
                        </p>

                        <a
                            class="action-btn"
                            href="../certificates/certificate.php?course_id=<?= $course_id ?>">
                            🎓 View Certificate →
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty">
                    <h3>📜 No Certificates Yet</h3>
                    <p>
                        Pass your AI Viva to become eligible for a certificate.
                    </p>
                    <a class="action-btn" href="course_catalog.php">
                        📚 Explore Courses →
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<footer>
    © <?= date('Y') ?> OnlineSkillPlatform | Learn • Practice • Certify
</footer>

<script>
function toggleCertificates() {
    const content = document.getElementById('certificates-content');
    const arrow = document.getElementById('cert-arrow');
    const button = document.getElementById('certificates-toggle');

    content.hidden = !content.hidden;
    arrow.textContent = content.hidden ? '▼' : '▲';

    button.setAttribute(
        'aria-expanded',
        content.hidden ? 'false' : 'true'
    );
}
</script>

</body>
</html>
