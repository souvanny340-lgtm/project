<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white bg-dark">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">📊 ລະບົບປະເມີນ</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : 'text-white'; ?>">🏠 ໜ້າຫຼັກ</a>
        </li>
        <li>
            <a href="teacher.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'teacher.php') ? 'active' : 'text-white'; ?>">👨‍🏫 ຈັດການຂໍ້ມູນອາຈານ</a>
        </li>
        <li>
            <a href="classroom.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'classroom.php') ? 'active' : 'text-white'; ?>">🏫 ຈັດການຫ້ອງຮຽນ</a>
        </li>
        <li>
            <a href="major.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'major.php') ? 'active' : 'text-white'; ?>">🎓 ຈັດການສາຂາຮຽນ</a>
        </li>
        <li>
            <a href="teaching.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'teaching.php') ? 'active' : 'text-white'; ?>">🗓️ ຕາຕະລາງການສອນ</a>
        </li>
        <li>
            <a href="question.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'question.php') ? 'active' : 'text-white'; ?>">📝 ຈັດການຄຳຖາມ</a>
        </li>
        <li>
            <a href="questionnaire.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'questionnaire.php') ? 'active' : 'text-white'; ?>">📋 ຈັດການແບບສອບຖາມ</a>
        </li>
        <li>
            <a href="answer.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'answer.php') ? 'active' : 'text-white'; ?>">💬 ຈັດການຄຳຕອບ</a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2"></i>
            <strong><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] : 'Admin User'; ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow border-0" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i>ຕັ້ງຄ່າ</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>ອອກຈາກລະບົບ</a></li>
        </ul>
    </div>
</div>