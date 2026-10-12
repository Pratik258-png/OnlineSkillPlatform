<?php

session_start();

include '../includes/db.php';


/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");

    exit;
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   COURSE ID FROM CERTIFICATE
========================================================= */

if (
    !isset($_GET['course_id']) ||
    (int)$_GET['course_id'] <= 0
) {

    die(
        "Course ID is required. Please open Resume Builder from the certificate page."
    );

}

$course_id = (int) $_GET['course_id'];


/* =========================================================
   DEFAULT DATA
========================================================= */

$name = '';
$email = '';

$career_goal = '';
$interests = '';
$bio = '';

$career_name = '';

$course = null;

$quiz = null;

$viva = null;

$course_skills = [];

$objective = '';

$course_text = '';

$skill_text = '';


/* =========================================================
   USER INFORMATION
========================================================= */

$stmt = $conn->prepare("
    SELECT
        name,
        email
    FROM users
    WHERE id = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $user = $result->fetch_assoc();

        $name =
            $user['name'] ?? '';

        $email =
            $user['email'] ?? '';

    }

    $stmt->close();

}


/* =========================================================
   STUDENT PROFILE
========================================================= */

$stmt = $conn->prepare("
    SELECT
        career_goal,
        interests,
        bio
    FROM student_profiles
    WHERE user_id = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $profile =
            $result->fetch_assoc();

        $career_goal =
            $profile['career_goal'] ?? '';

        $interests =
            $profile['interests'] ?? '';

        $bio =
            $profile['bio'] ?? '';

    }

    $stmt->close();

}


/* =========================================================
   CAREER RECOMMENDATION
========================================================= */

$stmt = $conn->prepare("
    SELECT
        career_name
    FROM career_recommendations
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $career =
            $result->fetch_assoc();

        $career_name =
            $career['career_name'] ?? '';

    }

    $stmt->close();

}


/* =========================================================
   GET ONLY SELECTED COURSE
========================================================= */

