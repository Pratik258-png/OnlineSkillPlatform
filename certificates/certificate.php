
<?php

include '../includes/db.php';

session_start();

/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$user_id = intval($_SESSION['user_id']);


/* =====================================================
   CHECK COURSE ID
===================================================== */

if (!isset($_GET['course_id'])) {
    die("Course ID not found.");
}

$course_id = intval($_GET['course_id']);


/* =====================================================
   GET COURSE
===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM courses WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Course not found.");
}

$course = mysqli_fetch_assoc($result);


/* =====================================================
   STUDENT NAME
===================================================== */

$student_name =
    $_SESSION['student_name']
    ?? $_SESSION['name']
    ?? $_SESSION['username']
    ?? "Student";


/* =====================================================
   COURSE NAME
===================================================== */

$course_name =
    $course['course_name']
    ?? $course['title']
    ?? "Course";


/* =====================================================
   CHECK AI VIVA RESULT
===================================================== */

$viva_passed = false;
$viva_score = 0;
$viva_percentage = 0;
$viva_status = "NOT_ATTEMPTED";
$attempted_questions = 0;


/*
   AI VIVA CERTIFICATE RULE

   Total Questions = 10
   Total Marks = 100
   PASS = 35 / 100 or above

   Certificate unlocks only when:
   1. All 10 questions are attempted
   2. Score is at least 35
   3. Status is PASS
*/

$viva_stmt = mysqli_prepare(
    $conn,
    "SELECT total_score, percentage, status, attempted_questions
     FROM viva_results
     WHERE user_id = ?
     AND course_id = ?
     ORDER BY id DESC
     LIMIT 1"
);

mysqli_stmt_bind_param($viva_stmt, "ii", $user_id, $course_id);
mysqli_stmt_execute($viva_stmt);

$viva_result = mysqli_stmt_get_result($viva_stmt);

if ($viva_result && mysqli_num_rows($viva_result) > 0) {

    $viva = mysqli_fetch_assoc($viva_result);

    $viva_score = floatval($viva['total_score']);
    $viva_percentage = floatval($viva['percentage']);
    $viva_status = strtoupper(trim($viva['status']));
    $attempted_questions = intval($viva['attempted_questions']);

    if (
        $attempted_questions >= 10
        && $viva_score >= 35
        && $viva_status === "PASS"
    ) {
        $viva_passed = true;
    }
}


/* =====================================================
   DATE
===================================================== */

$completion_date = date("d F Y");


/* =====================================================
   CERTIFICATE NUMBER
===================================================== */

$certificate_number =
    "OSP-" .
    date("Y") .
    "-" .
    str_pad($course_id, 4, "0", STR_PAD_LEFT);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php
echo $viva_passed
    ? "Certificate of Completion"
    : "Certificate Locked";
?>
</title>

<style>

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {
    background: #eef1f5;
    font-family: Georgia, "Times New Roman", serif;
}

/* =====================================================
   PROFESSIONAL BACK BUTTON
===================================================== */

.back-btn,
.back-btn2 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 22px;
    margin: 10px 6px;
    background: #ffffff;
    color: #243b80;
    border: 1px solid #dbe3f0;
    border-radius: 50px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(36, 59, 128, 0.08);
    transition: all 0.25s ease;
}

.back-btn:hover,
.back-btn2:hover {
    background: #243b80;
    color: #ffffff;
    border-color: #243b80;
    transform: translateX(-3px);
    box-shadow: 0 6px 16px rgba(36, 59, 128, 0.18);
}

.back-btn:focus-visible,
.back-btn2:focus-visible {
    outline: 3px solid #93c5fd;
    outline-offset: 3px;
}

/* =====================================================
   LOCKED BOX
===================================================== */

.locked-container {
    width: 700px;
    max-width: 92%;
    margin: 100px auto;
    padding: 55px 40px;
    background: white;
    text-align: center;
    border-radius: 15px;
    border: 2px solid #c9a227;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.lock-icon {
    font-size: 65px;
    margin-bottom: 15px;
}

