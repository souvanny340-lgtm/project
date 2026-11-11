<?php
/**
 * index.php
 * Main Dashboard for the Teacher Evaluation System
 */

require_once 'dbconfig.php';
require_once 'session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ສ້າງການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$database = new Database();
$conn = $database->getConnection();

// --- Fetch Statistics ---

try {
    // 1. Total Tables
    $stmt = $conn->query("SHOW TABLES");
    $total_tables = $stmt->rowCount();

    // 2. Total Teachers
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM Teacher");
    $total_teachers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Total Questionnaires
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM Questionnaire");
    $total_questionnaires = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 4. Total Students (check if table exists)
    $total_students = 0;
    $check = $conn->query("SHOW TABLES LIKE 'student'");
    if ($check->rowCount() > 0) {
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM student");
        $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
} catch (PDOException $e) {
    // Handle error gracefully
    $total_tables = 0;
    $total_teachers = 0;
    $total_questionnaires = 0;
    $total_students = 0;
    $db_error = "Error: " . $e->getMessage();
}

include 'header.php';
?>

<?php if (isset($db_error)): ?>
    <div class="alert alert-danger shadow-sm border-0 mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $db_error; ?>
    </div>
<?php endif; ?>

<!-- Dashboard Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card custom-card bg-primary text-white border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2 opacity-75">
                    <i class="bi bi-grid-3x3-gap-fill fs-1"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0"><?php echo $total_tables; ?></h2>
                <p class="mb-0">ຕາຕະລາງທັງໝົດ</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card custom-card bg-success text-white border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2 opacity-75">
                    <i class="bi bi-people-fill fs-1"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0"><?php echo $total_students; ?></h2>
                <p class="mb-0">ນັກສຶກສາທັງໝົດ</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card custom-card bg-warning text-dark border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2 opacity-75">
                    <i class="bi bi-person-workspace fs-1"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0"><?php echo $total_teachers; ?></h2>
                <p class="mb-0">ອາຈານທັງໝົດ</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card custom-card bg-danger text-white border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2 opacity-75">
                    <i class="bi bi-file-earmark-text-fill fs-1"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0"><?php echo $total_questionnaires; ?></h2>
                <p class="mb-0">ແບບສອບຖາມ</p>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm p-4 bg-white">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-circle me-4">
                    <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-1">ຍິນດີຕ້ອນຮັບເຂົ້າສູ່ລະບົບ, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h3>
                    <p class="text-muted mb-0">ນີ້ແມ່ນພາບລວມຂອງລະບົບປະເມີນຜົນການສອນ. ທ່ານສາມາດເລືອກຈັດການຂໍ້ມູນຕ່າງໆໄດ້ຈາກເມນູດ້ານຊ້າຍ.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Overview Table -->
<div class="card custom-card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-primary fw-bold">ໂຄງສ້າງຖານຂໍ້ມູນ (Database Structure)</h4>
        <span class="badge bg-primary rounded-pill">Status: Online</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">ລຳດັບ</th>
                        <th class="py-3">ຊື່ຕາຕະລາງ</th>
                        <th class="py-3">ລາຍລະອຽດ</th>
                        <th class="py-3 text-center">ສະຖານະ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tables = [
                        ["answer","ເກັບຄຳຕອບການປະເມິນ", "bi-chat-left-text-fill"],
                        ["classroom","ເກັບຂໍ້ມູນຫ້ອງຮຽນ", "bi-door-open-fill"],
                        ["major","ເກັບຂໍ້ມູນສາຂາວິຊາ", "bi-mortarboard-fill"],
                        ["question","ເກັບຄຳຖາມປະເມິນ", "bi-question-circle-fill"],
                        ["questionnaire","ເກັບຊຸດແບບສອບຖາມ", "bi-file-earmark-spreadsheet-fill"],
                        ["session","ເກັບຂໍ້ມູນປີການສຶກສາ", "bi-calendar-range-fill"],
                        ["teacher","ເກັບຂໍ້ມູນອາຈານ", "bi-person-badge-fill"],
                        ["teaching","ເກັບຂໍ້ມູນການສອນ", "bi-journal-check"]
                    ];

                    $i = 1;
                    foreach($tables as $table){
                        echo "<tr>
                                <td class='px-4'>$i</td>
                                <td>
                                    <div class='d-flex align-items-center'>
                                        <i class='bi {$table[2]} me-3 text-secondary'></i>
                                        <strong class='text-dark'>{$table[0]}</strong>
                                    </div>
                                </td>
                                <td>{$table[1]}</td>
                                <td class='text-center'><span class='badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill'>Active</span></td>
                              </tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>