$stmt = $conn->prepare("
    SELECT
        c.id,
        c.course_name,
        c.instructor_name,
        c.duration,
        c.description,
        e.enrolled_at
    FROM courses c

    INNER JOIN enrollments e
        ON e.course_id = c.id

    WHERE c.id = ?
    AND e.user_id = ?

    LIMIT 1
");

if (!$stmt) {

    die(
        "Unable to load selected course."
    );

}

$stmt->bind_param(
    "ii",
    $course_id,
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

if (
    !$result ||
    $result->num_rows == 0
) {

    $stmt->close();

    die(
        "Selected course was not found for your account."
    );

}

$course =
    $result->fetch_assoc();

$stmt->close();


/* =========================================================
   COURSE SPECIFIC SKILLS / TOPICS
========================================================= */

function getCourseSkills($course_name)
{

    $name =
        strtolower(
            trim($course_name)
        );


    /* =====================================================
       JAVA C
    ===================================================== */

    if (
        strpos($name, 'java c') !== false ||
        strpos($name, 'java/c') !== false ||
        strpos($name, 'java-c') !== false ||
        (
            strpos($name, 'java') !== false &&
            strpos($name, 'c') !== false
        )
    ) {

        return [

            'Java',

            'C Programming',

            'Object-Oriented Programming',

            'Programming Fundamentals',

            'Variables and Data Types',

            'Control Statements',

            'Functions',

            'Arrays',

            'Classes and Objects',

            'Inheritance',

            'Polymorphism',

            'Exception Handling'

        ];

    }


    /* =====================================================
       JAVA
    ===================================================== */

    if (
        strpos($name, 'java') !== false
    ) {

        return [

            'Java',

            'Programming Fundamentals',

            'Variables and Data Types',

            'Control Statements',

            'Functions',

            'Classes and Objects',

            'Object-Oriented Programming',

            'Inheritance',

            'Polymorphism',

            'Exception Handling',

            'Collections'

        ];

    }


    /* =====================================================
       PYTHON
    ===================================================== */

    if (
        strpos($name, 'python') !== false
    ) {

        return [

            'Python',

            'Programming Fundamentals',

            'Variables and Data Types',

            'Operators',

            'Control Statements',

            'Functions',

            'Lists and Dictionaries',

            'Tuples and Sets',

            'Object-Oriented Programming',

            'File Handling',

            'Exception Handling'

        ];

    }


    /* =====================================================
       DATA ANALYTICS
    ===================================================== */

    if (
        strpos($name, 'data analytics') !== false ||
        strpos($name, 'analytics') !== false
    ) {

        return [

            'Data Analytics',

            'Python',

            'Data Analysis',

            'Data Cleaning',

            'Data Preprocessing',

            'Data Visualization',

            'Statistics',

            'Pandas',

            'NumPy',

            'Matplotlib',

            'Exploratory Data Analysis'

        ];

    }


    /* =====================================================
       WEB DEVELOPMENT
    ===================================================== */

    if (
        strpos($name, 'web development') !== false ||
        strpos($name, 'web development') !== false ||
        strpos($name, 'web') !== false
    ) {

        return [

            'HTML',

            'CSS',

            'JavaScript',

            'Bootstrap',

            'PHP',

            'MySQL',

            'Responsive Web Design',

            'Forms and Validation',

            'Database Connectivity',

            'Web Development Fundamentals'

        ];

    }


    /* =====================================================
       DATA STRUCTURE
    ===================================================== */

    if (
        strpos($name, 'data structure') !== false ||
        strpos($name, 'data structures') !== false
    ) {

        return [

            'Data Structures',

            'Arrays',

            'Linked Lists',

            'Stacks',

            'Queues',

            'Searching',

            'Sorting',

            'Trees',

            'Graphs',

            'Algorithms',

            'Time Complexity',

            'Space Complexity'

        ];

    }


    /* =====================================================
       DEFAULT
    ===================================================== */

    return [

        $course_name

    ];

}


/* =========================================================
   GET SELECTED COURSE SKILLS
========================================================= */

$course_skills =
    getCourseSkills(
        $course['course_name']
    );


$skill_text =
    implode(
        ', ',
        $course_skills
    );


/* =========================================================
   SELECTED COURSE NAME
========================================================= */

$course_text =
    $course['course_name'];


/* =========================================================
   QUIZ RESULT
   ONLY SELECTED COURSE
========================================================= */

$stmt = $conn->prepare("
    SELECT
        score,
        percentage,
        created_at
    FROM results

    WHERE user_id = ?
    AND course_id = ?

    ORDER BY created_at DESC

    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "ii",
        $user_id,
        $course_id
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $quiz =
            $result->fetch_assoc();

    }

    $stmt->close();

}


/* =========================================================
   VIVA / SKILL ASSESSMENT
   ONLY SELECTED COURSE
========================================================= */

$stmt = $conn->prepare("
    SELECT
        total_questions,
        attempted_questions,
        correct_answers,
        total_score,
        percentage,
        status,
        created_at
    FROM viva_results

    WHERE user_id = ?
    AND course_id = ?

    ORDER BY created_at DESC

    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "ii",
        $user_id,
        $course_id
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $viva =
            $result->fetch_assoc();

    }

    $stmt->close();

}


/* =========================================================
   CAREER OBJECTIVE
========================================================= */

if (!empty($career_goal)) {

    $objective =
        "Motivated computer science graduate seeking to build a professional career in "
        . $career_goal
        . ", with practical knowledge developed through "
        . $course_text
        . " and structured skill assessment.";

}
elseif (!empty($career_name)) {

    $objective =
        "Motivated computer science graduate seeking to build a professional career as a "
        . $career_name
        . ", with practical knowledge developed through "
        . $course_text
        . " and verified technical skills.";

}
else {

    $objective =
        "Motivated computer science graduate with practical knowledge in "
        . implode(
            ', ',
            array_slice(
                $course_skills,
                0,
                5
            )
        )
        . ", developed through "
        . $course_text
        . ", seeking an opportunity to apply technical skills in a professional environment.";

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Professional Resume Builder
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background: #f5f6f8;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #212529;

}


