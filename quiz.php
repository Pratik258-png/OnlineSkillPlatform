
<?php

session_start();

include 'includes/db.php';

if (!$conn) {
    die("Database connection failed!");
}

/* ==========================================
   LOGIN CHECK
========================================== */

if (!isset($_SESSION['user_id'])) {
    die("Please login first!");
}

/* ==========================================
   GET COURSE ID
========================================== */

$course_id = 0;

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
} elseif (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
} elseif (isset($_SESSION['current_course_id'])) {
    $course_id = intval($_SESSION['current_course_id']);
}

if ($course_id <= 0) {
    die("Course not selected!");
}

/* ==========================================
   GET COURSE
========================================== */

$stmt = $conn->prepare("
    SELECT course_name
    FROM courses
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $course_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found!");
}

$course = $result->fetch_assoc();

$course_name = $course['course_name'];

$_SESSION['current_course_id'] = $course_id;

/* ==========================================
   COURSE TYPE
========================================== */

$course_lower = strtolower(trim($course_name));

$quiz_title = $course_name . " Quiz";

$question_bank = [];

/* ==========================================
   PYTHON QUESTION BANK
========================================== */

if (strpos($course_lower, 'python') !== false) {

    $quiz_title = "Python Quiz";

    $question_bank = [

        [
            'question' => 'Which keyword is used to define a function in Python?',
            'options' => ['def', 'function', 'fun', 'define'],
            'answer' => 'def',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which symbol is used to write a single-line comment in Python?',
            'options' => ['#', '//', '/*', '--'],
            'answer' => '#',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which data type stores True or False values in Python?',
            'options' => ['bool', 'int', 'str', 'float'],
            'answer' => 'bool',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which function is used to display output in Python?',
            'options' => ['print()', 'display()', 'show()', 'output()'],
            'answer' => 'print()',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which extension is normally used for Python files?',
            'options' => ['.py', '.python', '.pt', '.pyt'],
            'answer' => '.py',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which keyword is used to create a loop that repeats while a condition is true?',
            'options' => ['while', 'repeat', 'loop', 'during'],
            'answer' => 'while',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which collection stores values in an ordered and changeable sequence?',
            'options' => ['List', 'Tuple', 'Set', 'Dictionary'],
            'answer' => 'List',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which function returns the number of items in a Python collection?',
            'options' => ['len()', 'count()', 'size()', 'length()'],
            'answer' => 'len()',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which operator is used for exponentiation in Python?',
            'options' => ['**', '^', '//', '%%'],
            'answer' => '**',
            'skill' => 'Python Basics'
        ],

        [
            'question' => 'Which keyword is used to import a module in Python?',
            'options' => ['import', 'include', 'require', 'using'],
            'answer' => 'import',
            'skill' => 'Python Basics'
        ]

    ];
}

/* ==========================================
   WEB DEVELOPMENT
========================================== */

elseif (
    strpos($course_lower, 'web') !== false ||
    strpos($course_lower, 'html') !== false
) {

    $quiz_title = "Web Development Quiz";

    $question_bank = [

        [
            'question' => 'Which language is used to create the structure of a web page?',
            'options' => ['HTML', 'Java', 'Python', 'SQL'],
            'answer' => 'HTML',
            'skill' => 'HTML'
        ],

        [
            'question' => 'Which language is used to style a web page?',
            'options' => ['CSS', 'PHP', 'SQL', 'Java'],
            'answer' => 'CSS',
            'skill' => 'CSS'
        ],

        [
            'question' => 'Which language is mainly used to add interactivity to web pages?',
            'options' => ['JavaScript', 'HTML', 'CSS', 'SQL'],
            'answer' => 'JavaScript',
            'skill' => 'JavaScript'
        ],

        [
            'question' => 'Which HTML tag is used for the largest heading?',
            'options' => ['<h1>', '<h6>', '<head>', '<title>'],
            'answer' => '<h1>',
            'skill' => 'HTML'
        ],

        [
            'question' => 'Which HTML tag is used to create a paragraph?',
            'options' => ['<p>', '<div>', '<br>', '<para>'],
            'answer' => '<p>',
            'skill' => 'HTML'
        ],

        [
            'question' => 'Which CSS property is used to change text color?',
            'options' => ['color', 'font-color', 'text-color', 'foreground'],
            'answer' => 'color',
            'skill' => 'CSS'
        ],

        [
            'question' => 'Which CSS property is used to change the background color?',
            'options' => ['background-color', 'bgcolor', 'background', 'color'],
            'answer' => 'background-color',
            'skill' => 'CSS'
        ],

        [
            'question' => 'Which JavaScript keyword declares a block-scoped variable?',
            'options' => ['let', 'var', 'define', 'dim'],
            'answer' => 'let',
            'skill' => 'JavaScript'
        ],

        [
            'question' => 'Which HTML attribute specifies the destination of a link?',
            'options' => ['href', 'src', 'link', 'url'],
            'answer' => 'href',
            'skill' => 'HTML'
        ],

        [
            'question' => 'Which JavaScript function displays a message box?',
            'options' => ['alert()', 'message()', 'popup()', 'show()'],
            'answer' => 'alert()',
            'skill' => 'JavaScript'
        ]

    ];
}

