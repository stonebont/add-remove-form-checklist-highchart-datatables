<?php 
include 'config.php'; 

// --- LOGIC DATA UNTUK GRAFIK (Sama seperti sebelumnya) ---
$queryCat = $pdo->query("SELECT c.name, COUNT(t.id) as total FROM transactions t JOIN categories c ON t.category_id = c.id GROUP BY c.name");
$catNames = []; $catTotals = [];
while($row = $queryCat->fetch(PDO::FETCH_ASSOC)) {
    $catNames[] = $row['name'];
    $catTotals[] = (int)$row['total'];
}

$querySub = $pdo->query("SELECT s.name, COUNT(t.id) as total FROM transactions t JOIN sub_categories s ON t.sub_category_id = s.id GROUP BY s.name");
$pieData = [];
while($row = $querySub->fetch(PDO::FETCH_ASSOC)) {
    $pieData[] = ['name' => $row['name'], 'y' => (int)$row['total']];
}

// --- QUERY UNTUK DATATABLES ---
$stmtList = $pdo->query("SELECT t.id, c.name as cat_name, s.name as sub_name, t.description, t.entry_date 
                         FROM transactions t 
                         JOIN categories c ON t.category_id = c.id 
                         JOIN sub_categories s ON t.sub_category_id = s.id 
                         ORDER BY t.id DESC");
$listData = $stmtList->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>App Form - Charts - DataTables</title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Buttons CSS -->
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>

<style>
    /* Mengubah warna teks untuk semua tombol DataTables */
    .dt-button {
        color: #FFFFFF; /* Ganti dengan warna teks yang Anda inginkan (misalnya, putih) */
        background-color: #007BFF; /* Opsional: Mengubah warna latar belakang agar teks kontras */
        border: 1px solid #007BFF; /* Opsional: Mengubah warna border */
    }

    /* Hover state (kondisi saat kursor diarahkan ke tombol) */
    .dt-button:hover {
        color: #FFFFFF; /* Pastikan warna teks tetap sama saat di-hover jika diperlukan */
        background-color: #0056b3; /* Warna latar belakang yang sedikit lebih gelap saat di-hover */
    }

    /* Jika Anda menggunakan tombol tertentu (misalnya, tombol 'Export ke Excel') */
    .buttons-excel {
        color: #FFFFFF;
        background-color: #28a745; /* Warna hijau untuk tombol Excel */
    }
</style>	
	

</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    
    <!-- 1. SEKSI GRAFIK -->
    <div class="row mb-4">
        <div class="col-md-6"><div id="chartKategori" class="card shadow p-3"></div></div>
        <div class="col-md-6"><div id="chartSubKategori" class="card shadow p-3"></div></div>
    </div>

    <!-- 2. SEKSI FORM INPUT -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Input Data</h5>
            <button type="button" id="addRow" class="btn btn-light btn-sm">+ Tambah Baris</button>
        </div>
        <div class="card-body">
            <form action="save.php" method="POST">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="dynamicTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>Kategori</th>
                                <th>Sub Kategori</th>
                                <th>Keterangan</th>
                                <th>Tanggal</th>
                                <th width="70">Copy?</th>
                                <th width="50">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="row-item">
                                <td>
                                    <select name="category_id[]" class="form-select category" required>
                                        <option value="">-- Pilih --</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT * FROM categories");
                                        while($row = $stmt->fetch()) {
                                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="sub_category_id[]" class="form-select sub_category" required>
                                        <option value="">-- Pilih Sub --</option>
                                    </select>
                                </td>
                                <td><input type="text" name="description[]" class="form-control" required></td>
                                <td><input type="date" name="entry_date[]" class="form-control" required></td>
                                <td class="text-center"><input type="checkbox" class="form-check-input check-copy"></td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm remove-row">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="submit" class="btn btn-primary shadow-sm">Simpan Semua Baris</button>
            </form>
        </div>
    </div>

    <!-- 3. SEKSI DATATABLES -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Data Tersimpan</h5>
			<a href="export-excel.php" class="btn btn-success btn-sm">Download Excel (Full)</a>
        </div>
        <div class="card-body">
		

		
            <table id="myTable" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listData as $data): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><span class="badge bg-info text-dark"><?= $data['cat_name'] ?></span></td>
                        <td><?= $data['sub_name'] ?></td>
                        <td><?= htmlspecialchars($data['description']) ?></td>
                        <td><?= date('d M Y', strtotime($data['entry_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables dengan Tombol Export
    var table = $('#myTable').DataTable({
        "order": [[ 0, "desc" ]],
        "dom": '<"d-flex justify-content-between"fB>rtip', // Mengatur posisi tombol (B = Buttons)
        "buttons": [
            {
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn btn-outline-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'print',
                text: 'Cetak',
                className: 'btn btn-outline-secondary btn-sm'
            },
			{
                extend: 'pdfHtml5',
                text: 'pdf',
                className: 'btn btn-outline-secondary btn-sm'
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        }
    });

    // Dependency Dropdown
    $(document).on('change', '.category', function() {
        var category_id = $(this).val();
        var currentRow = $(this).closest('tr');
        var subDropdown = currentRow.find('.sub_category');
        if (category_id) {
            $.ajax({
                url: 'get_subcategories.php',
                type: 'POST',
                data: {category_id: category_id},
                success: function(html) { subDropdown.html(html); }
            });
        }
    });

    // Add/Remove Row logic
    $('#addRow').click(function() {
        var lastRow = $('#dynamicTable tbody tr:last');
        var isCopy = lastRow.find('.check-copy').is(':checked');
        var newRow = lastRow.clone();
        if (!isCopy) {
            newRow.find('input[type="text"], input[type="date"]').val('');
            newRow.find('select').val('');
            newRow.find('.sub_category').html('<option value="">-- Pilih Sub --</option>');
        }
        newRow.find('.check-copy').prop('checked', false);
        $('#dynamicTable tbody').append(newRow);
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#dynamicTable tbody tr').length > 1) $(this).closest('tr').remove();
    });

    // Highcharts Logic
    Highcharts.chart('chartKategori', {
        chart: { type: 'column', height: 300 },
        title: { text: 'Data per Kategori' },
        xAxis: { categories: <?= json_encode($catNames) ?> },
        series: [{ name: 'Jumlah', data: <?= json_encode($catTotals) ?> }]
    });

    Highcharts.chart('chartSubKategori', {
        chart: { type: 'pie', height: 300 },
        title: { text: 'Data per Sub-Kategori' },
        series: [{ name: 'Total', colorByPoint: true, data: <?= json_encode($pieData) ?> }]
    });
});
</script>
</body>
</html>