.container-main {

    max-width: 1100px;

    margin: 30px auto;

    padding: 0 15px;

}


/* =====================================================
   HEADER
===================================================== */

.header-box {

    background: #ffffff;

    padding: 25px;

    border: 1px solid #ddd;

    border-radius: 8px;

    margin-bottom: 20px;

}


.header-box h2 {

    margin: 0;

    font-size: 28px;

    font-weight: 700;

}


.header-box p {

    margin: 8px 0 0;

    color: #666;

}


/* =====================================================
   SELECTED COURSE
===================================================== */

.course-banner {

    background: #212529;

    color: white;

    padding: 20px 24px;

    border-radius: 7px;

    margin-bottom: 20px;

}


.course-title {

    font-size: 23px;

    font-weight: 700;

}


.course-info {

    margin-top: 7px;

    font-size: 14px;

    opacity: 0.85;

}


/* =====================================================
   SECTION
===================================================== */

.section {

    background: #ffffff;

    border: 1px solid #ddd;

    border-radius: 8px;

    padding: 25px;

    margin-bottom: 20px;

}


.section h4 {

    font-size: 18px;

    font-weight: 700;

    margin: 0 0 20px;

    padding-bottom: 10px;

    border-bottom: 1px solid #ddd;

}


/* =====================================================
   FORM
===================================================== */

.form-group {

    margin-bottom: 18px;

}


label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

}