/* ==========================================
   DATA STRUCTURES
========================================== */

elseif (
    strpos($course_lower, 'data structure') !== false ||
    strpos($course_lower, 'datastructure') !== false
) {

    $quiz_title = "Data Structure Quiz";

    $question_bank = [

        [
            'question' => 'Which data structure follows LIFO?',
            'options' => ['Stack', 'Queue', 'Array', 'Tree'],
            'answer' => 'Stack',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure follows FIFO?',
            'options' => ['Queue', 'Stack', 'Tree', 'Graph'],
            'answer' => 'Queue',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure stores elements in contiguous memory locations?',
            'options' => ['Array', 'Linked List', 'Graph', 'Tree'],
            'answer' => 'Array',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure consists of nodes connected by links?',
            'options' => ['Linked List', 'Array', 'Stack', 'Queue'],
            'answer' => 'Linked List',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure has a root node and child nodes?',
            'options' => ['Tree', 'Queue', 'Stack', 'Array'],
            'answer' => 'Tree',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure is commonly used for implementing recursion?',
            'options' => ['Stack', 'Queue', 'Array', 'Graph'],
            'answer' => 'Stack',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure is commonly used in breadth-first search?',
            'options' => ['Queue', 'Stack', 'Tree', 'Array'],
            'answer' => 'Queue',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which data structure stores key-value pairs?',
            'options' => ['Hash Table', 'Stack', 'Queue', 'Linked List'],
            'answer' => 'Hash Table',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which structure represents relationships between vertices and edges?',
            'options' => ['Graph', 'Stack', 'Queue', 'Array'],
            'answer' => 'Graph',
            'skill' => 'Data Structures'
        ],

        [
            'question' => 'Which linked list allows traversal in both directions?',
            'options' => ['Doubly Linked List', 'Singly Linked List', 'Circular Queue', 'Stack'],
            'answer' => 'Doubly Linked List',
            'skill' => 'Data Structures'
        ]

    ];
}

/* ==========================================
   JAVA
========================================== */

