<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form PHP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
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
                            <th>Keterangan (Text)</th>
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
                            <td><input type="text" name="description[]" class="form-control" placeholder="Isi teks..." required></td>
                            <td><input type="date" name="entry_date[]" class="form-control" required></td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input check-copy">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-row">Hapus</button>
                            </td>
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
    // Fungsi untuk memuat Sub-Kategori (Dependency)
    $(document).on('change', '.category', function() {
        var category_id = $(this).val();
        var currentRow = $(this).closest('tr');
        var subDropdown = currentRow.find('.sub_category');

        if (category_id) {
            $.ajax({
                url: 'get_subcategories.php',
                type: 'POST',
                data: {category_id: category_id},
                success: function(html) {
                    subDropdown.html(html);
                }
            });
        } else {
            subDropdown.html('<option value="">-- Pilih Sub --</option>');
        }
    });

    // Fungsi Tambah Baris
    $('#addRow').click(function() {
        var lastRow = $('#dynamicTable tbody tr:last');
        var isCopy = lastRow.find('.check-copy').is(':checked');
        
        var newRow = lastRow.clone();
        
        // Reset ID/Values jika tidak dicopy
        if (!isCopy) {
            newRow.find('input[type="text"]').val('');
            newRow.find('input[type="date"]').val('');
            newRow.find('select').val('');
            newRow.find('.sub_category').html('<option value="">-- Pilih Sub --</option>');
        } else {
            // Jika dicopy, pastikan sub-category yang sudah dipilih ikut terbawa
            var selectedSub = lastRow.find('.sub_category').val();
            setTimeout(function() {
                newRow.find('.sub_category').val(selectedSub);
            }, 100);
        }

        newRow.find('.check-copy').prop('checked', false); // Uncheck box copy di baris baru
        $('#dynamicTable tbody').append(newRow);
    });

    // Fungsi Hapus Baris
    $(document).on('click', '.remove-row', function() {
        if ($('#dynamicTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert("Minimal harus ada satu baris!");
        }
    });
});
</script>

</body>
</html>