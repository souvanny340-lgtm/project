<?php
/**
 * answer.php
 * Secure Answer Management CRUD system
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
    
    // --- ເພີ່ມຂໍ້ມູນຄຳຕອບໃໝ່ ---
    if (isset($_POST['add_answer'])) {
        $score = filter_var($_POST['score'], FILTER_SANITIZE_NUMBER_INT);
        $question_id = $_POST['question_id'];
        $questionnaire_id = $_POST['questionnaire_id'];

        if (!empty($score) && !empty($question_id) && !empty($questionnaire_id)) {
            try {
                $sql = "INSERT INTO Answer (score, Question_id, Questionnaire_id) 
                        VALUES (:score, :question_id, :questionnaire_id)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':score', $score);
                $stmt->bindParam(':question_id', $question_id);
                $stmt->bindParam(':questionnaire_id', $questionnaire_id);
                if ($stmt->execute()) {
                    $success_msg = "ເພີ່ມຂໍ້ມູນຄຳຕອບສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ແກ້ໄຂຂໍ້ມູນຄຳຕອບ ---
    if (isset($_POST['edit_answer'])) {
        $id = $_POST['answer_id'];
        $score = filter_var($_POST['score'], FILTER_SANITIZE_NUMBER_INT);
        $question_id = $_POST['question_id'];
        $questionnaire_id = $_POST['questionnaire_id'];

        try {
            $sql = "UPDATE Answer SET score = :score, Question_id = :question_id, 
                    Questionnaire_id = :questionnaire_id WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':score', $score);
            $stmt->bindParam(':question_id', $question_id);
            $stmt->bindParam(':questionnaire_id', $questionnaire_id);
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

    // --- ລຶບຂໍ້ມູນຄຳຕອບ ---
    if (isset($_POST['delete_answer'])) {
        $id = $_POST['answer_id'];
        try {
            $sql = "DELETE FROM Answer WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $success_msg = "ລຶບຂໍ້ມູນຄຳຕອບສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// 4. ດຶງຂໍ້ມູນ Dropdown (Questions & Questionnaires)
$questions = $db->query("SELECT * FROM Question ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$questionnaires = $db->query("SELECT * FROM Questionnaire ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// 5. ດຶງຂໍ້ມູນຄຳຕອບທັງໝົດ ພ້ອມ Join ເພື່ອສະແດງຊື່ຄຳຖາມ ແລະ ແບບສອບຖາມ
$query = "SELECT a.*, q.question_title, qn.title as questionnaire_title 
          FROM Answer a
          LEFT JOIN Question q ON a.Question_id = q.id
          LEFT JOIN Questionnaire qn ON a.Questionnaire_id = qn.id
          ORDER BY a.id DESC";
$answers = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-primary">ຈັດການຂໍ້ມູນຄຳຕອບ (Answers)</h2>
        <p class="text-muted">ເບິ່ງ ແລະ ຈັດການຄະແນນການປະເມີນຈາກນັກສຶກສາ</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary btn-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-2"></i>ເພີ່ມຄຳຕອບໃໝ່
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
                        <th class="ps-4">ລຳດັບ</th>
                        <th>ແບບສອບຖາມ</th>
                        <th>ຄຳຖາມ</th>
                        <th class="text-center">ຄະແນນ</th>
                        <th class="text-end pe-4">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($answers) > 0): ?>
                        <?php $i = 1; foreach($answers as $row): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($row['questionnaire_title']); ?></span></td>
                            <td>
                                <div class="text-truncate" style="max-width: 400px;">
                                    <?php echo htmlspecialchars($row['question_title']); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 text-success"><?php echo $row['score']; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-2" 
                                        onclick="editAnswer(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                        title="ແກ້ໄຂ">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['question_title']); ?>')"
                                        title="ລຶບ">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-chat-left-dots display-4 text-muted mb-3 d-block"></i>
                                <p class="text-muted">ບໍ່ມີຂໍ້ມູນຄຳຕອບໃນລະບົບ.</p>
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
                    <h5 class="modal-title fw-bold">ເພີ່ມຂໍ້ມູນຄຳຕອບ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ແບບສອບຖາມ</label>
                        <select name="questionnaire_id" class="form-select" required>
                            <option value="">-- ເລືອກແບບສອບຖາມ --</option>
                            <?php foreach($questionnaires as $qn): ?>
                                <option value="<?php echo $qn['id']; ?>"><?php echo $qn['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄຳຖາມ</label>
                        <select name="question_id" class="form-select" required>
                            <option value="">-- ເລືອກຄຳຖາມ --</option>
                            <?php foreach($questions as $q): ?>
                                <option value="<?php echo $q['id']; ?>"><?php echo $q['question_title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄະແນນ (1-5)</label>
                        <input type="number" name="score" class="form-control" min="1" max="5" value="5" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_answer" class="btn btn-primary px-4">ບັນທຶກຂໍ້ມູນ</button>
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
                <input type="hidden" name="answer_id" id="edit_id">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">ແກ້ໄຂຂໍ້ມູນຄຳຕອບ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ແບບສອບຖາມ</label>
                        <select name="questionnaire_id" id="edit_questionnaire_id" class="form-select" required>
                            <?php foreach($questionnaires as $qn): ?>
                                <option value="<?php echo $qn['id']; ?>"><?php echo $qn['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄຳຖາມ</label>
                        <select name="question_id" id="edit_question_id" class="form-select" required>
                            <?php foreach($questions as $q): ?>
                                <option value="<?php echo $q['id']; ?>"><?php echo $q['question_title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ຄະແນນໃໝ່ (1-5)</label>
                        <input type="number" name="score" id="edit_score" class="form-control" min="1" max="5" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_answer" class="btn btn-warning px-4">ບັນທຶກການປ່ຽນແປງ</button>
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
                <input type="hidden" name="answer_id" id="delete_id">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-circle text-danger display-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">ຢືນຢັນການລຶບ?</h3>
                    <p class="text-muted mb-4 fs-5" id="delete_text"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">ຍົກເລີກ</button>
                        <button type="submit" name="delete_answer" class="btn btn-danger px-4 py-2">ລຶບຂໍ້ມູນ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editAnswer(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_score').value = data.score;
        document.getElementById('edit_question_id').value = data.Question_id;
        document.getElementById('edit_questionnaire_id').value = data.Questionnaire_id;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, text) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_text').innerText = "ທ່ານຕ້ອງການລຶບຄຳຕອບຂອງຄຳຖາມ \"" + text + "\" ແທ້ຫຼືບໍ່?";
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php
include 'footer.php';
?>