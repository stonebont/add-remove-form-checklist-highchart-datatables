<?php 
include 'config.php'; 

// --- LOGIC AMBIL DATA UNTUK GRAFIK ---

// 1. Data untuk Column Chart (Jumlah per Kategori)
$queryCat = $pdo->query("SELECT c.name, COUNT(t.id) as total 
                         FROM transactions t 
                         JOIN categories c ON t.category_id = c.id 
                         GROUP BY c.name");
$catNames = [];
$catTotals = [];
while($row = $queryCat->fetch(PDO::FETCH_ASSOC)) {
    $catNames[] = $row['name'];
    $catTotals[] = (int)$row['total'];
}

// 2. Data untuk Pie Chart (Distribusi Sub Kategori)
$querySub = $pdo->query("SELECT s.name, COUNT(t.id) as total 
                         FROM transactions t 
                         JOIN sub_categories s ON t.sub_category_id = s.id 
                         GROUP BY s.name");
$pieData = [];
while($row = $querySub->fetch(PDO::FETCH_ASSOC)) {
    $pieData[] = [
        'name' => $row['name'],
        'y' => (int)$row['total']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form & Highcharts</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Library Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <!-- SEKSI GRAFIK -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <div id="chartKategori"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <div id="chartSubKategori"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKSI FORM (Sama seperti sebelumnya) -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Form Input Dinamis</h4>
        </div>
        <div class="card-body">
            <form action="save.php" method="POST">
                <table class="table table-bordered" id="dynamicTable">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Sub Kategori</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>Copy?</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="row-item">
                            <td>
                                <select name="category_id[]" class="form-control category" required>
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
                                <select name="sub_category_id[]" class="form-control sub_category" required>
                                    <option value="">-- Pilih Sub --</option>
                                </select>
                            </td>
                            <td><input type="text" name="description[]" class="form-control" required></td>
                            <td><input type="date" name="entry_date[]" class="form-control" required></td>
                            <td class="text-center"><input type="checkbox" class="form-check-input check-copy"></td>
                            <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="addRow" class="btn btn-success">Tambah Baris</button>
                <hr>
                <button type="submit" name="submit" class="btn btn-primary w-100">Simpan Semua Data</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // --- SCRIPT FORM (Sama seperti sebelumnya) ---
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

    // --- SCRIPT HIGHCHARTS ---

    // 1. Grafik Column (Kategori)
    Highcharts.chart('chartKategori', {
        chart: { type: 'column' },
        title: { text: 'Total Data per Kategori' },
        xAxis: { categories: <?php echo json_encode($catNames); ?> },
        yAxis: { title: { text: 'Jumlah Data' } },
        series: [{
            name: 'Kategori',
            data: <?php echo json_encode($catTotals); ?>,
            colorByPoint: true
        }]
    });

    // 2. Grafik Pie (Sub Kategori)
    Highcharts.chart('chartSubKategori', {
        chart: { type: 'pie' },
        title: { text: 'Distribusi Sub-Kategori' },
        tooltip: { pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' },
        accessibility: { point: { valueSuffix: '%' } },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: { enabled: true, format: '<b>{point.name}</b>: {point.y}' }
            }
        },
        series: [{
            name: 'Total',
            colorByPoint: true,
            data: <?php echo json_encode($pieData); ?>
        }]
    });
});
</script>
</body>
</html>