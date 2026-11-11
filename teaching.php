<?php
/**
 * teaching.php
 * Secure Teaching Management CRUD system
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
    
    // --- ເພີ່ມຂໍ້ມູນການສອນໃໝ່ ---
    if (isset($_POST['add_teaching'])) {
        $academic = htmlspecialchars(trim($_POST['academic']));
        $term = htmlspecialchars(trim($_POST['term']));
        $major_id = $_POST['major_id'];
        $session_id = $_POST['session_id'];
        $classroom_id = $_POST['classroom_id'];
        $teacher_id = $_POST['teacher_id'];

        if (!empty($academic) && !empty($term) && !empty($major_id)) {
            try {
                $sql = "INSERT INTO Teaching (academic, term, Major_id, Session_id, Classroom_id, Teacher_id) 
                        VALUES (:academic, :term, :major_id, :session_id, :classroom_id, :teacher_id)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':academic', $academic);
                $stmt->bindParam(':term', $term);
                $stmt->bindParam(':major_id', $major_id);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':classroom_id', $classroom_id);
                $stmt->bindParam(':teacher_id', $teacher_id);
                
                if ($stmt->execute()) {
                    $success_msg = "ເພີ່ມຂໍ້ມູນການສອນສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }

    // --- ແກ້ໄຂຂໍ້ມູນການສອນ ---
    if (isset($_POST['edit_teaching'])) {
        $id = $_POST['teaching_id'];
        $academic = htmlspecialchars(trim($_POST['academic']));
        $term = htmlspecialchars(trim($_POST['term']));
        $major_id = $_POST['major_id'];
        $session_id = $_POST['session_id'];
        $classroom_id = $_POST['classroom_id'];
        $teacher_id = $_POST['teacher_id'];

        try {
            $sql = "UPDATE Teaching SET academic = :academic, term = :term, Major_id = :major_id, 
                    Session_id = :session_id, Classroom_id = :classroom_id, Teacher_id = :teacher_id 
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':academic', $academic);
            $stmt->bindParam(':term', $term);
            $stmt->bindParam(':major_id', $major_id);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':classroom_id', $classroom_id);
            $stmt->bindParam(':teacher_id', $teacher_id);
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

    // --- ລຶບຂໍ້ມູນການສອນ ---
    if (isset($_POST['delete_teaching'])) {
        $id = $_POST['teaching_id'];
        try {
            $sql = "DELETE FROM Teaching WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $success_msg = "ລຶບຂໍ້ມູນການສອນສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// 4. ດຶງຂໍ້ມູນ Entities ສຳລັບ Dropdowns
$majors = $db->query("SELECT * FROM Major")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $db->query("SELECT * FROM Session")->fetchAll(PDO::FETCH_ASSOC);
$classrooms = $db->query("SELECT * FROM Classroom")->fetchAll(PDO::FETCH_ASSOC);
$teachers = $db->query("SELECT id, firstname, lastname FROM Teacher")->fetchAll(PDO::FETCH_ASSOC);

// 5. ດຶງຂໍ້ມູນການສອນທັງໝົດ ພ້ອມ Join ກັບຕາຕະລາງອື່ນ
$query = "SELECT t.*, m.major_name, s.session_name, c.classroom_name, tc.firstname, tc.lastname 
          FROM Teaching t
          LEFT JOIN Major m ON t.Major_id = m.id
          LEFT JOIN Session s ON t.Session_id = s.id
          LEFT JOIN Classroom c ON t.Classroom_id = c.id
          LEFT JOIN Teacher tc ON t.Teacher_id = tc.id
          ORDER BY t.id DESC";
$teachings = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-primary">ຈັດການຕາຕະລາງການສອນ</h2>
        <p class="text-muted">ກຳນົດອາຈານສອນ, ຫ້ອງຮຽນ ແລະ ສາຂາວິຊາ ສຳລັບການປະເມີນ</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary btn-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-calendar-check-fill me-2"></i>ເພີ່ມຕາຕະລາງສອນ
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
                        <th class="ps-4">ສົກຮຽນ</th>
                        <th>ພາກຮຽນ</th>
                        <th>ສາຂາວິຊາ</th>
                        <th>ອາຈານສອນ</th>
                        <th>ຫ້ອງຮຽນ</th>
                        <th class="text-end pe-4">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($teachings) > 0): ?>
                        <?php foreach($teachings as $row): ?>
                        <tr>
                            <td class="ps-4"><strong><?php echo htmlspecialchars($row['academic']); ?></strong></td>
                            <td><span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><?php echo htmlspecialchars($row['session_name']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['major_name']); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2 text-secondary"></i>
                                    <span><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($row['classroom_name']); ?></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-2" 
                                        onclick="editTeaching(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                        title="ແກ້ໄຂ">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['academic'] . ' - ' . $row['firstname']); ?>')"
                                        title="ລຶບ">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                                <p class="text-muted">ບໍ່ມີຂໍ້ມູນຕາຕະລາງສອນໃນລະບົບ.</p>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">ເພີ່ມຕາຕະລາງການສອນໃໝ່</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ສົກຮຽນ</label>
                            <input type="text" name="academic" class="form-control" placeholder="ຕົວຢ່າງ: 2025-2026" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ພາກຮຽນທີ</label>
                            <input type="text" name="term" class="form-control" placeholder="ຕົວຢ່າງ: 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ສາຂາວິຊາ</label>
                            <select name="major_id" class="form-select" required>
                                <option value="">-- ເລືອກສາຂາ --</option>
                                <?php foreach($majors as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo $m['major_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ສົກຮຽນ/ພາກຮຽນ (Session)</label>
                            <select name="session_id" class="form-select" required>
                                <option value="">-- ເເລືອກ Session --</option>
                                <?php foreach($sessions as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo $s['session_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ຫ້ອງຮຽນ</label>
                            <select name="classroom_id" class="form-select" required>
                                <option value="">-- ເລືອກຫ້ອງຮຽນ --</option>
                                <?php foreach($classrooms as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['classroom_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ອາຈານສອນ</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">-- ເລືອກອາຈານ --</option>
                                <?php foreach($teachers as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo $t['firstname'] . ' ' . $t['lastname']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_teaching" class="btn btn-primary px-4">ບັນທຶກຂໍ້ມູນ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ແກ້ໄຂຂໍ້ມູນ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <input type="hidden" name="teaching_id" id="edit_id">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">ແກ້ໄຂຕາຕະລາງການສອນ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ສົກຮຽນ</label>
                            <input type="text" name="academic" id="edit_academic" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ພາກຮຽນທີ</label>
                            <input type="text" name="term" id="edit_term" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ສາຂາວິຊາ</label>
                            <select name="major_id" id="edit_major_id" class="form-select" required>
                                <?php foreach($majors as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo $m['major_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Session</label>
                            <select name="session_id" id="edit_session_id" class="form-select" required>
                                <?php foreach($sessions as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo $s['session_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ຫ້ອງຮຽນ</label>
                            <select name="classroom_id" id="edit_classroom_id" class="form-select" required>
                                <?php foreach($classrooms as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['classroom_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ອາຈານສອນ</label>
                            <select name="teacher_id" id="edit_teacher_id" class="form-select" required>
                                <?php foreach($teachers as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo $t['firstname'] . ' ' . $t['lastname']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_teaching" class="btn btn-warning px-4">ບັນທຶກການປ່ຽນແປງ</button>
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
                <input type="hidden" name="teaching_id" id="delete_id">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-trash3 text-danger display-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">ຢືນຢັນການລຶບ?</h3>
                    <p class="text-muted mb-4 fs-5" id="delete_text"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">ຍົກເລີກ</button>
                        <button type="submit" name="delete_teaching" class="btn btn-danger px-4 py-2">ລຶບຂໍ້ມູນ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editTeaching(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_academic').value = data.academic;
        document.getElementById('edit_term').value = data.term;
        document.getElementById('edit_major_id').value = data.Major_id;
        document.getElementById('edit_session_id').value = data.Session_id;
        document.getElementById('edit_classroom_id').value = data.Classroom_id;
        document.getElementById('edit_teacher_id').value = data.Teacher_id;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, text) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_text').innerText = "ທ່ານຕ້ອງການລຶບຕາຕະລາງ \"" + text + "\" ແທ້ຫຼືບໍ່? ຂໍ້ມູນນີ້ບໍ່ສາມາດກູ້ຄືນໄດ້.";
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php
include 'footer.php';
?>