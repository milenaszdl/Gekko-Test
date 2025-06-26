<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report']) && $_FILES['report']['error'] === 0) {
    // Загружаем и парсим HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $html = file_get_contents($_FILES['report']['tmp_name']);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $rows = $xpath->query('//tr');

    $balance = 0;
    $balances = [];
    $labels = [];

    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        // У нас хотя бы две ячейки: время и profit
        if ($cells->length < 2) {
            continue;
        }

        // profit — всегда в последней <td>
        $lastIndex = $cells->length - 1;
        $profitText = trim($cells->item($lastIndex)->nodeValue);
        // чистим пробелы и запятые
        $profitText = str_replace([',', ' '], '', $profitText);

        // метка: закрытие сделки (ячейка 8), иначе открытие (ячейка 1)
        if ($cells->length >= 9) {
            $timeLabel = trim($cells->item(8)->nodeValue);
        } else {
            $timeLabel = trim($cells->item(1)->nodeValue);
        }
        // если пусто — просто порядковый номер
        if ($timeLabel === '') {
            $timeLabel = count($labels) + 1;
        }

        // учитываем только числовые profit
        if (is_numeric($profitText)) {
            $profit = (float)$profitText;
            $balance += $profit;
            // баланс не может быть отрицательным
            if ($balance < 0) {
                $balance = 0;
            }
            $balances[] = round($balance, 2);
            $labels[] = $timeLabel;
        }
    }

    if (!empty($balances)) {
        $data = [
            'labels'   => $labels,
            'balances' => $balances,
        ];
        // сохраняем в JS-файл для графика
        file_put_contents(
            "data.js",
            "const chartData = " .
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
            ";"
        );
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
?>