elseif (strpos($course_lower, 'java') !== false) {

    $quiz_title = "Java Quiz";

    $question_bank = [

        [
            'question' => 'Which keyword is used to create a class in Java?',
            'options' => ['class', 'define', 'struct', 'object'],
            'answer' => 'class',
            'skill' => 'Java Basics'
        ],

        [
            'question' => 'Which concept allows a class to have many forms?',
            'options' => ['Polymorphism', 'Compilation', 'Iteration', 'Abstraction'],
            'answer' => 'Polymorphism',
            'skill' => 'OOP'
        ],

        [
            'question' => 'Which keyword is used to inherit a class in Java?',
            'options' => ['extends', 'inherits', 'implements', 'super'],
            'answer' => 'extends',
            'skill' => 'OOP'
        ],

        [
            'question' => 'Which method is the entry point of a Java application?',
            'options' => ['main()', 'start()', 'run()', 'execute()'],
            'answer' => 'main()',
            'skill' => 'Java Basics'
        ],

        [
            'question' => 'Which collection does not allow duplicate elements?',
            'options' => ['Set', 'List', 'ArrayList', 'Vector'],
            'answer' => 'Set',
            'skill' => 'Collections'
        ],

        [
            'question' => 'Which keyword is used to create an object in Java?',
            'options' => ['new', 'create', 'object', 'make'],
            'answer' => 'new',
            'skill' => 'Java Basics'
        ],

        [
            'question' => 'Which OOP concept hides internal implementation details?',
            'options' => ['Abstraction', 'Inheritance', 'Polymorphism', 'Compilation'],
            'answer' => 'Abstraction',
            'skill' => 'OOP'
        ],

        [
            'question' => 'Which keyword is used to prevent a method from being overridden?',
            'options' => ['final', 'static', 'private', 'constant'],
            'answer' => 'final',
            'skill' => 'OOP'
        ],

        [
            'question' => 'Which collection maintains insertion order and allows duplicates?',
            'options' => ['List', 'Set', 'Map', 'TreeSet'],
            'answer' => 'List',
            'skill' => 'Collections'
        ],

        [
            'question' => 'Which interface is commonly used to store key-value pairs?',
            'options' => ['Map', 'List', 'Set', 'Queue'],
            'answer' => 'Map',
            'skill' => 'Collections'
        ]

    ];
}

/* ==========================================
   MYSQL / SQL
========================================== */

elseif (
    strpos($course_lower, 'mysql') !== false ||
    strpos($course_lower, 'sql') !== false
) {

    $quiz_title = "MySQL / SQL Quiz";

    $question_bank = [

        [
            'question' => 'Which command is used to retrieve data from a table?',
            'options' => ['SELECT', 'GET', 'FETCH', 'READ'],
            'answer' => 'SELECT',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which command is used to add new records?',
            'options' => ['INSERT', 'ADD', 'CREATE', 'APPEND'],
            'answer' => 'INSERT',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which clause is used to filter records?',
            'options' => ['WHERE', 'FILTER', 'SEARCH', 'HAVING'],
            'answer' => 'WHERE',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which command modifies existing records?',
            'options' => ['UPDATE', 'CHANGE', 'MODIFY', 'ALTER'],
            'answer' => 'UPDATE',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which command removes records from a table?',
            'options' => ['DELETE', 'REMOVE', 'DROP', 'CLEAR'],
            'answer' => 'DELETE',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which command is used to create a new table?',
            'options' => ['CREATE TABLE', 'NEW TABLE', 'MAKE TABLE', 'ADD TABLE'],
            'answer' => 'CREATE TABLE',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which keyword removes duplicate rows from SELECT results?',
            'options' => ['DISTINCT', 'UNIQUE', 'REMOVE', 'FILTER'],
            'answer' => 'DISTINCT',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which clause is used to sort query results?',
            'options' => ['ORDER BY', 'SORT BY', 'GROUP BY', 'ARRANGE BY'],
            'answer' => 'ORDER BY',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which key uniquely identifies each row in a table?',
            'options' => ['Primary Key', 'Foreign Key', 'Candidate Key', 'Index'],
            'answer' => 'Primary Key',
            'skill' => 'MySQL'
        ],

        [
            'question' => 'Which clause groups rows having the same values?',
            'options' => ['GROUP BY', 'ORDER BY', 'WHERE', 'JOIN'],
            'answer' => 'GROUP BY',
            'skill' => 'MySQL'
        ]

    ];
}

/* ==========================================
   DATA SCIENCE
========================================== */

