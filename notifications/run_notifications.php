<?php
include("notification_engine.php");

$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));

/* =========================
   GET STUDENTS
========================= */
$students = $conn->query("
    SELECT id, name, phone
    FROM students
    WHERE phone IS NOT NULL AND phone != ''
");

/* =========================
   GET EXAMS
========================= */
$exams = $conn->query("
    SELECT * FROM faculty_calendar
    WHERE event_type='Exam'
    AND event_date IN ('$today', '$tomorrow')
");

/* =========================
   SEND EXAM ALERTS
========================= */
while ($exam = $exams->fetch_assoc()) {

    $examMsg = "📢 *Exam Reminder*\n\n"
             . "📘 Subject: " . $exam['event_title'] . "\n"
             . "📅 Date: " . $exam['event_date'] . "\n\n"
             . "Prepare well! 🎓";

    mysqli_data_seek($students, 0);

    while ($student = $students->fetch_assoc()) {

        $response = sendWhatsApp(
            $student['phone'],
            $examMsg,
            $ACCESS_TOKEN,
            $PHONE_NUMBER_ID
        );

        /* =========================
           LOG SAVE
        ========================= */
        $status = (strpos($response, 'error') !== false) ? 'failed' : 'sent';

        $sid = $student['id'];
        $conn->query("
            INSERT INTO notifications_log
            (student_id, type, message, status)
            VALUES
            ('$sid', 'exam', '$examMsg', '$status')
        ");
    }
}

/* =========================
   HOLIDAY ALERT (OPTIONAL)
========================= */
$holidays = $conn->query("
    SELECT * FROM faculty_calendar
    WHERE event_type='Holiday'
    AND event_date='$today'
");

while ($h = $holidays->fetch_assoc()) {

    $msg = "🎉 Holiday Alert: " . $h['event_title'] . " today!";

    mysqli_data_seek($students, 0);

    while ($student = $students->fetch_assoc()) {

        sendWhatsApp($student['phone'], $msg, $ACCESS_TOKEN, $PHONE_NUMBER_ID);
    }
}

echo "Notifications sent successfully!";
?>