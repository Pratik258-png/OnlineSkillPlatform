<?php

session_start();

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Student';


/* =========================================================
   GEMINI API KEY
========================================================= */

$GEMINI_API_KEY = "gemini api key";


/* =========================================================
   ONLY ONE MODEL
========================================================= */

$MODEL = "gemini-3.5-flash-lite";


/* =========================================================
   VARIABLES
========================================================= */

$notes = "";
$error = "";
$youtubeLink = "";


/* =========================================================
   GENERATE NOTES
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $youtubeLink = trim(
        $_POST["youtube_link"] ?? ""
    );


    /* =====================================================
       CHECK EMPTY
    ===================================================== */

    if ($youtubeLink === "") {

        $error = "Please enter a YouTube link.";

    }


    /* =====================================================
       CHECK API KEY
    ===================================================== */

    elseif (
        $GEMINI_API_KEY === "" ||
        $GEMINI_API_KEY === "YOUR_API_KEY_HERE"
    ) {

        $error =
            "Gemini API key is not configured.";

    }


    /* =====================================================
       CHECK YOUTUBE URL
    ===================================================== */

    elseif (
        !preg_match(
            '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\//i',
            $youtubeLink
        )
    ) {

        $error =
            "Please enter a valid public YouTube URL.";

    }


    else {

        /* =================================================
           PROMPT
        ================================================= */

        $prompt = <<<PROMPT

You are an expert college teacher and exam preparation assistant.

Analyze the provided public YouTube educational video.

Create simple, accurate, exam-oriented study material.

IMPORTANT:
Use information from the video.
Do not invent unrelated information.

Give the answer in this structure:

==================================================
📚 IMPORTANT NOTES
==================================================

1. TOPIC OVERVIEW

Explain the main topic of the video in simple English.

2. MOST IMPORTANT CONCEPTS

Give the important concepts explained in the video.

For each concept give:
- Concept name
- Simple explanation
- Important point

3. IMPORTANT DEFINITIONS

Give important definitions from the video.

4. MOST IMPORTANT POINTS

Give 7 to 10 important exam points.

5. REAL LIFE EXAMPLE

Give a simple real-life example if the video explains one.
If no example is present, say:
"No specific real-life example was clearly explained in the video."

==================================================
📝 IMPORTANT QUESTIONS AND ANSWERS
==================================================

Create exactly 7 important exam questions.

EVERY QUESTION MUST HAVE A COMPLETE ANSWER.

Use this format:

Q1. Question

Answer:
Complete answer.

Q2. Question

Answer:
Complete answer.

Continue until Q7.

==================================================
⚡ QUICK REVISION
==================================================

Give 10 short revision points.

==================================================
🔑 KEYWORDS
==================================================

Give the important keywords.

==================================================
🎯 EXAM FOCUS
==================================================

Give:

3 most important topics:
1.
2.
3.

3 important definitions:
1.
2.
3.

3 important exam questions:
1.
2.
3.

RULES:

- Use simple English.
- Keep answers short and useful.
- Do not leave answers blank.
- Do not repeat information unnecessarily.
- Focus on the educational content.
- Make the output easy for a college student to study.

PROMPT;


        /* =================================================
           GEMINI API URL
        ================================================= */

        $apiUrl =
            "https://generativelanguage.googleapis.com/v1beta/models/"
            . $MODEL .
            ":generateContent";


        /* =================================================
           REQUEST DATA
        ================================================= */

        $requestData = [

            "contents" => [

                [

                    "role" => "user",

                    "parts" => [

                        [
                            "text" => $prompt
                        ],

                        [
                            "file_data" => [

                                "mime_type" => "video/*",

                                "file_uri" => $youtubeLink

                            ]
                        ]

                    ]

                ]

            ]

        ];


        /* =================================================
           JSON
        ================================================= */

        $jsonData = json_encode(
            $requestData,
            JSON_UNESCAPED_UNICODE
        );


        if ($jsonData === false) {

            $error =
                "Unable to create API request.";

        }


        else {

            /* =============================================
               CURL
            ============================================= */

            $ch = curl_init($apiUrl);


            curl_setopt_array(

                $ch,

                [

                    CURLOPT_RETURNTRANSFER => true,

                    CURLOPT_POST => true,

                    CURLOPT_HTTPHEADER => [

                        "Content-Type: application/json",

                        "x-goog-api-key: " .
                        $GEMINI_API_KEY

                    ],

                    CURLOPT_POSTFIELDS =>
                        $jsonData,

                    CURLOPT_CONNECTTIMEOUT =>
                        15,

                    CURLOPT_TIMEOUT =>
                        75

                ]

            );


            /* =============================================
               SEND REQUEST
            ============================================= */

            $response =
                curl_exec($ch);


            $curlError =
                curl_error($ch);


            $httpCode =
                curl_getinfo(
                    $ch,
                    CURLINFO_HTTP_CODE
                );


            curl_close($ch);


            /* =============================================
               CONNECTION ERROR
            ============================================= */

            if ($curlError !== "") {

                $error =
                    "Connection error: " .
                    $curlError;

            }


            else {

                /* =========================================
                   DECODE RESPONSE
                ========================================= */

                $data =
                    json_decode(
                        $response,
                        true
                    );


                /* =========================================
                   INVALID JSON
                ========================================= */

                if (!is_array($data)) {

                    $error =
                        "Invalid response received from Gemini.";

                }


                /* =========================================
                   API ERROR
                ========================================= */

                elseif (isset($data["error"])) {

                    $apiMessage =
                        $data["error"]["message"]
                        ?? "Unknown Gemini API error";


                    /* =====================================
                       QUOTA ERROR
                    ===================================== */

                    if (
                        $httpCode == 429 ||
                        stripos(
                            $apiMessage,
                            "quota"
                        ) !== false ||
                        stripos(
                            $apiMessage,
                            "limit"
                        ) !== false
                    ) {

                        $error =
                            "❌ Gemini quota reached for "
                            . $MODEL
                            . ". Please wait for the quota "
                            . "to reset or enable billing.";

                    }


                    /* =====================================
                       SERVER BUSY
                    ===================================== */

                    elseif (
                        $httpCode == 500 ||
                        $httpCode == 502 ||
                        $httpCode == 503
                    ) {

                        $error =
                            "❌ Gemini server is temporarily busy. "
                            . "Please try again after a short time.";

                    }


                    /* =====================================
                       OTHER ERROR
                    ===================================== */

                    else {

                        $error =
                            "❌ Gemini API Error: "
                            . $apiMessage;

                    }

                }


                else {

                    /* =====================================
                       GET GENERATED TEXT
                    ===================================== */

                    $generatedText = "";


                    if (
                        isset(
                            $data["candidates"][0]
                            ["content"]
                            ["parts"]
                        )
                    ) {

                        foreach (

                            $data["candidates"][0]
                            ["content"]
                            ["parts"]

                            as $part

                        ) {

                            if (
                                isset($part["text"])
                            ) {

                                $generatedText .=
                                    $part["text"] .
                                    "\n";

                            }

                        }

                    }


                    /* =====================================
                       SUCCESS
                    ===================================== */

                    if (
                        trim($generatedText) !== ""
                    ) {

                        $notes =
                            trim($generatedText);

                        $error = "";

                    }


                    /* =====================================
                       EMPTY RESPONSE
                    ===================================== */

                    else {

                        $error =
                            "❌ Gemini returned an empty response.";

                    }

                }

            }

        }

    }

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
AI Important Notes
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {

    box-sizing:
        border-box;

}


