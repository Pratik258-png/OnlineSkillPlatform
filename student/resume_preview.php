<?php

session_start();


/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");

    exit;

}


/* =====================================================
   SAFE POST FUNCTION
===================================================== */

function post_value($name)
{
    return isset($_POST[$name])
        ? trim($_POST[$name])
        : '';
}


/* =====================================================
   GET DATA
===================================================== */

$name =
    post_value('name');

$email =
    post_value('email');

$mobile =
    post_value('mobile');

$location =
    post_value('location');

$linkedin =
    post_value('linkedin');

$github =
    post_value('github');

$title =
    post_value('title');

$objective =
    post_value('objective');

$education =
    post_value('education');

$skills =
    post_value('skills');

$courses =
    post_value('courses');

$projects =
    post_value('projects');

$achievements =
    post_value('achievements');

$interests =
    post_value('interests');

$assessment_score =
    post_value('assessment_score');

$assessment_percentage =
    post_value('assessment_percentage');


/* =====================================================
   ESCAPE FUNCTION
===================================================== */

function e($value)
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =====================================================
   FORMAT MULTILINE TEXT
===================================================== */

function format_text($value)
{
    return nl2br(
        htmlspecialchars(
            $value,
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/* =====================================================
   SPLIT COURSES
===================================================== */

$course_list = [];

if (!empty($courses)) {

    $course_list =
        explode(
            "|",
            $courses
        );

}


/* =====================================================
   SPLIT SKILLS
===================================================== */

$skill_list = [];

if (!empty($skills)) {

    $skill_list =
        explode(
            ",",
            $skills
        );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    <?php echo e($name); ?> - Resume
</title>


<style>

/* =====================================================
   PAGE
===================================================== */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background: #e9ecef;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #222;

}


/* =====================================================
   ACTION BAR
===================================================== */

.actions {

    width: 210mm;

    max-width: 95%;

    margin: 20px auto;

    text-align: right;

}


.actions button,
.actions a {

    display: inline-block;

    padding: 11px 20px;

    margin-left: 6px;

    border: none;

    border-radius: 5px;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;

    font-family: Arial, sans-serif;

}


.pdf-btn {

    background: #212529;

    color: white;

}


.back-btn {

    background: #6c757d;

    color: white;

}


/* =====================================================
   A4 RESUME
===================================================== */

.resume {

    width: 210mm;

    min-height: 297mm;

    margin: 0 auto 40px;

    padding: 18mm 18mm 16mm;

    background: white;

    box-shadow:
        0 5px 25px
        rgba(0,0,0,0.15);

}


/* =====================================================
   HEADER
===================================================== */

.header {

    border-bottom: 2px solid #222;

    padding-bottom: 12px;

}


.name {

    margin: 0;

    font-size: 30px;

    font-weight: 700;

    letter-spacing: 1px;

}


.title {

    margin-top: 5px;

    font-size: 15px;

    color: #555;

    font-weight: 600;

}


/* =====================================================
   CONTACT
===================================================== */

.contact {

    margin-top: 10px;

    font-size: 12px;

    line-height: 1.7;

    color: #444;

}


/* =====================================================
   SECTIONS
===================================================== */

.section {

    margin-top: 17px;

}


.section-title {

    margin: 0 0 8px;

    padding-bottom: 5px;

    border-bottom: 1px solid #bbb;

    font-size: 14px;

    font-weight: 700;

    letter-spacing: 1px;

    text-transform: uppercase;

}


.section-content {

    font-size: 12.5px;

    line-height: 1.6;

}


/* =====================================================
   SKILLS
===================================================== */

.skills {

    line-height: 1.8;

}


.skill-item {

    display: inline;

}


/* =====================================================
   COURSES
===================================================== */

.course {

    margin-bottom: 8px;

}


.course-name {

    font-weight: 700;

}


.course-name::after {

    content: "";

}


/* =====================================================
   ASSESSMENT
===================================================== */

.assessment {

    border: 1px solid #ccc;

    padding: 10px 12px;

}


.assessment-row {

    display: flex;

    justify-content: space-between;

    padding: 3px 0;

}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    color: #777;

}


/* =====================================================
   PRINT
===================================================== */

@media print {

    @page {

        size: A4;

        margin: 0;

    }


    html,
    body {

        margin: 0;

        padding: 0;

        background: white;

    }


    .actions {

        display: none !important;

    }


    .resume {

        width: 210mm;

        min-height: 297mm;

        margin: 0;

        padding: 16mm 17mm;

        box-shadow: none;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     ACTIONS
===================================================== -->

<div class="actions">

    <button
        class="pdf-btn"
        onclick="window.print();">

        Download / Save as PDF

    </button>


    <a
        href="resume_builder.php"
        class="back-btn">

        Edit Resume

    </a>

</div>


<!-- =====================================================
     RESUME
===================================================== -->

<div class="resume">


    <!-- =================================================
         HEADER
    ================================================= -->

    <div class="header">

        <h1 class="name">

            <?php

            echo e(
                strtoupper($name)
            );

            ?>

        </h1>


        <?php if (!empty($title)): ?>

            <div class="title">

                <?php

                echo e($title);

                ?>

            </div>

        <?php endif; ?>


        <div class="contact">

            <?php if (!empty($email)): ?>

                <?php echo e($email); ?>

            <?php endif; ?>


            <?php if (!empty($mobile)): ?>

                <?php if (!empty($email)): ?>
                    &nbsp; | &nbsp;
                <?php endif; ?>

                <?php echo e($mobile); ?>

            <?php endif; ?>


            <?php if (!empty($location)): ?>

                &nbsp; | &nbsp;

                <?php echo e($location); ?>

            <?php endif; ?>


            <?php if (!empty($linkedin)): ?>

                <br>

                LinkedIn:
                <?php echo e($linkedin); ?>

            <?php endif; ?>


            <?php if (!empty($github)): ?>

                <?php if (!empty($linkedin)): ?>
                    &nbsp; | &nbsp;
                <?php endif; ?>

                GitHub:
                <?php echo e($github); ?>

            <?php endif; ?>

        </div>

    </div>


    <!-- =================================================
         OBJECTIVE
    ================================================= -->

    <?php if (!empty($objective)): ?>

    <div class="section">

        <div class="section-title">

            Career Objective

        </div>


        <div class="section-content">

            <?php

            echo format_text(
                $objective
            );

            ?>

        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         EDUCATION
    ================================================= -->

    <?php if (!empty($education)): ?>

    <div class="section">

        <div class="section-title">

            Education

        </div>


        <div class="section-content">

            <?php

            echo format_text(
                $education
            );

            ?>

        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         TECHNICAL SKILLS
    ================================================= -->

    <?php if (!empty($skills)): ?>

    <div class="section">

        <div class="section-title">

            Technical Skills

        </div>


        <div class="section-content skills">

            <?php

            $clean_skills = [];

            foreach ($skill_list as $skill) {

                $skill = trim($skill);

                if ($skill !== '') {

                    $clean_skills[] = $skill;

                }

            }


            echo e(
                implode(
                    " | ",
                    $clean_skills
                )
            );

            ?>

        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         COURSES
    ================================================= -->

    <?php if (count($course_list) > 0): ?>

    <div class="section">

        <div class="section-title">

            Courses & Certifications

        </div>


        <div class="section-content">


            <?php foreach ($course_list as $course): ?>

                <?php

                $course = trim($course);

                if ($course === '') {
                    continue;
                }

                ?>


                <div class="course">

                    <div class="course-name">

                        <?php

                        echo e($course);

                        ?>

                    </div>

                    <div>

                        OnlineSkill Learning Platform

                    </div>

                </div>


            <?php endforeach; ?>


        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         SKILL ASSESSMENT
    ================================================= -->

    <?php if (
        $assessment_score !== ''
        ||
        $assessment_percentage !== ''
    ): ?>

    <div class="section">

        <div class="section-title">

            Skill Assessment

        </div>


        <div class="assessment">


            <?php if ($assessment_score !== ''): ?>

            <div class="assessment-row">

                <span>
                    Assessment Score
                </span>

                <strong>

                    <?php

                    echo e(
                        $assessment_score
                    );

                    ?>

                    / 100

                </strong>

            </div>

            <?php endif; ?>


            <?php if ($assessment_percentage !== ''): ?>

            <div class="assessment-row">

                <span>
                    Percentage
                </span>

                <strong>

                    <?php

                    echo e(
                        $assessment_percentage
                    );

                    ?>%

                </strong>

            </div>

            <?php endif; ?>


            <div class="assessment-row">

                <span>
                    Result
                </span>

                <strong>
                    Verified
                </strong>

            </div>


        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         PROJECTS
    ================================================= -->

    <?php if (!empty($projects)): ?>

    <div class="section">

        <div class="section-title">

            Projects

        </div>


        <div class="section-content">

            <?php

            echo format_text(
                $projects
            );

            ?>

        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         ACHIEVEMENTS
    ================================================= -->

    <?php if (!empty($achievements)): ?>

    <div class="section">

        <div class="section-title">

            Achievements

        </div>


        <div class="section-content">

            <?php

            echo format_text(
                $achievements
            );

            ?>

        </div>

    </div>

    <?php endif; ?>


    <!-- =================================================
         INTERESTS
    ================================================= -->

    <?php if (!empty($interests)): ?>

    <div class="section">

        <div class="section-title">

            Interests

        </div>


        <div class="section-content">

            <?php

            echo e($interests);

            ?>

        </div>

    </div>

    <?php endif; ?>


</div>


</body>

</html>