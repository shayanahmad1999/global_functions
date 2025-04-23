//date format dd/mm/yyy
if (!function_exists('format')) {
	function format($date)
	{
		$raw_date = new DateTime($date);
		$formated_date = $raw_date->format('d/m/Y');
		return $formated_date;
	}
}
//its uses
format($data->date);


//date format to sql Y-m-d
if (!function_exists('sql_date_format')) {
	function sql_date_format($date)
	{
		if (!$date) return null;

		$raw_date = DateTime::createFromFormat('d/m/Y', $date);

		if ($raw_date && $raw_date->format('d/m/Y') === $date) {
			return $raw_date->format('Y-m-d');
		}
		return null;
	}
}
//its uses
sql_date_format($_POST['date]);


//sort and order data from table column
if (!function_exists('sortData')) {
	function sortData(array $data, string $sortField, string $order, array $sortable): array
	{
		if (!in_array($sortField, $sortable, true)) {
			return $data;
		}
		usort($data, function ($a, $b) use ($sortField, $order) {
			$aVal = getValue($a, $sortField);
			$bVal = getValue($b, $sortField);

			if ($aVal === $bVal) {
				$cmp = 0;
			} elseif ($aVal === null) {
				$cmp = 1;
			} elseif ($bVal === null) {
				$cmp = -1;
			} elseif ($sortField === 'date' && strtotime($aVal) && strtotime($bVal)) {
				$cmp = strtotime($aVal) <=> strtotime($bVal);
			} elseif (is_numeric($aVal) && is_numeric($bVal)) {
				$cmp = $aVal <=> $bVal;
			} else {
				$cmp = strcasecmp($aVal, $bVal);
			}
			return strtolower($order) === 'asc' ? $cmp : -$cmp;
		});
		return $data;
	}
}
if (!function_exists('getValue')) {
	function getValue($item, $field)
	{
		if (is_array($item)) {
			return $item[$field] ?? null;
		} elseif (is_object($item)) {
			return $item->$field ?? null;
		}
		return null;
	}
}
if (!function_exists('sortLink')) {
	function sortLink($column, $label)
	{
		$currentSort = $_GET['sort'] ?? '';
		$currentOrder = $_GET['order'] ?? 'asc';
		$isActive = $currentSort === $column;
		$nextOrder = ($isActive && $currentOrder === 'asc') ? 'desc' : 'asc';
		$arrow = $isActive ? ($currentOrder === 'asc' ? ' ▲' : ' ▼') : '';

		$params = $_GET;
		$params['sort'] = $column;
		$params['order'] = $nextOrder;
		$url = '?' . http_build_query($params);

		return "<a href='" . htmlspecialchars($url) . "'>" . htmlspecialchars($label) . "{$arrow}</a>";
	}
}

//its uses
$sortable = [
     'date',
     'ref_no',
     'payment_term',
     'customer_name',
     'total_amount',
     'remaining_amount',
     'description'
  ];

$sort = $_GET['sort'] ?? 'date';
$order = $_GET['order'] ?? 'asc';

$data = sortData($data, $sort, $order, $sortable);

<th><?= sortLink('date', 'Date') ?></th>