/* =====================================================
   BODY
===================================================== */

body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: white;

    background:

        radial-gradient(
            circle at 10% 10%,
            rgba(124,58,237,.25),
            transparent 30%
        ),

        radial-gradient(
            circle at 90% 90%,
            rgba(6,182,212,.20),
            transparent 30%
        ),

        linear-gradient(
            135deg,
            #020617,
            #0f172a,
            #1e1b4b
        );

    padding:
        40px 20px;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 100%;

    max-width:
        1100px;

    margin:
        auto;

}


/* =====================================================
   HEADER
===================================================== */

.header {

    text-align:
        center;

    margin-bottom:
        30px;

}


.header h1 {

    color:
        #00ffd5;

    font-size:
        38px;

    margin-bottom:
        10px;

}


.header p {

    color:
        #94a3b8;

    line-height:
        1.6;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background:
        rgba(15,23,42,.95);

    border:
        1px solid
        rgba(255,255,255,.10);

    border-radius:
        22px;

    padding:
        30px;

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.45);

}


/* =====================================================
   LABEL
===================================================== */

label {

    display:
        block;

    margin-bottom:
        10px;

    font-weight:
        bold;

    color:
        #cbd5e1;

}


/* =====================================================
   INPUT
===================================================== */

input {

    width:
        100%;

    padding:
        15px;

    border:
        none;

    outline:
        none;

    border-radius:
        12px;

    font-size:
        16px;

    background:
        #e2e8f0;

    color:
        #0f172a;

}


