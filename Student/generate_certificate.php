<?php
if(!isset($_SESSION)){
    session_start();
}
include_once('../dbConnection.php');

// التحقق من تسجيل الدخول
if(!isset($_SESSION['is_login'])){
    echo "<script>location.href='../index.php' </script>";
    exit();
}

$stuLogEmail = $_SESSION['stuLogEmail'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

// التحقق من صحة معرف الدورة والطالب
if(!$course_id || !$student_id) {
    echo "<script>location.href='myCourses.php' </script>";
    exit();
}

// التحقق من تسجيل الطالب في الدورة
$enrollment_check = "SELECT * FROM courseorder WHERE stu_email = '$stuLogEmail' AND course_id = $course_id";
$enrollment_result = $conn->query($enrollment_check);

if($enrollment_result->num_rows == 0) {
    echo "<script>alert('You are not enrolled in this course.'); location.href='myCourses.php' </script>";
    exit();
}

// الحصول على معلومات الدورة
$course_sql = "SELECT * FROM course WHERE course_id = $course_id";
$course_result = $conn->query($course_sql);
$course = $course_result->fetch_assoc();

// الحصول على معلومات الطالب
$student_sql = "SELECT * FROM students WHERE stu_id = $student_id";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();

// التحقق من اجتياز الاختبار النهائي
$final_exam_sql = "SELECT q.quiz_id, q.passing_score, qr.score 
                   FROM quizzes q 
                   JOIN quiz_results qr ON q.quiz_id = qr.quiz_id 
                   WHERE q.course_id = $course_id AND q.quiz_title LIKE '%Final Exam%' 
                   AND qr.student_id = $student_id AND qr.score >= q.passing_score 
                   LIMIT 1";
$final_exam_result = $conn->query($final_exam_sql);

if($final_exam_result->num_rows == 0) {
    echo "<script>alert('You have not passed the final exam for this course yet.'); location.href='finalExam.php?course_id=$course_id' </script>";
    exit();
}

$final_exam = $final_exam_result->fetch_assoc();

// تضمين مكتبة FPDF فقط
require('fpdf/fpdf.php');

// إنشاء معرف فريد للشهادة
$certificate_id = 'CK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));

// إنشاء فئة مخصصة للشهادة
class ProfessionalCertificatePDF extends FPDF {
    private $border_image;
    private $logo_image;
    private $watermark_image;
    private $seal_image;
    private $certificate_id;
    
    function __construct($certificate_id) {
        parent::__construct('L', 'mm', 'A4');
        $this->border_image = '../images/certificate_border.png';
        $this->logo_image = '../images/logo.png';
        $this->watermark_image = '../images/certificate_watermark.png';
        $this->seal_image = '../images/certificate_seal.png';
        $this->certificate_id = $certificate_id;
    }
    
    function Header() {
        // إضافة تصميم الحدود
        if(file_exists($this->border_image)) {
            $this->Image($this->border_image, 0, 0, 297, 210);
        }
        
        // إضافة علامة مائية
        if(file_exists($this->watermark_image)) {
            $this->Image($this->watermark_image, 50, 50, 200, 120, '', '', '', false, 300, '', false, false, 0);
        }
        
        // إضافة الشعار
        if(file_exists($this->logo_image)) {
            $this->Image($this->logo_image, 30, 25, 40);
        }
        
        // إضافة معرف الشهادة في أعلى اليمين
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(200, 20);
        $this->Cell(70, 8, 'Certificate ID: ' . $this->certificate_id, 0, 1, 'R');
    }
    
    function Footer() {
        // باركود في الأسفل
        $this->SetY(-30);
        $this->SetFont('Arial', '', 8);
        
        // إنشاء باركود بسيط
        $this->Cell(0, 5, str_repeat('_', 50), 0, 1, 'C');
        $this->Cell(0, 5, $this->certificate_id, 0, 1, 'C');
        
        // كود QR للتحقق (باستخدام خدمة عبر الإنترنت)
        $qrData = urlencode('https://codekids.com/verify-certificate?id=' . $this->certificate_id);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qrData;
        
        // حفظ صورة QR مؤقتاً
        $tempQR = tempnam(sys_get_temp_dir(), 'qr') . '.png';
        file_put_contents($tempQR, file_get_contents($qrUrl));
        
        // إضافة صورة QR إلى PDF
        $this->Image($tempQR, 240, 170, 30, 30);
        
        // حذف الملف المؤقت
        unlink($tempQR);
        
        // نص التحقق
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Scan QR code to verify this certificate', 0, 0, 'R');
    }
    
    function AddMainContent($student_name, $course_name, $score, $issue_date, $course_duration) {
        // عنوان الشهادة مع عناصر زخرفية
        $this->SetY(50);
        $this->SetFont('Times', 'B', 36);
        $this->SetTextColor(10, 50, 100);
        $this->Cell(0, 15, 'CERTIFICATE OF EXCELLENCE', 0, 1, 'C');
        
        // خطوط زخرفية
        $this->SetDrawColor(200, 160, 60);
        $this->SetLineWidth(1.5);
        $this->Line(60, 75, 237, 75);
        $this->Line(60, 180, 237, 180);
        
        // مقدمة الشهادة
        $this->SetY(85);
        $this->SetFont('Times', '', 20);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'THIS CERTIFICATE IS PROUDLY PRESENTED TO', 0, 1, 'C');
        
        // اسم الطالب
        $this->SetY(100);
        $this->SetFont('Times', 'B', 32);
        $this->SetTextColor(10, 50, 100);
        $this->Cell(0, 15, strtoupper($student_name), 0, 1, 'C');
        
        // نص الإنجاز
        $this->SetY(120);
        $this->SetFont('Times', '', 18);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(0, 10, "for successfully completing the ".$course_duration." hours course in\n" . 
            strtoupper($course_name) . "\nwith distinction and achieving a final score of " . $score . "%", 0, 'C');
                $this->SetY(190);
        $this->SetFont('Times', 'I', 14);
        $this->Cell(0, 10, 'Issued on: ' . $issue_date, 0, 1, 'C');
        // الختم الرسمي
        
                // تاريخ الإصدار
        $this->SetY(190);
        $this->SetFont('Times', 'I', 14);
        $this->Cell(0, 10, 'Issued on: ' . $issue_date, 0, 1, 'C');
        
        
        if(file_exists($this->seal_image)) {
            $this->Image($this->seal_image, 130, 140, 40, 40);
        }
   
   
        


        

    }
}

// الحصول على مدة الدورة (افتراضياً 40 ساعة)
$course_duration = isset($course['course_duration']) ? $course['course_duration'] : '40';

// إنشاء شهادة جديدة
$pdf = new ProfessionalCertificatePDF($certificate_id);
$pdf->AddPage();
$pdf->AddMainContent(
    $student['stu_name'],
    $course['course_name'],
    $final_exam['score'],
    date('F j, Y'),
    $course_duration
);

// إنشاء اسم فريد للملف
$filename = 'CodeKids_Certificate_' . $certificate_id . '.pdf';

// إخراج ملف PDF للتنزيل
$pdf->Output('D', $filename);
?>