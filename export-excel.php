<?php
include 'config.php';

// Nama file yang akan dihasilkan
$filename = "Laporan_Data_" . date('Ymd_His') . ".xls";

// Header untuk memaksa download file excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil data dari database
$stmt = $pdo->query("SELECT t.id, c.name as cat_name, s.name as sub_name, t.description, t.entry_date 
                     FROM transactions t 
                     JOIN categories c ON t.category_id = c.id 
                     JOIN sub_categories s ON t.sub_category_id = s.id 
                     ORDER BY t.id DESC");
?>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #f2f2f2;">ID</th>
            <th style="background-color: #f2f2f2;">Kategori</th>
            <th style="background-color: #f2f2f2;">Sub Kategori</th>
            <th style="background-color: #f2f2f2;">Keterangan</th>
            <th style="background-color: #f2f2f2;">Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['cat_name']; ?></td>
            <td><?php echo $row['sub_name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['entry_date']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>