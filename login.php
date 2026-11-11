<?php
/**
 * login.php
 * Secure Authentication System using PDO and OOP
 */

// 1. ເລີ່ມຕົ້ນ Session

// echo password_hash('admin123',PASSWORD_DEFAULT);
session_start();

// ຖ້າຫາກ User ເຂົ້າສູ່ລະບົບແລ້ວ ໃຫ້ Redirect ໄປໜ້າ index.php ທັນທີ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. ນຳເຂົ້າໄຟລ໌ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
require_once 'dbconfig.php';

$error_message = "";

// 3. ກວດສອບການສົ່ງຟອມ (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ຮັບຄ່າ ແລະ ທຳຄວາມສະອາດຂໍ້ມູນເບື້ອງຕົ້ນ
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            // ສ້າງ Instance ຂອງຄຼາສ Database
            $database = new Database();
            $db = $database->getConnection();

            if ($db) {
                // ກຽມ SQL Statement ເພື່ອປ້ອງກັນ SQL Injection (Prepared Statements)
                $sql = "SELECT id, firstname, lastname, email, password, role 
                        FROM Teacher 
                        WHERE email = :email 
                        LIMIT 1";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // ກວດສອບວ່າມີ User ນີ້ໃນລະບົບຫຼືບໍ່
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // ກວດສອບລະຫັດຜ່ານດ້ວຍ password_verify
                    if (password_verify($password, $user['password'])) {
                        
                        // ເກັບຂໍ້ມູນລົງໃນ Session
                        $_SESSION['user_id']   = $user['id'];
                        $_SESSION['firstname'] = $user['firstname'];
                        $_SESSION['lastname']  = $user['lastname'];
                        $_SESSION['email']     = $user['email'];
                        $_SESSION['role']      = $user['role'];

                        // Redirect ໄປໜ້າຫຼັກ
                        header("Location: index.php");
                        exit();
                    } else {
                        $error_message = "ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ!";
                    }
                } else {
                    $error_message = "ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ!";
                }
            } else {
                $error_message = "ບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້.";
            }
        } catch (PDOException $e) {
            $error_message = "ເກີດຂໍ້ຜິດພາດ: " . $e->getMessage();
        }
    } else {
        $error_message = "ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບຖ້ວນ!";
    }
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ເຂົ້າສູ່ລະບົບ - Teacher Evaluation System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6, #60a5fa);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Phetsarath OT', 'Segoe UI', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            text-align: center;
            padding-top: 30px;
        }
        .card-header h2 {
            color: #1e3a8a;
            font-weight: bold;
        }
        .btn-primary {
            background: #1e3a8a;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }
        .alert {
            border-radius: 12px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header">
        <h2>ເຂົ້າສູ່ລະບົບ</h2>
        <p class="text-muted">ລະບົບປະເມິນການສອນອາຈານ</p>
    </div>
    <div class="card-body p-4">
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">ອີເມວ (Email)</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="touna@gmail.com" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">ລະຫັດຜ່ານ (Password)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">
                    ເຂົ້າສູ່ລະບົບ
                </button>
            </div>
        </form>

    </div>
    <div class="card-footer text-center pb-4 bg-transparent border-0">
        <small class="text-muted">© 2026 Teacher Evaluation System</small>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>