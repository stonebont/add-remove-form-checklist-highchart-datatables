<?php
include 'config.php';

if (isset($_POST['category_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM sub_categories WHERE category_id = ?");
    $stmt->execute([$_POST['category_id']]);
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<option value="">-- Pilih Sub Kategori --</option>';
    foreach ($subs as $row) {
        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
    }
}
?>