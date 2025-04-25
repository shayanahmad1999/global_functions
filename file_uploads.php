//single file uploads
<input type="file" name="attachment" class="form-control">

<?php
$invoice_id = $pdo->lastInsertId();

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $uploadDir = DOL_URL_ROOT . '/accountancy/sale-invoice/attachment/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES['attachment']['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $uniqueName = uniqid('file_', true) . '.' . $extension;
    $targetFile = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
        // Insert into database
        $pdo = new PDO("mysql:host=localhost;dbname=YOUR_DB;charset=utf8", "USERNAME", "PASSWORD");
        $stmt = $pdo->prepare("INSERT INTO sale_invoice_attachments (invoice_id, original_name, unique_name) VALUES (?, ?, ?)");
        $stmt->execute([$invoice_id, $originalName, $uniqueName]); // $invoice_id comes from your code/context

        echo "File uploaded and saved in database!";
    } else {
        echo "Error uploading file.";
    }
}
?>

//multiple file uploads
<input type="file" name="attachment[]" class="form-control" multiple>

<?php
$invoice_id = $pdo->lastInsertId();

if (!empty($_FILES['attachment']['name'][0])) {
    $uploadDir = DOL_URL_ROOT . '/accountancy/sale-invoice/attachment/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $pdo = new PDO("mysql:host=localhost;dbname=YOUR_DB;charset=utf8", "USERNAME", "PASSWORD");
    foreach ($_FILES['attachment']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['attachment']['error'][$key] == 0) {
            $originalName = $_FILES['attachment']['name'][$key];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('file_', true) . '.' . $extension;
            $targetFile = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $stmt = $pdo->prepare("INSERT INTO sale_invoice_attachments (invoice_id, original_name, unique_name) VALUES (?, ?, ?)");
                $stmt->execute([$invoice_id, $originalName, $uniqueName]);
            }
        }
    }
    echo "Files uploaded and saved in database!";
}
?>

//edit and update the files
<?php
    if (!empty($data->id)) {
    $fileQuery = "SELECT * FROM llx_sale_order_attachments WHERE sale_order_id IN ($data->id)";
    $files = $pdo->query($fileQuery)->fetchAll(PDO::FETCH_OBJ);
    } else {
        $files = [];
    }    
?>
<div class="col-md-4">
    <div class="form-group">
        <label>Attachment</label>
        <input type="file" name="attachment[]" class="form-control" multiple>
    </div>
    <?php
        if ($files) {
            foreach ($files as $file) {
        ?>
                <input type="hidden" name="old_attachment[]" value="<?php echo $file->unique_name; ?>">
        <?php }
        } else {
            echo "No Attachments";
        } 
    ?>
</div>

//update.php

<?php
    if ($lastInsertId) {
        // 6.a. Remove old attachments if not in old_attachment[]
        // Fetch all current attachments for this sale_order_id
        $stmt = $pdo->prepare("SELECT unique_name FROM llx_sale_order_attachments WHERE sale_order_id = ?");
        $stmt->execute([$lastInsertId]);
        $existing_attachments = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $old_attachments = isset($_POST['old_attachment']) ? $_POST['old_attachment'] : [];

        // Delete attachments that are not in old_attachment[]
        foreach ($existing_attachments as $filename) {
            if (!in_array($filename, $old_attachments)) {
                // Delete from DB
                $del_stmt = $pdo->prepare("DELETE FROM llx_sale_order_attachments WHERE sale_order_id = ? AND unique_name = ?");
                $del_stmt->execute([$lastInsertId, $filename]);

                // Also remove the physical file if you want
                $filepath = __DIR__ . '/../attachment/' . $filename;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }

        // 6.b. Add new attachments
        if (!empty($_FILES['attachment']['name'][0])) {
            $uploadDir = __DIR__ . '/../attachment/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            foreach ($_FILES['attachment']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['attachment']['error'][$key] == 0) {
                    $originalName = $_FILES['attachment']['name'][$key];
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $uniqueName = uniqid('file_', true) . '.' . $extension;
                    $targetFile = $uploadDir . $uniqueName;
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $stmt = $pdo->prepare("INSERT INTO llx_sale_order_attachments (sale_order_id, original_name, unique_name) VALUES (?, ?, ?)");
                        $stmt->execute([$lastInsertId, $originalName, $uniqueName]);
                    }
                }
            }
        }
    }
?>

