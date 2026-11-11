<?php
/**
 * evaluate.php
 * หน้าสำหรับให้นັກສຶກສາເຂົ້າມາຕອບແບບສອບຖາມ ຜ່ານ Token
 */

require_once 'dbconfig.php';
$database = new Database();
$db = $database->getConnection();

$error_msg = "";
$success_msg = "";
$questionnaire = null;
$questions = [];

// ກວດສອບວ່າສົ່ງ Token ມານຳບໍ່
if (!isset($_GET['token']) || empty(trim($_GET['token']))) {
    $error_msg = "ບໍ່ພົບລິ້ງການປະເມີນທີ່ຖືກຕ້ອງ (Invalid Link).";
} else {
    $token = trim($_GET['token']);

    try {
        // 1. ກວດສອບ Token ແລະ ສະຖານະໃນຕາຕະລາງ evaluation_link
        $link_sql = "SELECT l.*, q.title, q.description, q.id as q_id 
                     FROM evaluation_link l
                     JOIN questionnaire q ON l.questionnaire_id = q.id
                     WHERE l.token = :token";
        $link_stmt = $db->prepare($link_sql);
        $link_stmt->bindParam(':token', $token);
        $link_stmt->execute();

        if ($link_stmt->rowCount() == 0) {
            $error_msg = "ລິ້ງການປະເມີນນີ້ບໍ່ມີຢູ່ໃນລະບົບ.";
        } else {
            $link_data = $link_stmt->fetch(PDO::FETCH_ASSOC);

            // ກວດສອບວ່າລິ້ງຖືກປິດການໃຊ້ງານຫຼືບໍ່
            if ($link_data['status'] !== 'active') {
                $error_msg = "ຂໍ້ອະໄພ, ແບບສອບຖາມນີ້ຖືກປິດການປະເມີນແລ້ວໃນຂະນະນີ້.";
            } else {
                $questionnaire = $link_data;
                $questionnaire_id = $link_data['q_id'];

                // 2. ດຶງຄຳຖາມຕາມໂຄງສ້າງຕາຕະລາງ answer, question, questionnaire ຂອງທ່ານ
                $q_sql = "SELECT a.*, q.question_title, qn.description, qn.title FROM answer a
                          JOIN question q ON a.question_id = q.id
                          JOIN questionnaire qn ON a.questionnaire_id = qn.id
                          WHERE a.questionnaire_id = :qid ORDER BY a.id ASC";
                $q_stmt = $db->prepare($q_sql);
                $q_stmt->bindParam(':qid', $questionnaire_id);
                $q_stmt->execute();
                $questions = $q_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        $error_msg = "ເກີດຂໍ້ຜິດພາດໃນລະບົບ: " . $e->getMessage();
    }
}

// 3. ຈັດການການສົ່ງຄຳຕອບ (Submit Evaluation) ບັນທຶກລົງຕາຕະລາງ evaluation_result
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_evaluation']) && $questionnaire) {
    $questionnaire_id = $questionnaire['q_id'];
    
    // ຮັບຂໍ້ມູນຄຳຕອບ (array: answer[question_id] = score/value)
    $answers = isset($_POST['answer']) ? $_POST['answer'] : [];

    if (empty($answers)) {
        $error_msg = "ກະລຸນາຕອບຄຳຖາມໃຫ້ຄົບຖ້ວນ.";
    } else {
        try {
            $db->beginTransaction();

            // ບັນທຶກລົງຕາຕະລາງ evaluation_result ໂດຍກົງຕາມແຕ່ລະຂໍ້ທີ່ຕອບ
            // (ກະລຸນາປັບຊື່ Column ພາຍໃນ evaluation_result ໃຫ້ກົງກັບ Database ຂອງທ່ານເຊັ່ນ: questionnaire_id, question_id, answer_value/score, submitted_at)
            $res_sql = "INSERT INTO evaluation_result (questionnaire_id, question_id, score, created_at) 
                        VALUES (:qid, :q_id, :ans_val, NOW())";
            $res_stmt = $db->prepare($res_sql);

            foreach ($answers as $q_id => $ans_val) {
                // ປ້ອງກັນ XSS / Clean data
                $clean_ans = is_array($ans_val) ? json_encode($ans_val) : htmlspecialchars(trim($ans_val));
                
                $res_stmt->bindParam(':qid', $questionnaire_id);
                $res_stmt->bindParam(':q_id', $q_id);
                $res_stmt->bindParam(':ans_val', $clean_ans);
                $res_stmt->execute();
            }

            $db->commit();
            $success_msg = "ສົ່ງແບບປະເມີນສຳເລັດແລ້ວ! ຂອບໃຈທີ່ໃຫ້ຄວາມຮ່ວມມື.";
            // ລ້າງຄຳຖາມເພື່ອບໍ່ໃຫ້ສະແດງຟອມຊ້ຳ
            $questionnaire = null; 

        } catch (Exception $e) {
            $db->rollBack();
            $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຄຳຕອບ: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $questionnaire ? htmlspecialchars($questionnaire['title']) : 'ແບບຟອມປະເມີນຜົນ'; ?></title>
    <!-- Google Fonts: Noto Sans Lao -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@100..900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-dark: #0f172a; /* ສີຟ້າເຂັ້ມເກືອບດຳ (Slate 900) */
            --primary-blue: #1e3a8a; /* ສີຟ້າເຂັ້ມ (Blue 900) */
            --accent-blue: #3b82f6;  /* ສີຟ້າສົດ */
            --bg-light: #f8fafc;     /* ສີຂາວອົມເທົ່າອ່ອນໆ (Slate 50) */
        }

        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .navbar-custom {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-card {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease;
        }

        .card-custom:hover {
            transform: translateY(-2px);
        }

        .btn-primary-custom {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
            font-weight: 500;
            padding: 10px 24px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
        }

        .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        footer {
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-dark py-3">
        <div class="container">
            <span class="navbar-brand mb-0 h1 fs-5 fw-bold">
                <i class="bi bi-clipboard2-check-fill me-2 text-info"></i>ລະບົບປະເມີນຜົນອອນໄລນ໌
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5 flex-grow-1" style="max-width: 800px;">
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill display-4 text-danger mb-3 d-block"></i>
                <h4 class="fw-bold mb-2">ຂໍ້ອະໄພ</h4>
                <p class="mb-0 fs-5"><?php echo $error_msg; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="card card-custom p-5 text-center bg-white border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success display-1"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">ສຳເລັດແລ້ວ!</h2>
                    <p class="text-muted fs-5 mb-4"><?php echo $success_msg; ?></p>
                    <a href="index.php" class="btn btn-primary-custom">ກັບຄືນໜ້າຫຼັກ</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($questionnaire && empty($success_msg)): ?>
            <!-- Header Section -->
            <div class="header-card p-4 p-md-5 mb-4">
                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($questionnaire['title']); ?></h1>
                <p class="mb-0 opacity-75 fs-6 lh-base"><?php echo nl2br(htmlspecialchars($questionnaire['description'])); ?></p>
            </div>

            <!-- Questionnaire Form -->
            <form method="POST">
                <?php if (!empty($questions)): ?>
                    <?php $no = 1; foreach ($questions as $q): ?>
                        <div class="card card-custom p-4 mb-3 bg-white">
                            <div class="mb-3 fw-bold text-dark">
                                <span class="text-primary me-2"><?php echo $no++; ?>.</span> 
                                <?php echo htmlspecialchars($q['question_title']); ?>
                            </div>

                            <div class="vstack gap-2 ps-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="5" id="q_<?php echo $q['question_id']; ?>_5" required>
                                    <label class="form-check-label" for="q_<?php echo $q['question_id']; ?>_5">ດີຫຼາຍ (5 ຄະແນນ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="4" id="q_<?php echo $q['question_id']; ?>_4">
                                    <label class="form-check-label" for="q_<?php echo $q['question_id']; ?>_4">ດີ (4 ຄະແນນ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="3" id="q_<?php echo $q['question_id']; ?>_3">
                                    <label class="form-check-label" for="q_<?php echo $q['question_id']; ?>_3">ປານກາງ (3 ຄະແນນ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="2" id="q_<?php echo $q['question_id']; ?>_2">
                                    <label class="form-check-label" for="q_<?php echo $q['question_id']; ?>_2">ຄວນປັບປຸງ (2 ຄະແນນ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="1" id="q_<?php echo $q['question_id']; ?>_1">
                                    <label class="form-check-label" for="q_<?php echo $q['question_id']; ?>_1">ຕ້ອງປັບປຸງດ່ວນ (1 ຄະແນນ)</label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit_evaluation" class="btn btn-primary-custom btn-lg px-5 shadow-sm">
                            <i class="bi bi-send-fill me-2"></i>ສົ່ງແບບປະເມີນ
                        </button>
                    </div>

                <?php else: ?>
                    <div class="card card-custom p-5 text-center bg-white">
                        <p class="text-muted mb-0">ຍັງບໍ່ມີຫົວຂໍ້ຄຳຖາມໃນແບບສອບຖາມຊຸດນີ້.</p>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5">
        <div class="container">
            <small>&copy; <?php echo date('Y'); ?> ລະບົບປະເມີນຜົນການສອນ. ສ້າງຂຶ້ນດ້ວຍຄວາມຕັ້ງໃຈ.</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>