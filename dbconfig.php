<?php
class Database {
    // กำนົດ Property สำหรับเก็บรายละเอียดการเชื่อมต่อ
    private $host = "localhost";
    private $db_name = "school_evaluation_db";
    private $username = "root";
    private $password = ""; // ປົກກະຕິ XAMPP ຈະຫວ່າງໄວ້
    public $conn;

    // ฟังก์ชันเชื่อมต่อฐานข้อมูล
    public function getConnection() {
        $this->conn = null;

        try {
            // สร้างการเชื่อมต่อด้วย PDO และกำหนดรหัสอักขระเป็น utf8mb4
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            
            // กำหนดเงื่อนไขให้แสดงข้อผิดพลาด (Error Mode) เป็น Exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // ถ้าเชื่อมต่อไม่สำเร็จ ให้แสดงข้อผิดพลาด
            echo "ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>