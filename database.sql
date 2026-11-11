-- ============================================================
-- 1. ສ້າງຖານຂໍ້ມູນ ແລະ ເລືອກໃຊ້ງານ
-- ============================================================
CREATE DATABASE IF NOT EXISTS school_evaluation_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_evaluation_db;

-- ============================================================
-- 2. ສ້າງຕາຕະລາງທີ່ບໍ່ມີ Foreign Key ກ່ອນ (ຕາຕະລາງຫຼັກ)
-- ============================================================

-- 1.1.1 ຕາຕະລາງຂໍ້ມູນພາກຮຽນ (Session)
CREATE TABLE `Session` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `session_name` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນພາກຮຽນ';

-- 1.1.2 ຕາຕະລາງຂໍ້ມູນອາຈານ (Teacher)
CREATE TABLE `Teacher` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `firstname` VARCHAR(30) NOT NULL,
    `lastname` VARCHAR(30) NOT NULL,
    `email` VARCHAR(50) NOT NULL UNIQUE, -- ເພີ່ມ UNIQUE ບໍ່ໃຫ້ອີເມວຊ້ຳກັນ
    `password` VARCHAR(255) NOT NULL,    -- ປັບເປັນ 255 ເພື່ອຮອງຮັບການ Hash ລະຫັດຜ່ານເພື່ອຄວາມປອດໄພ
    `role` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນອາຈານ';

-- 1.1.3 ຕາຕະລາງຂໍ້ມູນສາຂາຮຽນ (Major)
CREATE TABLE `Major` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `major_name` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນສາຂາຮຽນ';

-- 1.1.4 ຕາຕະລາງຂໍ້ມູນຫ້ອງຮຽນ (Classroom)
CREATE TABLE `Classroom` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `classroom_name` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນຫ້ອງຮຽນ';

-- 1.1.6 ຕາຕະລາງຂໍ້ມູນຄຳຖາມ (Question)
CREATE TABLE `Question` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `question_title` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຄຳຖາມ';

-- 1.1.8 ຕາຕະລາງຂໍ້ມູນແບບສອບຖາມ (Questionnaire)
CREATE TABLE `Questionnaire` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `description` VARCHAR(30) NOT NULL,
    `title` VARCHAR(100) NOT NULL, -- ເພີ່ມປະເພດຂໍ້ມູນ VARCHAR ທີ່ຕົກຫຼົ່ນໄປ
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນແບບສອບຖາມ';


-- ============================================================
-- 3. ສ້າງຕາຕະລາງທີ່ມີ Foreign Key (ຕາຕະລາງທີ່ຕ້ອງເຊື່ອມໂຍງ)
-- ============================================================

-- 1.1.7 ຕາຕະລາງຂໍ້ມູນຕາຕະລາງຮຽນ (Teaching)
CREATE TABLE `Teaching` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `academic` VARCHAR(30) NOT NULL,
    `term` VARCHAR(20) NOT NULL, -- ເພີ່ມປະເພດຂໍ້ມູນທີ່ຕົກຫຼົ່ນໄປ
    `Major_id` INT(11) NOT NULL,
    `Session_id` INT(11) NOT NULL,
    `Classroom_id` INT(11) NOT NULL,
    `Teacher_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    
    -- ກຳນົດຄວາມສຳພັນ Foreign Key
    CONSTRAINT `FK_Teaching_Major` FOREIGN KEY (`Major_id`) REFERENCES `Major` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_Teaching_Session` FOREIGN KEY (`Session_id`) REFERENCES `Session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_Teaching_Classroom` FOREIGN KEY (`Classroom_id`) REFERENCES `Classroom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_Teaching_Teacher` FOREIGN KEY (`Teacher_id`) REFERENCES `Teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຂໍ້ມູນຕາຕະລາງຮຽນ';

-- 1.1.5 ຕາຕະລາງຂໍ້ມູນຄຳຕອບ (Answer)
CREATE TABLE `Answer` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `score` INT(10) NOT NULL,
    `Question_id` INT(11) NOT NULL,        -- ໃສ່ປະເພດຂໍ້ມູນໃຫ້ຕົງກັບຕາຕະລາງ Question
    `Questionnaire_id` INT(11) NOT NULL,   -- ໃສ່ປະເພດຂໍ້ມູນໃຫ້ຕົງກັບຕາຕະລາງ Questionnaire
    PRIMARY KEY (`id`),
    
    -- ກຳນົດຄວາມສຳພັນ Foreign Key
    CONSTRAINT `FK_Answer_Question` FOREIGN KEY (`Question_id`) REFERENCES `Question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_Answer_Questionnaire` FOREIGN KEY (`Questionnaire_id`) REFERENCES `Questionnaire` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ຕາຕະລາງຂໍ້ມູນຄໍາຕອບ';