.locked-container h1 {
    margin: 10px 0;
    font-family: Arial, sans-serif;
    color: #222;
    font-size: 32px;
}

.locked-container h2 {
    margin: 15px 0;
    font-family: Arial, sans-serif;
    color: #9b7410;
    font-size: 23px;
}

.locked-container p {
    font-family: Arial, sans-serif;
    color: #666;
    font-size: 16px;
    line-height: 1.6;
}

.course-name {
    font-weight: bold;
    color: #333;
    font-size: 19px;
    margin: 20px 0;
}

.viva-info {
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    font-family: Arial, sans-serif;
    color: #555;
    line-height: 1.6;
}

.viva-marks {
    margin-top: 15px;
    padding: 12px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    font-size: 18px;
    font-weight: bold;
}

.pass-marks {
    color: #198754;
}

.fail-marks {
    color: #dc3545;
}

.viva-btn {
    display: inline-block;
    padding: 14px 28px;
    margin-top: 15px;
    background: #6f42c1;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-family: Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
}

.viva-btn:hover {
    background: #59339d;
}

/* =====================================================
   CERTIFICATE
===================================================== */

.certificate {
    position: relative;
    width: 1100px;
    height: 760px;
    margin: 30px auto;
    padding: 65px 80px;
    background: #ffffff;
    text-align: center;
    border: 2px solid #c9a227;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    overflow: hidden;
}

.certificate::before {
    content: "";
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    bottom: 15px;
    border: 1px solid #d8bd65;
    pointer-events: none;
}

.top-line {
    width: 120px;
    height: 4px;
    margin: 0 auto 25px;
    background: #c9a227;
}

.official {
    font-size: 15px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #777;
    margin-bottom: 18px;
}

h1 {
    margin: 0;
    font-size: 58px;
    letter-spacing: 9px;
    font-weight: 600;
    color: #1d1d1d;
}

h2 {
    margin: 10px 0 35px;
    font-size: 24px;
    letter-spacing: 7px;
    font-weight: normal;
    color: #a27b12;
}

.presented {
    margin-top: 20px;
    font-size: 18px;
    color: #555;
}

.student {
    display: inline-block;
    min-width: 450px;
    margin: 22px auto 20px;
    padding: 8px 35px 12px;
    font-size: 39px;
    font-weight: bold;
    color: #171717;
    border-bottom: 2px solid #c9a227;
}

.completed {
    margin-top: 8px;
    font-size: 18px;
    color: #555;
}

.course {
    margin: 15px auto;
    font-size: 32px;
    font-weight: bold;
    color: #9b7410;
}

.description {
    max-width: 720px;
    margin: 20px auto;
    font-size: 16px;
    line-height: 1.7;
    color: #666;
}

.date {
    margin-top: 20px;
    font-size: 16px;
    color: #444;
}

.viva-score {
    margin-top: 12px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #555;
}

.viva-score strong {
    color: #198754;
}

/* =====================================================
   BOTTOM SECTION
===================================================== */

.bottom-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    max-width: 800px;
    margin: 45px auto 0;
}

.signature {
    width: 250px;
    text-align: center;
}

.signature-line {
    border-top: 1px solid #333;
    margin-bottom: 8px;
}

.signature-title {
    font-size: 14px;
    color: #666;
    letter-spacing: 1px;
}

/* =====================================================
   SEAL
===================================================== */

.seal {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #c9a227;
    outline: 2px solid #e2ce86;
    outline-offset: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fffdf5;
    font-size: 38px;
}

/* =====================================================
   CERTIFICATE NUMBER
===================================================== */

.certificate-number {
    position: absolute;
    bottom: 28px;
    left: 0;
    right: 0;
    font-family: Arial, sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    color: #888;
}

/* =====================================================
   BUTTONS
===================================================== */

.buttons {
    text-align: center;
    margin: 25px 0 40px;
}

.print-btn,
.resume-btn {
    display: inline-block;
    padding: 12px 24px;
    margin: 5px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-family: Arial, sans-serif;
    font-weight: bold;
    transition: all 0.3s ease;
}

.print-btn {
    background: #198754;
    color: white;
}

.print-btn:hover {
    background: #157347;
}