elseif (strpos($course_lower, 'data science') !== false) {

    $quiz_title = "Data Science Quiz";

    $question_bank = [

        [
            'question' => 'Which Python library is mainly used for data manipulation?',
            'options' => ['Pandas', 'Flask', 'Django', 'Tkinter'],
            'answer' => 'Pandas',
            'skill' => 'Pandas'
        ],

        [
            'question' => 'Which field deals with collecting and analyzing numerical data?',
            'options' => ['Statistics', 'HTML', 'Networking', 'Operating Systems'],
            'answer' => 'Statistics',
            'skill' => 'Statistics'
        ],

        [
            'question' => 'Which technique allows a computer to learn patterns from data?',
            'options' => ['Machine Learning', 'HTML', 'Database Backup', 'Web Hosting'],
            'answer' => 'Machine Learning',
            'skill' => 'Machine Learning'
        ],

        [
            'question' => 'Which library is commonly used to create charts in Python?',
            'options' => ['Matplotlib', 'Flask', 'Requests', 'BeautifulSoup'],
            'answer' => 'Matplotlib',
            'skill' => 'Data Visualization'
        ],

        [
            'question' => 'Which technology is used for neural-network based learning?',
            'options' => ['Deep Learning', 'HTML', 'CSS', 'SQL'],
            'answer' => 'Deep Learning',
            'skill' => 'Deep Learning'
        ],

        [
            'question' => 'Which library is commonly used for numerical computing in Python?',
            'options' => ['NumPy', 'Flask', 'Django', 'Requests'],
            'answer' => 'NumPy',
            'skill' => 'Data Science'
        ],

        [
            'question' => 'Which measure represents the middle value of ordered data?',
            'options' => ['Median', 'Mean', 'Mode', 'Range'],
            'answer' => 'Median',
            'skill' => 'Statistics'
        ],

        [
            'question' => 'Which method is used to handle missing values by replacing them with a value?',
            'options' => ['Imputation', 'Compilation', 'Encryption', 'Rendering'],
            'answer' => 'Imputation',
            'skill' => 'Data Preprocessing'
        ],

        [
            'question' => 'Which chart is commonly useful for showing the distribution of numerical data?',
            'options' => ['Histogram', 'Pie chart', 'Logo', 'Table'],
            'answer' => 'Histogram',
            'skill' => 'Data Visualization'
        ],

        [
            'question' => 'Which type of machine learning uses labeled training data?',
            'options' => ['Supervised Learning', 'Unsupervised Learning', 'Reinforcement Learning', 'Random Learning'],
            'answer' => 'Supervised Learning',
            'skill' => 'Machine Learning'
        ]

    ];
}

/* ==========================================
   OTHER COURSE
========================================== */

else {

    $quiz_title = $course_name . " Quiz";

    $question_bank = [

        [
            'question' => 'What is the main purpose of this course?',
            'options' => ['Learning', 'Gaming', 'Entertainment', 'Shopping'],
            'answer' => 'Learning',
            'skill' => $course_name
        ],

        [
            'question' => 'What helps students understand a topic better?',
            'options' => ['Practice', 'Ignoring', 'Skipping', 'Avoiding'],
            'answer' => 'Practice',
            'skill' => $course_name
        ],

        [
            'question' => 'What should students do after learning a concept?',
            'options' => ['Practice it', 'Forget it', 'Skip it', 'Ignore it'],
            'answer' => 'Practice it',
            'skill' => $course_name
        ],

        [
            'question' => 'Which activity improves learning?',
            'options' => ['Regular revision', 'Never practicing', 'Ignoring concepts', 'Skipping lessons'],
            'answer' => 'Regular revision',
            'skill' => $course_name
        ],

        [
            'question' => 'What is important for becoming skilled?',
            'options' => ['Practice and learning', 'Skipping lessons', 'Avoiding practice', 'Ignoring feedback'],
            'answer' => 'Practice and learning',
            'skill' => $course_name
        ],

        [
            'question' => 'Which approach helps improve performance?',
            'options' => ['Regular practice', 'No practice', 'Skipping tasks', 'Avoiding learning'],
            'answer' => 'Regular practice',
            'skill' => $course_name
        ],

        [
            'question' => 'Why is assessment useful in learning?',
            'options' => ['It measures understanding', 'It stops learning', 'It removes practice', 'It avoids feedback'],
            'answer' => 'It measures understanding',
            'skill' => $course_name
        ],

        [
            'question' => 'Which habit supports continuous learning?',
            'options' => ['Regular revision', 'Ignoring mistakes', 'Skipping topics', 'Avoiding practice'],
            'answer' => 'Regular revision',
            'skill' => $course_name
        ]

    ];
}

/* ==========================================
   SAFETY CHECK
========================================== */

