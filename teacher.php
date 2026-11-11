<?php
/**
 * teacher.php
 * Secure Teacher Management CRUD system
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
    
    // --- ເພີ່ມຂໍ້ມູນອາຈານໃໝ່ ---
    if (isset($_POST['add_teacher'])) {
        $firstname = htmlspecialchars(trim($_POST['firstname']));
        $lastname = htmlspecialchars(trim($_POST['lastname']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $role = htmlspecialchars(trim($_POST['role']));

        if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)) {
            try {
                // Hash ລະຫັດຜ່ານກ່ອນບັນທຶກ
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO Teacher (firstname, lastname, email, password, role) 
                        VALUES (:firstname, :lastname, :email, :password, :role)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                
                if ($stmt->execute()) {
                    $success_msg = "ເພີ່ມຂໍ້ມູນອາຈານສຳເລັດແລ້ວ!";
                } else {
                    $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error_msg = "ອີເມວນີ້ມີໃນລະບົບແລ້ວ!";
                } else {
                    $error_msg = "Error: " . $e->getMessage();
                }
            }
        }
    }

    // --- ແກ້ໄຂຂໍ້ມູນອາຈານ ---
    if (isset($_POST['edit_teacher'])) {
        $id = $_POST['teacher_id'];
        $firstname = htmlspecialchars(trim($_POST['firstname']));
        $lastname = htmlspecialchars(trim($_POST['lastname']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $role = htmlspecialchars(trim($_POST['role']));

        if (!empty($firstname) && !empty($lastname) && !empty($email)) {
            try {
                // ກວດສອບວ່າໄດ້ປ່ຽນລະຫັດຜ່ານຫຼືບໍ່
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE Teacher SET firstname = :firstname, lastname = :lastname, 
                            email = :email, password = :password, role = :role WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':password', $hashed_password);
                } else {
                    $sql = "UPDATE Teacher SET firstname = :firstname, lastname = :lastname, 
                            email = :email, role = :role WHERE id = :id";
                    $stmt = $db->prepare($sql);
                }
                
                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':role', $role);
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

    // --- ລຶບຂໍ້ມູນອາຈານ ---
    if (isset($_POST['delete_teacher'])) {
        $id = $_POST['teacher_id'];
        try {
            $sql = "DELETE FROM Teacher WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $success_msg = "ລຶບຂໍ້ມູນອາຈານສຳເລັດແລ້ວ!";
            } else {
                $error_msg = "ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ.";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// 4. ດຶງຂໍ້ມູນອາຈານທັງໝົດ
try {
    $query = "SELECT id, firstname, lastname, email, role FROM Teacher ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teachers = [];
    $error_msg = "Error: " . $e->getMessage();
}

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold text-primary">ຈັດການຂໍ້ມູນອາຈານ</h2>
        <p class="text-muted">ເພີ່ມ, ແກ້ໄຂ ແລະ ລຶບ ຂໍ້ມູນອາຈານໃນລະບົບ</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary btn-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-person-plus-fill me-2"></i>ເພີ່ມອາຈານໃໝ່
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
                        <th class="ps-4">ລຳດັບ</th>
                        <th>ຊື່ ແລະ ນາມສະກຸນ</th>
                        <th>ອີເມວ</th>
                        <th>ບົດບາດ</th>
                        <th class="text-end pe-4">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($teachers) > 0): ?>
                        <?php $i = 1; foreach($teachers as $row): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-3 text-primary">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge <?php echo ($row['role'] == 'Admin') ? 'bg-danger' : 'bg-info'; ?> bg-opacity-10 <?php echo ($row['role'] == 'Admin') ? 'text-danger' : 'text-info'; ?> px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-2" 
                                        onclick="editTeacher(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                        title="ແກ້ໄຂ">
                                    <i class="bi bi-pencil-square"></i> ແກ້ໄຂ
                                </button>
                                <button class="btn btn-sm btn-light text-danger" 
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>')"
                                        title="ລຶບ">
                                    <i class="bi bi-trash3"></i> ລຶບ
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-people display-4 text-muted mb-3 d-block"></i>
                                <p class="text-muted">ບໍ່ມີຂໍ້ມູນອາຈານໃນລະບົບ.</p>
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
                    <h5 class="modal-title fw-bold">ເພີ່ມອາຈານໃໝ່</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ຊື່</label>
                            <input type="text" name="firstname" class="form-control" placeholder="ຊື່" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ນາມສະກຸນ</label>
                            <input type="text" name="lastname" class="form-control" placeholder="ນາມສະກຸນ" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ອີເມວ</label>
                            <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ລະຫັດຜ່ານ</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ບົດບາດ</label>
                            <select name="role" class="form-select" required>
                                <option value="Teacher">Teacher (ອາຈານ)</option>
                                <option value="Admin">Admin (ແອດມິນ)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_teacher" class="btn btn-primary px-4">ບັນທຶກຂໍ້ມູນ</button>
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
                <input type="hidden" name="teacher_id" id="edit_id">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">ແກ້ໄຂຂໍ້ມູນອາຈານ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ຊື່</label>
                            <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ນາມສະກຸນ</label>
                            <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ອີເມວ</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ລະຫັດຜ່ານໃໝ່ (ປະວ່າງໄວ້ຫາກບໍ່ຕ້ອງການປ່ຽນ)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ບົດບາດ</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="Teacher">Teacher (ອາຈານ)</option>
                                <option value="Admin">Admin (ແອດມິນ)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_teacher" class="btn btn-warning px-4">ບັນທຶກການປ່ຽນແປງ</button>
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
                <input type="hidden" name="teacher_id" id="delete_id">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-person-x text-danger display-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">ຢືນຢັນການລຶບ?</h3>
                    <p class="text-muted mb-4 fs-5" id="delete_text"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">ຍົກເລີກ</button>
                        <button type="submit" name="delete_teacher" class="btn btn-danger px-4 py-2">ລຶບຂໍ້ມູນ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editTeacher(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_firstname').value = data.firstname;
        document.getElementById('edit_lastname').value = data.lastname;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_role').value = data.role;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_text').innerText = "ທ່ານຕ້ອງການລຶບຂໍ້ມູນອາຈານ \"" + name + "\" ແທ້ຫຼືບໍ່? ຂໍ້ມູນນີ້ບໍ່ສາມາດກູ້ຄືນໄດ້.";
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php
include 'footer.php';
?>