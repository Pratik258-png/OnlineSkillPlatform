<?php

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);

    exit;
}


/* =========================================
   CHECK REQUEST
========================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


/* =========================================
   GET YOUTUBE LINK
========================================= */

$youtube_link =
    trim($_POST['youtube_link'] ?? '');


if ($youtube_link === '') {

    echo json_encode([
        "success" => false,
        "message" => "YouTube link is required."
    ]);

    exit;
}


/* =========================================
   VALIDATE YOUTUBE URL
========================================= */

if (
    strpos($youtube_link, 'youtube.com') === false &&
    strpos($youtube_link, 'youtu.be') === false
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid YouTube URL."
    ]);

    exit;
}


/* =========================================
   CREATE NOTES FOLDER
========================================= */

$notes_folder =
    __DIR__ . "/uploads/notes/";


if (!is_dir($notes_folder)) {

    mkdir(
        $notes_folder,
        0777,
        true
    );
}


/* =========================================
   GET VIDEO ID
========================================= */

$video_id = "";


/* youtube.com/watch?v= */

if (strpos($youtube_link, 'v=') !== false) {

    parse_str(
        parse_url(
            $youtube_link,
            PHP_URL_QUERY
        ),
        $query
    );

    $video_id =
        $query['v'] ?? '';

}


/* youtu.be/ */

elseif (
    strpos(
        $youtube_link,
        'youtu.be/'
    ) !== false
) {

    $video_id =
        basename(
            parse_url(
                $youtube_link,
                PHP_URL_PATH
            )
        );

}


/* =========================================
   CHECK VIDEO ID
========================================= */

if ($video_id === '') {

    echo json_encode([
        "success" => false,
        "message" => "Could not identify YouTube video."
    ]);

    exit;
}


/* =========================================
   TEMPORARY IMPORTANT NOTES
=========================================

   IMPORTANT:

   At this stage we are creating the PDF
   structure.

   Actual YouTube transcript + AI processing
   will be connected after this step.
========================================= */


/* =========================================
   NOTES DATA
========================================= */

$student_name =
    $_SESSION['name'] ?? 'Student';

$date =
    date("d M Y");


$notes = [

    "Topic" =>
        "YouTube Study Video",

    "Video ID" =>
        $video_id,

    "Most Important Parts" => [

        "Understand the main concept explained in the video.",

        "Identify important definitions and terminology.",

        "Focus on examples used to explain the concept.",

        "Remember important formulas, rules or steps.",

        "Review concepts that are repeated or emphasized.",

        "Practice the examples discussed in the video."

    ],

    "Quick Revision Points" => [

        "Understand the basic definition.",

        "Remember the key concepts.",

        "Study important examples.",

        "Revise important points before examination."

    ]

];


/* =========================================
   CREATE HTML PDF CONTENT
========================================= */

$html = "

<!DOCTYPE html>

<html>

<head>

<meta charset='UTF-8'>

<style>

body {

    font-family: Arial, sans-serif;

    padding: 40px;

    color: #111827;

}

.header {

    text-align: center;

    border-bottom: 3px solid #2563eb;

    padding-bottom: 20px;

    margin-bottom: 25px;

}

.header h1 {

    color: #2563eb;

    margin-bottom: 5px;

}

.student {

    color: #475569;

}

.section {

    margin-top: 25px;

}

.section h2 {

    color: #7c3aed;

    border-bottom: 1px solid #ddd;

    padding-bottom: 8px;

}

ul {

    line-height: 1.8;

}

.important {

    background: #f1f5f9;

    padding: 18px;

    border-left: 5px solid #06b6d4;

}

.footer {

    margin-top: 50px;

    text-align: center;

    color: #64748b;

    font-size: 12px;

}

</style>

</head>

<body>


<div class='header'>

<h1>

OnlineSkill Platform

</h1>

<h2>

AI Important Notes

</h2>

<div class='student'>

Student:
<strong>
" . htmlspecialchars($student_name) . "
</strong>

<br>

Generated:
" . $date . "

</div>

</div>


<div class='section'>

<h2>

📚 Topic

</h2>

<div class='important'>

YouTube Study Video

</div>

</div>


<div class='section'>

<h2>

⭐ Most Important Parts

</h2>

<ul>
";


foreach (
    $notes['Most Important Parts']
    as $point
) {

    $html .=
        "<li>" .
        htmlspecialchars($point) .
        "</li>";

}


$html .= "

</ul>

</div>


<div class='section'>

<h2>

⚡ Quick Revision Points

</h2>

<ul>
";


foreach (
    $notes['Quick Revision Points']
    as $point
) {

    $html .=
        "<li>" .
        htmlspecialchars($point) .
        "</li>";

}


$html .= "

</ul>

</div>


<div class='section'>

<h2>

🎥 YouTube Video

</h2>

<p>

<a href='" .
htmlspecialchars($youtube_link) .
"'>

Watch Original Video

</a>

</p>

</div>


<div class='footer'>

Generated by OnlineSkill Platform

</div>


</body>

</html>
";


/* =========================================
   SAVE HTML FILE
========================================= */

$file_name =
    "AI_Notes_" .
    $video_id .
    "_" .
    time() .
    ".html";


$file_path =
    $notes_folder .
    $file_name;


if (
    file_put_contents(
        $file_path,
        $html
    ) === false
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Could not create notes file."
    ]);

    exit;
}


/* =========================================
   RETURN FILE
========================================= */

$pdf_url =
    "uploads/notes/" .
    $file_name;


echo json_encode([

    "success" => true,

    "message" =>
        "Important notes generated successfully.",

    "pdf" =>
        $pdf_url

]);

exit;

?>