input,
textarea {

    width: 100%;

    padding: 11px 12px;

    border: 1px solid #ced4da;

    border-radius: 5px;

    font-size: 14px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


input:focus,
textarea:focus {

    outline: none;

    border-color: #666;

}


textarea {

    resize: vertical;

}


.row {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;

}


/* =====================================================
   AUTO DATA
===================================================== */

.auto-data {

    background: #f8f9fa;

    border: 1px solid #ddd;

    border-radius: 6px;

    padding: 18px;

}


/* =====================================================
   SKILLS
===================================================== */

.skill {

    display: inline-block;

    padding: 7px 12px;

    border: 1px solid #999;

    border-radius: 4px;

    background: #ffffff;

    margin: 4px;

    font-size: 14px;

}


/* =====================================================
   COURSE TOPICS
===================================================== */

.topic-list {

    margin-top: 12px;

}


.topic-item {

    padding: 7px 0;

    border-bottom: 1px solid #e1e1e1;

    font-size: 14px;

}


.topic-item:last-child {

    border-bottom: none;

}


/* =====================================================
   SCORE
===================================================== */

.score-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.score-box {

    background: #f8f9fa;

    border: 1px solid #ddd;

    padding: 18px;

    border-radius: 6px;

    text-align: center;

}


.score-box strong {

    display: block;

    margin-bottom: 8px;

    font-size: 14px;

}


.score-value {

    font-size: 22px;

    font-weight: 700;

}


.empty {

    color: #777;

}


/* =====================================================
   BUTTONS
===================================================== */

.button-area {

    display: flex;

    gap: 10px;

    align-items: center;

}


.btn-create {

    background: #212529;

    color: white;

    border: none;

    padding: 13px 25px;

    border-radius: 5px;

    font-weight: 600;

    cursor: pointer;

}


.btn-create:hover {

    background: #000;

}


.btn-back {

    padding: 12px 25px;

    border: 1px solid #999;

    border-radius: 5px;

    text-decoration: none;

    color: #212529;

    background: white;

}


@media (max-width: 700px) {

    .row {

        grid-template-columns: 1fr;

    }


    .score-grid {

        grid-template-columns:
            1fr;

    }


    .button-area {

        flex-direction: column;

        align-items: stretch;

    }

}

</style>

</head>


<body>


<div class="container-main">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="header-box">

<h2>

Professional Resume Builder

</h2>


<p>

Create a professional resume using your verified
OnlineSkill learning information.

</p>

</div>


<!-- =====================================================
     SELECTED COURSE
===================================================== -->

<div class="course-banner">

<div class="course-title">

<?php

echo htmlspecialchars(
    $course['course_name']
);

?>

</div>


<div class="course-info">

Instructor:

<?php

echo htmlspecialchars(
    $course['instructor_name'] ?? 'N/A'
);

?>


<?php

if (!empty($course['duration'])) {

?>

&nbsp; | &nbsp;

Duration:

<?php

echo htmlspecialchars(
    $course['duration']
);

?>

<?php

}

?>

</div>

</div>


<form
    method="POST"
    action="resume_preview.php"
>


<!-- =====================================================
     COURSE ID
===================================================== -->

<input
    type="hidden"
    name="course_id"
    value="<?php echo (int)$course_id; ?>"
>


<input
    type="hidden"
    name="course_name"
    value="<?php echo htmlspecialchars($course_text); ?>"
>


<!-- =====================================================
     PERSONAL INFORMATION
===================================================== -->

<div class="section">

<h4>
Personal Information
</h4>


<div class="row">


<div class="form-group">

<label>
Full Name
</label>


<input
    type="text"
    name="name"
    value="<?php echo htmlspecialchars($name); ?>"
    readonly
>

</div>


<div class="form-group">

<label>
Email
</label>


<input
    type="email"
    name="email"
    value="<?php echo htmlspecialchars($email); ?>"
    readonly
>

</div>


<div class="form-group">

<label>
Mobile Number
</label>


<input
    type="text"
    name="mobile"
    placeholder="Enter mobile number"
>

</div>


<div class="form-group">

<label>
Location
</label>


<input
    type="text"
    name="location"
    placeholder="Enter location"
>

</div>


<div class="form-group">

<label>
LinkedIn
</label>


<input
    type="text"
    name="linkedin"
    placeholder="LinkedIn profile"
>

</div>


<div class="form-group">

<label>
GitHub
</label>


<input
    type="text"
    name="github"
    placeholder="GitHub profile"
>

</div>


</div>

</div>


<!-- =====================================================
     CAREER OBJECTIVE
===================================================== -->

<div class="section">

<h4>
Career Objective
</h4>


<textarea
    name="objective"
    rows="4"
><?php echo htmlspecialchars($objective); ?></textarea>

</div>


<!-- =====================================================
     EDUCATION
===================================================== -->

<div class="section">

<h4>
Education
</h4>


<textarea
    name="education"
    rows="6"
    placeholder="Enter your education details"
></textarea>

</div>


<!-- =====================================================
     VERIFIED SKILLS
===================================================== -->

<div class="section">

<h4>
Verified Skills
</h4>


<p>

Skills are automatically selected according to the
selected course.

</p>


<div class="auto-data">

<?php

foreach (
    $course_skills
    as $skill
) {

    echo '<span class="skill">';

    echo htmlspecialchars(
        $skill
    );

    echo '</span>';

}

?>

</div>


<input
    type="hidden"
    name="skills"
    value="<?php echo htmlspecialchars($skill_text); ?>"
>

</div>


<!-- =====================================================
     COURSE & CERTIFICATION
===================================================== -->

<div class="section">

<h4>
Course & Certification
</h4>


<div class="auto-data">


<strong>

<?php

echo htmlspecialchars(
    $course['course_name']
);

?>

</strong>


<br><br>


<div>

OnlineSkill Learning Platform

</div>


<br>


<div>

Instructor:

<?php

echo htmlspecialchars(
    $course['instructor_name'] ?? 'N/A'
);

?>

</div>


<?php

if (!empty($course['duration'])) {

?>

<div>

Duration:

<?php

echo htmlspecialchars(
    $course['duration']
);

?>

</div>

<?php

}

?>


<br>


<strong>

Course Topics

</strong>


<div class="topic-list">


<?php

foreach (
    $course_skills
    as $skill
) {

?>

<div class="topic-item">

<?php

echo htmlspecialchars(
    $skill
);

?>

</div>

<?php

}

?>


</div>


</div>

</div>


<!-- =====================================================
     QUIZ PERFORMANCE
===================================================== -->

<div class="section">

<h4>
Quiz Performance
</h4>


<?php

if ($quiz) {

?>


<div class="score-grid">


<div class="score-box">

<strong>
Quiz Score
</strong>


<div class="score-value">

<?php

echo htmlspecialchars(
    $quiz['score'] ?? 0
);

?>

</div>

</div>


<div class="score-box">

<strong>
Quiz Percentage
</strong>


<div class="score-value">

<?php

echo htmlspecialchars(
    $quiz['percentage'] ?? 0
);

?>%

</div>

</div>


</div>


<?php

}

else {

?>


<div class="empty">

No quiz result available for this course.

</div>


<?php

}

?>

</div>


<!-- =====================================================
     SKILL ASSESSMENT
===================================================== -->

<div class="section">

<h4>
Skill Assessment
</h4>


<?php

if ($viva) {

?>


<div class="score-grid">


<div class="score-box">

<strong>
AI Viva Assessment Score
</strong>


<div class="score-value">

<?php

echo htmlspecialchars(
    $viva['total_score'] ?? 0
);

?>/100

</div>

</div>


<div class="score-box">

<strong>
Percentage
</strong>


<div class="score-value">

<?php

echo htmlspecialchars(
    $viva['percentage'] ?? 0
);

?>%

</div>

</div>


<div class="score-box">

<strong>
Status
</strong>


<div class="score-value">

<?php

echo htmlspecialchars(
    $viva['status'] ?? 'N/A'
);

?>

</div>

</div>


</div>


<input
    type="hidden"
    name="assessment_score"
    value="<?php echo htmlspecialchars($viva['total_score'] ?? 0); ?>"
>


<input
    type="hidden"
    name="assessment_percentage"
    value="<?php echo htmlspecialchars($viva['percentage'] ?? 0); ?>"
>


<input
    type="hidden"
    name="viva_score"
    value="<?php echo htmlspecialchars($viva['total_score'] ?? 0); ?>"
>


<input
    type="hidden"
    name="viva_percentage"
    value="<?php echo htmlspecialchars($viva['percentage'] ?? 0); ?>"
>


<input
    type="hidden"
    name="viva_status"
    value="<?php echo htmlspecialchars($viva['status'] ?? ''); ?>"
>


<?php

}

else {

?>


<div class="empty">

No skill assessment result available for this course.

</div>


<input
    type="hidden"
    name="assessment_score"
    value=""
>


<input
    type="hidden"
    name="assessment_percentage"
    value=""
>


<input
    type="hidden"
    name="viva_score"
    value="0"
>


<input
    type="hidden"
    name="viva_percentage"
    value="0"
>


<input
    type="hidden"
    name="viva_status"
    value=""
>


<?php

}

?>

</div>


<!-- =====================================================
     PROJECTS
===================================================== -->

<div class="section">

<h4>
Projects
</h4>


<textarea
    name="projects"
    rows="6"
    placeholder="Enter your academic or personal projects"
></textarea>

</div>


<!-- =====================================================
     ACHIEVEMENTS
===================================================== -->

<div class="section">

<h4>
Achievements
</h4>


<textarea
    name="achievements"
    rows="5"
    placeholder="Enter your achievements, hackathons or other certifications"
></textarea>

</div>


<!-- =====================================================
     INTERESTS
===================================================== -->

<div class="section">

<h4>
Interests
</h4>


<textarea
    name="interests"
    rows="3"
><?php echo htmlspecialchars($interests); ?></textarea>

</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="section">

<div class="button-area">


<button
    type="submit"
    class="btn-create"
>

Create Professional Resume

</button>


<a
    href="dashboard.php"
    class="btn-back"
>

Back to Dashboard

</a>


</div>

</div>


</form>


</div>


</body>

</html>