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
    foreach ($_FILES['attachment']['name'] as $key => $originalName) {
        if ($_FILES['attachment']['error'][$key] == 0) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('file_', true) . '.' . $extension;
            $targetFile = $uploadDir . $uniqueName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'][$key], $targetFile)) {
                $stmt = $pdo->prepare("INSERT INTO sale_invoice_attachments (invoice_id, original_name, unique_name) VALUES (?, ?, ?)");
                $stmt->execute([$invoice_id, $originalName, $uniqueName]);
            }
        }
    }
    echo "Files uploaded and saved in database!";
}
?>

