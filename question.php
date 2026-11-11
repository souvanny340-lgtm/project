<?php
/**
 * question.php
 * Secure Question Management CRUD system
 */

// 1. ກວດສອບການເຂົ້າສູ່ລະບົບ
require_once 'session.php';
checkLogin();

// 2. ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
require_once 'dbconfig.php';
$database = new Database();
$db = $database->getConnection();

$success_msg = "";
$error_msg = "";

// 3. ຈັດການການສົ່ງຂໍ້ມູນ (POST Actions)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- ເພີ່ມຄຳຖາມໃໝ່ ---
    if (isset($_POST['add_question'])) {
        $title = htmlspecialchars(trim($_POST['question_title']));
        if (!empty($title)) {
            try {
                $sql = "INSERT INTO Question (question_title) VALUES (:title)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':title', $title);
                if ($stmt->execute()) {
                    $success_msg = "ເພີ່ມຄຳຖາມສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ແກ້ໄຂຄຳຖາມ ---
    if (isset($_POST['edit_question'])) {
        $id = $_POST['question_id'];
        $title = htmlspecialchars(trim($_POST['question_title']));
        if (!empty($title)) {
            try {
                $sql = "UPDATE Question SET question_title = :title WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':id', $id);
                if ($stmt->execute()) {
                    $success_msg = "ແກ້ໄຂຄຳຖາມສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການແກ້ໄຂຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ລຶບຄຳຖາມ ---
    if (isset($_POST['delete_question'])) {
        $id = $_POST['question_id'];
        try {
            $sql = "DELETE FROM Question WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $success_msg = "ລຶບຄຳຖາມສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// 4. ດຶງຂໍ້ມູນຄຳຖາມທັງໝົດ
try {
    $query = "SELECT * FROM Question ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $questions = [];
    $error_msg = "Error: " . $e->getMessage();
}

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-primary">ຈັດການຄຳຖາມປະເມີນ</h2>
        <p class="text-muted">ເພີ່ມ, ແກ້ໄຂ ແລະ ລຶບ ຄຳຖາມສຳລັບແບບສອບຖາມ</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary btn-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-2"></i>ເພີ່ມຄຳຖາມໃໝ່
        </button>
    </div>
</div>

<!-- ສ່ວນສະແດງການແຈ້ງເຕືອນ -->
<?php if($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ຕາຕະລາງສະແດງຂໍ້ມູນ -->
<div class="card custom-card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" width="100">ລຳດັບ</th>
                        <th>ຫົວຂໍ້ຄຳຖາມ</th>
                        <th class="text-end pe-4" width="200">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($questions) > 0): ?>
                        <?php $i = 1; foreach($questions as $row): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-3 text-primary">
                                        <i class="bi bi-question-square-fill"></i>
                                    </div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($row['question_title']); ?></span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-2" 
                                        onclick="editQuestion(<?php echo $row['id']; ?>, '<?php echo addslashes($row['question_title']); ?>')"
                                        title="ແກ້ໄຂ">
                                    <i class="bi bi-pencil-square"></i> ແກ້ໄຂ
                                </button>
                                <button class="btn btn-sm btn-light text-danger" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['question_title']); ?>')"
                                        title="ລຶບ">
                                    <i class="bi bi-trash3"></i> ລຶບ
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="bi bi-clipboard-x display-4 text-muted mb-3 d-block"></i>
                                <p class="text-muted">ບໍ່ມີຂໍ້ມູນຄຳຖາມໃນລະບົບ.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal ເພີ່ມຂໍ້ມູນ -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">ເພີ່ມຄຳຖາມໃໝ່</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຫົວຂໍ້ຄຳຖາມ</label>
                        <textarea name="question_title" class="form-control" rows="3" placeholder="ປ້ອນຄຳຖາມທີ່ຕ້ອງການປະເມີນ..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_question" class="btn btn-primary px-4">ບັນທຶກຂໍ້ມູນ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ແກ້ໄຂຂໍ້ມູນ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <input type="hidden" name="question_id" id="edit_id">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">ແກ້ໄຂຄຳຖາມ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຫົວຂໍ້ຄຳຖາມໃໝ່</label>
                        <textarea name="question_title" id="edit_title" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_question" class="btn btn-warning px-4">ບັນທຶກການປ່ຽນແປງ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ຢືນຢັນການລຶບ -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <input type="hidden" name="question_id" id="delete_id">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-octagon text-danger display-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">ຢືນຢັນການລຶບ?</h3>
                    <p class="text-muted mb-4 fs-5" id="delete_text"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">ຍົກເລີກ</button>
                        <button type="submit" name="delete_question" class="btn btn-danger px-4 py-2">ລຶບຂໍ້ມູນ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editQuestion(id, title) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_title').value = title;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, title) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_text').innerText = "ທ່ານຕ້ອງການລຶບຄຳຖາມ \"" + title + "\" ແທ້ຫຼືບໍ່? ຂໍ້ມູນນີ້ບໍ່ສາມາດກູ້ຄືນໄດ້.";
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php
include 'footer.php';
?>