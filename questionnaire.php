<?php
/**
 * questionnaire.php
 * Secure Questionnaire Management CRUD system with evaluation_link table integration
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
    
    // --- ເພີ່ມແບບສອບຖາມໃໝ່ ---
    if (isset($_POST['add_questionnaire'])) {
        $title = htmlspecialchars(trim($_POST['title']));
        $description = htmlspecialchars(trim($_POST['description']));

        if (!empty($title)) {
            try {
                $sql = "INSERT INTO Questionnaire (title, description) VALUES (:title, :description)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                if ($stmt->execute()) {
                    $success_msg = "ເພີ່ມແບບສອບຖາມສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ແກ້ໄຂແບບສອບຖາມ ---
    if (isset($_POST['edit_questionnaire'])) {
        $id = $_POST['questionnaire_id'];
        $title = htmlspecialchars(trim($_POST['title']));
        $description = htmlspecialchars(trim($_POST['description']));

        if (!empty($title)) {
            try {
                $sql = "UPDATE Questionnaire SET title = :title, description = :description WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':id', $id);
                if ($stmt->execute()) {
                    $success_msg = "ແກ້ໄຂຂໍ້ມູນສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການແກ້ໄຂຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ສັ່ງເປີດລິ້ງ (ສ້າງ Token ແລະ ບັນທຶກລົງຕາຕະລາງ evaluation_link) ---
    if (isset($_POST['open_link'])) {
        $questionnaire_id = $_POST['questionnaire_id'];
        $token = bin2hex(random_bytes(16)); // ສ້າງ Token ປອດໄພ

        try {
            // ກວດສອບກ່ອນວ່າເຄີຍມີ record ໃນ evaluation_link ແລ້ວ ຫຼື ຍັງ (ອາດຈະເຄີຍປິດໄວ້)
            $check_sql = "SELECT id FROM evaluation_link WHERE questionnaire_id = :qid";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bindParam(':qid', $questionnaire_id);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                // ຖ້າມີແລ້ວ ໃຫ້ອັບເດດ Token ໃໝ່ ແລະ ປ່ຽນ status ເປັນ active
                $sql = "UPDATE evaluation_link SET token = :token, status = 'active' WHERE questionnaire_id = :qid";
            } else {
                // ຖ້າຫາກຍังບໍ່ເຄີຍມີ ໃຫ້ Insert ໃໝ່
                $sql = "INSERT INTO evaluation_link (questionnaire_id, token, status) VALUES (:qid, :token, 'active')";
            }

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':qid', $questionnaire_id);
            
            if ($stmt->execute()) {
                $success_msg = "ເປີດລິ້ງ ແລະ ສ້າງຊຸດການປະເມີນສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເປີດລິ້ງ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }

    // --- ສັ່ງປິດລິ້ງ (ປ່ຽນ status ໃນ evaluation_link ເປັນ inactive) ---
    if (isset($_POST['close_link'])) {
        $questionnaire_id = $_POST['questionnaire_id'];

        try {
            $sql = "UPDATE evaluation_link SET status = 'inactive' WHERE questionnaire_id = :qid";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':qid', $questionnaire_id);
            
            if ($stmt->execute()) {
                $success_msg = "ສັ່ງປິດລິ້ງການປະເມີນສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການປິດລິ້ງ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }

    // --- ລຶບແບບສອບຖາມ ---
    if (isset($_POST['delete_questionnaire'])) {
        $id = $_POST['questionnaire_id'];
        try {
            // 由於ຕາຕະລາງ evaluation_link ມີ ON DELETE CASCADE ແລ້ວ ມັນຈະຖືກລຶບໄປພ້ອມກັນ
            $sql = "DELETE FROM Questionnaire WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $success_msg = "ລຶບແບບສອບຖາມສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// 4. ດຶງຂໍ້ມູນແບບສອບຖາມທັງໝົດ ພ້ອມກັບຂໍ້ມູນລິ້ງຈາກຕາຕະລາງ evaluation_link (ໃຊ້ LEFT JOIN)
try {
    $query = "SELECT q.*, l.token, l.status as link_status 
              FROM Questionnaire q 
              LEFT JOIN evaluation_link l ON q.id = l.questionnaire_id 
              ORDER BY q.id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $questionnaires = [];
    $error_msg = "Error: " . $e->getMessage();
}

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-primary">ຈັດການແບບສອບຖາມ</h2>
        <p class="text-muted">ສ້າງ, ຈັດການຊຸດແບບສອບຖາມ ແລະ ຄວບຄຸມການເປີດ-ປິດລິ້ງການປະເມີນ</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary btn-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-file-earmark-plus-fill me-2"></i>ເພີ່ມແບບສອບຖາມໃໝ່
        </button>
    </div>
</div>

<!-- ແຈ້ງເຕືອນ -->
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
                        <th class="ps-4" width="70">ລຳດັບ</th>
                        <th>ຫົວຂໍ້ແບບສອບຖາມ</th>
                        <th>ສະຖານະລິ້ງ</th>
                        <th>ລິ້ງສຳລັບຕອບ</th>
                        <th class="text-end pe-4" width="220">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($questionnaires) > 0): ?>
                        <?php $i = 1; foreach($questionnaires as $row): 
                            // ຫາ URL ພື້ນຖານຂອງເວັບໄຊ
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                            $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                            $eval_link = (!empty($row['token'])) ? $base_url . "/evaluate.php?token=" . $row['token'] : "";
                        ?>
                        <tr>
                            <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
                            </td>
                            <td>
                                <?php if($row['link_status'] == 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">ເປີດໃຊ້ງານ (Active)</span>
                                <?php elseif($row['link_status'] == 'inactive'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">ປິດແລ້ວ (Inactive)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">ຍັງບໍ່ເຄີຍເປີດ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($row['token']) && $row['link_status'] == 'active'): ?>
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $eval_link; ?>" id="link_<?php echo $row['id']; ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyLink('link_<?php echo $row['id']; ?>')" title="ກັອບປີ້ລິ້ງ">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">ລິ້ງຖືກປິດ ຫຼື ຍັງບໍ່ຖືກສ້າງ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <!-- ປຸ່ມ ເປີດ-ປິດ ລິ້ງ -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="questionnaire_id" value="<?php echo $row['id']; ?>">
                                    <?php if($row['link_status'] == 'active'): ?>
                                        <!-- ຖ້າເປີດຢູ່ ສະແດງປຸ່ມປິດ -->
                                        <button type="submit" name="close_link" class="btn btn-sm btn-light text-danger me-1" title="ສັ່ງປິດລິ້ງ">
                                            <i class="bi bi-lock-fill"></i> ປິດ
                                        </button>
                                    <?php else: ?>
                                        <!-- ຖ້າປິດ ຫຼື ຍັງບໍ່ມີ ສະແດງປຸ່ມເປີດ (ສ້າງລິ້ງໃໝ່) -->
                                        <button type="submit" name="open_link" class="btn btn-sm btn-light text-success me-1" title="ສັ່ງເປີດລິ້ງ">
                                            <i class="bi bi-unlock-fill"></i> ເປີດລິ້ງ
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <!-- ປຸ່ມ ແກ້ໄຂ -->
                                <button class="btn btn-sm btn-light text-warning me-1" 
                                        onclick="editQuestionnaire(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                        title="ແກ້ໄຂ">
                                    ແກ້ໄຂ
                                </button>

                                <!-- ປຸ່ມ ລຶບ -->
                                <button class="btn btn-sm btn-light text-danger" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')"
                                        title="ລຶບ">
                                    ລຶບ
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-folder-x display-4 text-muted mb-3 d-block"></i>
                                <p class="text-muted">ບໍ່ມີຂໍ້ມູນແບບສອບຖາມໃນລະບົບ.</p>
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
                    <h5 class="modal-title fw-bold">ເພີ່ມແບບສອບຖາມໃໝ່</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຫົວຂໍ້ (Title)</label>
                        <input type="text" name="title" class="form-control form-control-lg" placeholder="ຕົວຢ່າງ: ແບບປະເມີນຜົນການສອນ ພາກຮຽນ 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄຳອະທິບາຍ (Description)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ຄຳອະທິບາຍເພີ່ມເຕີມ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_questionnaire" class="btn btn-primary px-4">ບັນທຶກຂໍ້ມູນ</button>
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
                <input type="hidden" name="questionnaire_id" id="edit_id">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">ແກ້ໄຂແບບສອບຖາມ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຫົວຂໍ້ໃໝ່</label>
                        <input type="text" name="title" id="edit_title" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄຳອະທິບາຍໃໝ່</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_questionnaire" class="btn btn-warning px-4">ບັນທຶກການປ່ຽນແປງ</button>
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
                <input type="hidden" name="questionnaire_id" id="delete_id">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-trash3 text-danger display-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">ຢືນຢັນການລຶບ?</h3>
                    <p class="text-muted mb-4 fs-5" id="delete_text"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">ຍົກເລີກ</button>
                        <button type="submit" name="delete_questionnaire" class="btn btn-danger px-4 py-2">ລຶບຂໍ້ມູນ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editQuestionnaire(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_title').value = data.title;
        document.getElementById('edit_description').value = data.description;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, title) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_text').innerText = "ທ່ານຕ້ອງການລຶບແບບສອບຖາມ \"" + title + "\" ແທ້ຫຼືບໍ່? ຂໍ້ມູນນີ້ບໍ່ສາມາດກູ້ຄືນໄດ້.";
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // ຟັງຊັນກັອບປີ້ລິ້ງ
    function copyLink(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
        
        alert("ຄັດລອກລິ້ງສຳເລັດແລ້ວ: " + copyText.value);
    }
</script>

<?php
include 'footer.php';
?>