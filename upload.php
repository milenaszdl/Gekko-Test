<?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report']) && $_FILES['report']['error'] === 0) {
    $dom = new DOMDocument(); // дом для разбора html
    libxml_use_internal_errors(true);

    //загруз файла
    $html = file_get_contents($_FILES['report']['tmp_name']);
    $dom->loadHTML($html);
    libxml_clear_errors();

    //поиск строк
    $xpath = new DOMXPath($dom);
    $rows = $xpath->query('//tr');

    $balance = 0;
    $balances = [];
    $labels = [];

    //перебор
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        if ($cells->length < 14) continue;

        //значение прибыли и дата закрытия сделки
        $profitText = trim($cells->item(13)->nodeValue);
        $closeTime = trim($cells->item(8)->nodeValue);

        $profitText = str_replace([',', ' '], '', $profitText);
        //если профит является ичслом
        if (is_numeric($profitText)) {
            $profit = (float)$profitText;
            $balance += $profit;
            if ($balance < 0) $balance = 0;
            $balances[] = round($balance, 2);
            $labels[] = $closeTime !== '' ? $closeTime : count($labels) + 1;
        }
    }

    if (count($balances) > 0) {
        $data = [
            'labels' => $labels,
            'balances' => $balances,
        ];
        //запись данных в дату 
        file_put_contents("data.js", "const chartData = " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";");
        header("Location: index.php?success=1");
        exit;
    } else {
        header("Location: index.php?error=1");
        exit;
    }
} else {
    header("Location: index.php?error=1");
    exit;
}