/* =====================================================
   GENERATE BUTTON
===================================================== */

.generate {

    margin-top:
        20px;

    padding:
        14px 28px;

    border:
        none;

    border-radius:
        50px;

    background:

        linear-gradient(
            135deg,
            #7c3aed,
            #2563eb,
            #06b6d4
        );

    color:
        white;

    font-size:
        16px;

    font-weight:
        bold;

    cursor:
        pointer;

    transition:
        .3s;

}


.generate:hover {

    transform:
        scale(1.04);

}


.generate:disabled {

    opacity:
        .7;

    cursor:
        wait;

}


/* =====================================================
   ERROR
===================================================== */

.error {

    margin-top:
        25px;

    padding:
        15px;

    border-radius:
        12px;

    background:
        rgba(239,68,68,.15);

    border:
        1px solid
        rgba(239,68,68,.4);

    color:
        #f87171;

    line-height:
        1.6;

}


/* =====================================================
   SUCCESS
===================================================== */

.success {

    margin-top:
        30px;

}


.success-title {

    color:
        #00ffd5;

    font-size:
        24px;

    font-weight:
        bold;

    margin-bottom:
        15px;

}


/* =====================================================
   NOTES
===================================================== */

.notes {

    background:
        #f8fafc;

    color:
        #0f172a;

    padding:
        35px;

    border-radius:
        15px;

    line-height:
        1.7;

    white-space:
        pre-wrap;

    font-size:
        16px;

    max-height:
        750px;

    overflow-y:
        auto;

}


/* =====================================================
   ACTIONS
===================================================== */

.actions {

    margin-top:
        20px;

    display:
        flex;

    gap:
        15px;

    flex-wrap:
        wrap;

}


.action {

    display:
        inline-block;

    padding:
        12px 22px;

    border-radius:
        30px;

    text-decoration:
        none;

    color:
        white;

    font-weight:
        bold;

    border:
        none;

    cursor:
        pointer;

}


/* =====================================================
   PDF
===================================================== */

.pdf {

    background:

        linear-gradient(
            135deg,
            #dc2626,
            #ef4444
        );

}


/* =====================================================
   PRINT
===================================================== */

.print {

    background:

        linear-gradient(
            135deg,
            #059669,
            #10b981
        );

}


/* =====================================================
   BACK
===================================================== */

.back {

    background:

        linear-gradient(
            135deg,
            #475569,
            #64748b
        );

}


/* =====================================================
   LOADING
===================================================== */

.loading {

    margin-top:
        25px;

    padding:
        20px;

    text-align:
        center;

    border-radius:
        15px;

    background:
        rgba(6,182,212,.10);

    color:
        #00ffd5;

    font-weight:
        bold;

    line-height:
        1.8;

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:600px) {

    .header h1 {

        font-size:
            28px;

    }

    .card {

        padding:
            20px;

    }

    .notes {

        padding:
            20px;

        font-size:
            14px;

    }

}

</style>

</head>


<body>


<div class="container">


<!-- ===================================================
     HEADER
=================================================== -->

<div class="header">

    <h1>
        🤖 AI Important Notes
    </h1>


    <p>

        Welcome

        <b>

            <?php

            echo htmlspecialchars($name);

            ?>

        </b>

    </p>


    <p>

        Paste a public YouTube educational video
        and generate important notes,
        questions and answers.

    </p>

</div>


<!-- ===================================================
     CARD
=================================================== -->

<div class="card">


<form
method="POST"
onsubmit="showLoading()"
>


<label>

    🎥 YouTube Study Video

</label>


<input
    type="text"
    name="youtube_link"
    placeholder="https://www.youtube.com/watch?v=..."
    value="<?php

        echo htmlspecialchars(
            $youtubeLink
        );

    ?>"
    required
>


<button
    type="submit"
    class="generate"
    id="generateButton"
>

    🤖 Generate Important Notes

