<?php
// الاتصال بقاعدة البيانات
session_start();
try {
    $dbname = new PDO('mysql:host=localhost;dbname=aitp', 'root', '');
    $dbname->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}

function calculateCost($numPages, $numCopies, $colorMode, $printSides)
{
    if ($colorMode == 'color') {
        if ($printSides == 'two-sided') {
            $pagePrice = 4;
        } else {
            $pagePrice = 3;
        }
    } else {
        if ($printSides == 'two-sided') {
            $pagePrice = 3;
        } else {
            $pagePrice = 2;
        }
    }

    // حساب التكلفة الإجمالية
    $totalCost = $numPages * $numCopies * $pagePrice;

    return $totalCost;

}

// معالجة رفع الملف وإنشاء مهمة طباعة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تحقق مما إذا كانت البيانات مرسلة كـ JSON (AJAX)
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strpos($contentType, 'application/json') !== false) {
        // استقبال بيانات JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!empty($data)) {
            // التحقق من وجود الملف المرفوع سابقاً
            if (isset($data['file_path']) && file_exists($data['file_path'])) {
                // استخراج بيانات الطباعة من الـ JSON
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // استخدم معرف المستخدم من الجلسة أو قيمة افتراضية
                $file_name = $data['file_name'];
                $file_path = $data['file_path'];
                $num_pages = $data['num_pages'];
                $num_copies = $data['num_copies'];
                $color_mode = $data['color_mode'];
                $print_sides = $data['print_sides'];
                $orientation = $data['orientation'];
                $page_range = isset($data['page_range']) ? $data['page_range'] : 'all';

                // حساب التكلفة
                $cost = calculateCost($num_pages, $num_copies, $color_mode, $print_sides);

                // إدخال مهمة الطباعة في قاعدة البيانات
                $stmt = $dbname->prepare("INSERT INTO print_jobs (user_id, file_name, file_path, num_pages, num_copies, 
                                        color_mode, print_sides, orientation, cost, status, created_at) 
                                        VALUES (:user_id, :file_name, :file_path, :num_pages, :num_copies, 
                                        :color_mode, :print_sides, :orientation, :cost, 'pending', NOW())");

                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':file_name', $file_name);
                $stmt->bindParam(':file_path', $file_path);
                $stmt->bindParam(':num_pages', $num_pages);
                $stmt->bindParam(':num_copies', $num_copies);
                $stmt->bindParam(':color_mode', $color_mode);
                $stmt->bindParam(':print_sides', $print_sides);
                $stmt->bindParam(':orientation', $orientation);
                $stmt->bindParam(':cost', $cost);

                if ($stmt->execute()) {
                    $job_id = $dbname->lastInsertId();
                    echo json_encode(['success' => true, 'message' => 'تم إنشاء مهمة الطباعة بنجاح', 'job_id' => $job_id, 'cost' => $cost]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'فشل في إنشاء مهمة الطباعة']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'الملف غير موجود']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'لم يتم استلام بيانات صالحة']);
        }
    }
    // معالجة رفع الملف عبر FormData
    elseif (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // التحقق من نوع الملف (يمكنك إضافة المزيد من التحقق حسب الحاجة)
        $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png');
        $file_type = $_FILES['file']['type'];

        if (in_array($file_type, $allowed_types)) {
            $file_name = $_FILES['file']['name'];
            $tmp_path = $_FILES['file']['tmp_name'];

            // إنشاء مجلد للملفات المرفوعة إذا لم يكن موجوداً
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // إنشاء اسم فريد للملف لتجنب تكرار الأسماء
            $unique_file_name = time() . '_' . $file_name;
            $target_path = $upload_dir . $unique_file_name;

            // نقل الملف المرفوع
            if (move_uploaded_file($tmp_path, $target_path)) {
                // في حالة الرفع فقط، سنعيد مسار الملف ليتم استخدامه لاحقاً في إنشاء مهمة الطباعة
                echo json_encode([
                    'success' => true,
                    'message' => 'تم رفع الملف بنجاح',
                    'file_name' => $file_name,
                    'file_path' => $target_path,
                    'file_size' => $_FILES['file']['size'],
                    'file_type' => $file_type
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في نقل الملف']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم اختيار ملف أو حدث خطأ أثناء الرفع']);
    }
}

// معالجة طلب تأكيد مهمة الطباعة
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // استقبال بيانات JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['job_id'])) {
        $job_id = $data['job_id'];

        // تحديث حالة مهمة الطباعة إلى "مؤكدة"
        $stmt = $dbname->prepare("UPDATE print_jobs SET status = 'confirmed', confirmed_at = NOW() WHERE id = :job_id");
        $stmt->bindParam(':job_id', $job_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'تم تأكيد مهمة الطباعة بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل في تأكيد مهمة الطباعة']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'معرف مهمة الطباعة غير موجود']);
    }
}

// جلب تفاصيل مهمة طباعة معينة (يمكن استخدامها للتحقق من الحالة أو عرض التفاصيل)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];

    $stmt = $dbname->prepare("SELECT * FROM print_jobs WHERE id = :job_id");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
        echo json_encode(['success' => true, 'job' => $job]);
    } else {
        echo json_encode(['success' => false, 'message' => 'مهمة الطباعة غير موجودة']);
    }
}
?>