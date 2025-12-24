<?php
include 'config.php';

if (isset($_POST['submit'])) {
    $category_ids = $_POST['category_id'];
    $sub_category_ids = $_POST['sub_category_id'];
    $descriptions = $_POST['description'];
    $entry_dates = $_POST['entry_date'];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO transactions (category_id, sub_category_id, description, entry_date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($category_ids as $key => $val) {
            $stmt->execute([
                $category_ids[$key],
                $sub_category_ids[$key],
                $descriptions[$key],
                $entry_dates[$key]
            ]);
        }

        $pdo->commit();
        echo "<script>alert('Data berhasil disimpan!'); window.location='index.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>