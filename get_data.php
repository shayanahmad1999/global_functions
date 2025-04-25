//when there is a relationship bettween table one to many then how to get data using outside loop?
<?php

$edits = "SELECT * FROM llx_sale_orders_items WHERE unique_no = ?";
$stmts = $pdo->prepare($edits);
$stmts->execute([$detail]);
$datas = $stmts->fetchAll(PDO::FETCH_OBJ);

if (!empty($datas)) {
    $productIds = array_filter(array_column($datas, 'product_id'));

    if (!empty($productIds)) {
        $productIdsStr = implode(",", $productIds);
        $productQuery = "SELECT rowid, label, entity, quantity, price FROM llx_product WHERE rowid IN ($productIdsStr)";
        $products = $pdo->query($productQuery)->fetchAll(PDO::FETCH_OBJ);
    } else {
        $products = [];
    }
} else {
    $products = [];
}

$productMap = [];
foreach ($products as $prod) {
    $productMap[$prod->rowid] = $prod;
}

?>
<?php foreach ($datas as $val) {
    $product = isset($productMap[$val->product_id]) ? $productMap[$val->product_id] : null;
?>
<tr>
  <td><?= $val->id; ?></td>
  <td><?= $product ? $product->label : $val->manual_product; ?></td>
  <td><input type="number" name="quantity[]" value="<?= $val->quantity; ?>" class="form-control item-quantity" required></td>
  <td><input type="number" step="0.01" name="price[]" value="<?= $val->price; ?>" class="form-control item-price" required></td>
  <td><input type="number" name="total[]" value="<?= $val->total; ?>" class="form-control item-total" readonly></td>
</tr>
<?php } ?>