if (empty($question_bank)) {
    die("Quiz questions are not available for this course.");
}

/* ==========================================
   RANDOM QUESTION SELECTION
========================================== */

/*
   We have a larger question bank.
   Only 5 questions will be shown in each attempt.
*/

shuffle($question_bank);

$questions = array_slice($question_bank, 0, min(5, count($question_bank)));

/* ==========================================
   RANDOMIZE OPTIONS
========================================== */

foreach ($questions as &$question) {
    shuffle($question['options']);
}
unset($question);

/* ==========================================
   SAVE QUIZ DATA IN SESSION
========================================== */

$_SESSION['quiz_course_id'] = $course_id;

$_SESSION['quiz_answers'] = [];

$_SESSION['quiz_skill_map'] = [];

foreach ($questions as $index => $question) {

    $q_number = 'q' . ($index + 1);

    $_SESSION['quiz_answers'][$q_number] = $question['answer'];

    $_SESSION['quiz_skill_map'][$q_number] = $question['skill'];
}

/* ==========================================
   HTML
========================================== */
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($quiz_title); ?></title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    font-family: Arial, sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(135deg, #0f172a, #1e1b4b, #312e81);

    color: white;

    padding: 30px 15px;

}

.container {

    max-width: 850px;

    margin: auto;

}

.header {

    text-align: center;

    margin-bottom: 30px;

}

.header h1 {

    font-size: 34px;

    margin-bottom: 10px;

}

.header p {

    color: #cbd5e1;

}

.info {

    text-align: center;

    margin-bottom: 20px;

    color: #c7d2fe;

    font-size: 14px;

}

.quiz-card {

    background: rgba(255,255,255,0.08);

    border: 1px solid rgba(255,255,255,0.15);

    border-radius: 20px;

    padding: 25px;

    margin-bottom: 20px;

    backdrop-filter: blur(10px);

    box-shadow: 0 10px 30px rgba(0,0,0,0.25);

}

.question {

    font-size: 19px;

    font-weight: bold;

    margin-bottom: 18px;

}

.option {

    display: block;

    background: rgba(255,255,255,0.08);

    padding: 14px;

    border-radius: 10px;

    margin: 10px 0;

    cursor: pointer;

    border: 1px solid transparent;

    transition: 0.3s;

}

.option:hover {

    background: rgba(255,255,255,0.16);

    border-color: #818cf8;

}

.option input {

    margin-right: 10px;

}

.submit-btn {

    width: 100%;

    padding: 16px;

    border: none;

    border-radius: 12px;

    background: #6366f1;

    color: white;

    font-size: 18px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

}

.submit-btn:hover {

    background: #4f46e5;

    transform: translateY(-2px);

}

.badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    background: rgba(99,102,241,0.3);

    color: #c7d2fe;

    font-size: 13px;

    margin-top: 8px;

}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>🧠 <?php echo htmlspecialchars($quiz_title); ?></h1>

        <p>Test your knowledge and build your skill profile</p>

    </div>

    <div class="info">
        🎲 Randomized Quiz • 5 Questions • Questions & Options are shuffled
    </div>

    <form method="POST" action="quiz_result.php">

        <input
            type="hidden"
            name="course_id"
            value="<?php echo $course_id; ?>"
        >

        <?php foreach ($questions as $index => $question): ?>

            <?php $q_number = 'q' . ($index + 1); ?>

            <div class="quiz-card">

                <div class="question">

                    <?php
                    echo ($index + 1) . ". " .
                         htmlspecialchars($question['question']);
                    ?>

                </div>

                <span class="badge">
                    Skill: <?php echo htmlspecialchars($question['skill']); ?>
                </span>

                <?php foreach ($question['options'] as $option): ?>

                    <label class="option">

                        <input
                            type="radio"
                            name="<?php echo $q_number; ?>"
                            value="<?php echo htmlspecialchars($option); ?>"
                            required
                        >

                        <?php echo htmlspecialchars($option); ?>

                    </label>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

        <button type="submit" class="submit-btn">
            Submit Quiz 🚀
        </button>

    </form>

</div>

</body>

</html>
