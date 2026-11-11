<?php
require_once 'config.php';
$table = "Teacher";
$result = $conn->query("SHOW COLUMNS FROM $table");
if (!$result) {
    $table = "teacher";
    $result = $conn->query("SHOW COLUMNS FROM $table");
}

if ($result) {
    echo "Columns in $table:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Table not found.";
}
?>