.resume-btn {
    background: #212529;
    color: white;
}

.resume-btn:hover {
    background: #000000;
}

/* =====================================================
   PRINT
===================================================== */

@media print {

    @page {
        size: A4 landscape;
        margin: 0;
    }

    html {
        width: 297mm;
        height: 210mm;
    }

    body {
        width: 297mm;
        height: 210mm;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        overflow: hidden;
    }

    .certificate {
        position: relative;
        width: 297mm;
        height: 210mm;
        margin: 0;
        padding: 17mm 22mm;
        border: 2px solid #c9a227;
        box-shadow: none;
        overflow: hidden;
        page-break-after: avoid;
        page-break-before: avoid;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .certificate::before {
        top: 5mm;
        left: 5mm;
        right: 5mm;
        bottom: 5mm;
    }

    .top-line {
        width: 32mm;
        height: 1mm;
        margin-bottom: 5mm;
    }

    .official {
        font-size: 11px;
        letter-spacing: 3px;
        margin-bottom: 3mm;
    }

    h1 {
        font-size: 43px;
        letter-spacing: 7px;
        margin: 0;
    }

    h2 {
        font-size: 19px;
        letter-spacing: 5px;
        margin: 2mm 0 7mm;
    }

    .presented {
        margin-top: 2mm;
        font-size: 14px;
    }

    .student {
        min-width: 120mm;
        margin: 4mm auto 4mm;
        padding: 2mm 8mm 3mm;
        font-size: 30px;
    }

    .completed {
        margin-top: 2mm;
        font-size: 14px;
    }

    .course {
        margin: 3mm auto;
        font-size: 25px;
    }

    .description {
        max-width: 175mm;
        margin: 4mm auto;
        font-size: 13px;
        line-height: 1.45;
    }

    .date {
        margin-top: 4mm;
        font-size: 13px;
    }

    .viva-score {
        font-size: 10px;
    }

    .bottom-section {
        max-width: 190mm;
        margin: 10mm auto 0;
    }

    .signature {
        width: 52mm;
    }

    .signature-line {
        margin-bottom: 2mm;
    }

    .signature-title {
        font-size: 11px;
    }

    .seal {
        width: 23mm;
        height: 23mm;
        font-size: 25px;
        border-width: 2px;
    }

    .certificate-number {
        bottom: 7mm;
        font-size: 8px;
    }

    .buttons {
        display: none !important;
    }
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media screen and (max-width: 1150px) {

    .certificate {
        width: 95%;
        height: auto;
        min-height: 700px;
    }
}

@media screen and (max-width: 700px) {

    .locked-container {
        margin: 50px auto;
        padding: 40px 20px;
    }

    .certificate {
        padding: 45px 20px;
        min-height: auto;
    }

    .certificate h1 {
        font-size: 32px;
        letter-spacing: 4px;
    }

    .certificate h2 {
        font-size: 18px;
        letter-spacing: 3px;
    }

    .student {
        min-width: 0;
        width: 100%;
        font-size: 27px;
        overflow-wrap: anywhere;
    }

    .course {
        font-size: 24px;
        overflow-wrap: anywhere;
    }

    .bottom-section {
        gap: 20px;
        align-items: center;
    }

    .signature {
        width: 40%;
    }

    .seal {
        width: 60px;
        height: 60px;
        font-size: 27px;
        flex-shrink: 0;
    }

    .certificate-number {
        position: relative;
        margin-top: 30px;
        bottom: auto;
    }
}

</style>

</head>

<body>

<?php if (!$viva_passed): ?>

<!-- =====================================================
     CERTIFICATE LOCKED
===================================================== -->

<div class="locked-container">

    <div class="lock-icon">🔒</div>

    <h1>Certificate Locked</h1>

    <h2>AI Viva Required</h2>

    <div class="course-name">
        <?php echo htmlspecialchars($course_name); ?>
    </div>

    <p>
        You must complete the
        <strong>AI Viva</strong>
        for this course before your certificate can be unlocked.
    </p>

    <div class="viva-info">

        <strong>AI Viva Requirement</strong>

        <br><br>

        Complete all
        <strong>10 AI Viva questions</strong>
        and obtain at least
        <strong>35 / 100 marks</strong>
        to unlock your certificate.

        <?php if ($viva_status !== "NOT_ATTEMPTED"): ?>

            <div class="viva-marks <?php echo ($viva_score >= 35) ? 'pass-marks' : 'fail-marks'; ?>">

                Last Viva Score:
                <?php echo number_format($viva_score, 1); ?>
                / 100 Marks

                <br>

                Percentage:
                <?php echo number_format($viva_percentage, 1); ?>%

                <br>

                Attempted:
                <?php echo $attempted_questions; ?>/10

            </div>

        <?php endif; ?>

    </div>

    <a
        href="../student/ai_viva.php?course_id=<?php echo $course_id; ?>"
        class="viva-btn">
        🤖 Start AI Viva
    </a>

    <br>

    <!-- PROFESSIONAL BACK TO COURSES -->

    <a
        href="../student/course_catalog.php"
        class="back-btn">
        <span aria-hidden="true">←</span>
        Back to Courses
    </a>

</div>

<?php else: ?>

<!-- =====================================================
     CERTIFICATE
===================================================== -->

<div class="certificate">

    <div class="top-line"></div>

    <div class="official">
        Professional Learning Certificate
    </div>

    <h1>CERTIFICATE</h1>

    <h2>OF COMPLETION</h2>

    <div class="presented">
        This certificate is proudly presented to
    </div>

    <div class="student">
        <?php echo htmlspecialchars($student_name); ?>
    </div>

    <div class="completed">
        for successfully completing the course
    </div>

    <div class="course">
        <?php echo htmlspecialchars($course_name); ?>
    </div>

    <div class="description">
        This certificate recognizes the successful
        completion of the required learning content,
        assessment quiz, and AI Viva.
    </div>

    <div class="date">
        <strong>Date of Completion:</strong>
        <?php echo htmlspecialchars($completion_date); ?>
    </div>

    <!-- VIVA SCORE -->

    <div class="viva-score">

        <strong>AI Viva Score:</strong>
        <?php echo number_format($viva_score, 1); ?>
        / 100 Marks

        &nbsp; | &nbsp;

        <strong>Percentage:</strong>
        <?php echo number_format($viva_percentage, 1); ?>%

        &nbsp; | &nbsp;

        <strong>Result: PASS</strong>

    </div>

    <div class="bottom-section">

        <div class="signature">
            <div class="signature-line"></div>
            <div class="signature-title">
                Authorized Signature
            </div>
        </div>

        <div class="seal">
            🎓
        </div>

        <div class="signature">
            <div class="signature-line"></div>
            <div class="signature-title">
                Course Completion
            </div>
        </div>

    </div>

    <div class="certificate-number">

        Certificate No:
        <?php echo htmlspecialchars($certificate_number); ?>

    </div>

</div>

<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="buttons">

    <button
        onclick="window.print()"
        class="print-btn">
        🎓 Download / Save Certificate
    </button>

    <a
        href="../student/resume_builder.php?course_id=<?php echo $course_id; ?>"
        class="resume-btn">
        Create Resume
    </a>

    <!-- PROFESSIONAL BACK BUTTON -->

    <a
        href="javascript:void(0)"
        class="back-btn2"
        onclick="goBackToPreviousPage()"
        aria-label="Go back to the previous page">

        <span aria-hidden="true">←</span>
        Back

    </a>

</div>

<?php endif; ?>

<script>

/* =====================================================
   BACK BUTTON
   Return to the previous same-site page.
   Use Student Dashboard if no suitable previous page exists.
===================================================== */

function goBackToPreviousPage() {

    const fallbackUrl = "../student/dashboard.php";

    if (document.referrer) {

        try {

            const previousPage = new URL(document.referrer);

            if (previousPage.origin === window.location.origin) {
                window.location.href = previousPage.href;
                return;
            }

        } catch (error) {
            // Use the fallback page below.
        }

    }

    window.location.href = fallbackUrl;
}

</script>

</body>
</html>