</button>


</form>


<!-- ===================================================
     LOADING
=================================================== -->

<div
    id="loading"
    class="loading"
    style="display:none;"
>

    ⏳ AI is analyzing the YouTube video...

    <br>

    📚 Preparing important notes and answers...

    <br>

    Please wait.

</div>


<!-- ===================================================
     ERROR
=================================================== -->

<?php

if ($error !== ""):

?>

<div class="error">

    <?php

    echo htmlspecialchars(
        $error
    );

    ?>

</div>

<?php

endif;

?>


<!-- ===================================================
     NOTES
=================================================== -->

<?php

if ($notes !== ""):

?>

<div class="success">


<div class="success-title">

    ✅ Important Notes Generated

</div>


<div
    id="notesContent"
    class="notes"
><?php

echo htmlspecialchars(
    $notes
);

?></div>


<!-- =================================================
     ACTIONS
================================================= -->

<div class="actions">


<button
    onclick="downloadPDF()"
    class="action pdf"
>

    📄 Download / Save PDF

</button>


<button
    onclick="printNotes()"
    class="action print"
>

    🖨️ Print Notes

</button>


<a
    href="dashbord.php"
    class="action back"
>

    ← Dashboard

</a>


</div>


</div>

<?php

endif;

?>


</div>

</div>


<script>


/* =====================================================
   LOADING
===================================================== */

function showLoading() {

    const loading =
        document.getElementById(
            "loading"
        );


    const button =
        document.getElementById(
            "generateButton"
        );


    if (loading) {

        loading.style.display =
            "block";

    }


    if (button) {

        button.disabled =
            true;

        button.innerHTML =
            "⏳ Generating...";

    }

}


/* =====================================================
   PDF
===================================================== */

function downloadPDF() {

    const element =
        document.getElementById(
            "notesContent"
        );


    if (!element) {

        alert(
            "Notes not available."
        );

        return;

    }


    const content =
        element.innerText;


    const win =
        window.open(
            "",
            "_blank"
        );


    if (!win) {

        alert(
            "Please allow pop-ups in your browser."
        );

        return;

    }


    win.document.write(`

<!DOCTYPE html>

<html>

<head>

<title>
AI Important Notes
</title>

<style>

body {

    font-family:
        Arial,
        sans-serif;

    padding:
        40px;

    color:
        #111;

    line-height:
        1.7;

}

h1 {

    text-align:
        center;

    margin-bottom:
        20px;

}

pre {

    white-space:
        pre-wrap;

    font-family:
        Arial,
        sans-serif;

    font-size:
        14px;

}

</style>

</head>

<body>

<h1>
🤖 AI Important Notes
</h1>

<hr>

<pre>
${escapeHtml(content)}
</pre>

</body>

</html>

`);


    win.document.close();


    setTimeout(
        function() {

            win.print();

        },
        700
    );

}


/* =====================================================
   PRINT
===================================================== */

function printNotes() {

    const element =
        document.getElementById(
            "notesContent"
        );


    if (!element) {

        alert(
            "Notes not available."
        );

        return;

    }


    const content =
        element.innerText;


    const win =
        window.open(
            "",
            "_blank"
        );


    if (!win) {

        alert(
            "Please allow pop-ups in your browser."
        );

        return;

    }


    win.document.write(`

<!DOCTYPE html>

<html>

<head>

<title>
AI Important Notes
</title>

<style>

body {

    font-family:
        Arial,
        sans-serif;

    padding:
        40px;

    line-height:
        1.7;

}

pre {

    white-space:
        pre-wrap;

    font-family:
        Arial,
        sans-serif;

}

</style>

</head>

<body>

<h1>
🤖 AI Important Notes
</h1>

<hr>

<pre>
${escapeHtml(content)}
</pre>

</body>

</html>

`);


    win.document.close();


    setTimeout(
        function() {

            win.print();

        },
        500
    );

}


/* =====================================================
   ESCAPE HTML
===================================================== */

function escapeHtml(text) {

    return text

        .replace(
            /&/g,
            "&amp;"
        )

        .replace(
            /</g,
            "&lt;"
        )

        .replace(
            />/g,
            "&gt;"
        )

        .replace(
            /"/g,
            "&quot;"
        )

        .replace(
            /'/g,
            "&#039;"
        );

}

</script>


</body>

</html>