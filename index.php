<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>График изменения баланса</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h1 class="mb-4">Загрузка отчета сделок</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <input type="file" name="report" class="form-control" accept=".html,.htm" required>
        </div>
        <button type="submit" class="btn btn-primary">Загрузить и построить график</button>
    </form>
    
    <? if (isset($_GET['success'])): ?>
        <canvas id="balanceChart" width="1000" height="400"></canvas>
        <script src="data.js"></script>
        <script>
            //отрисовка чарта
            const ctx = document.getElementById('balanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Баланс',
                        data: chartData.balances,
                        borderColor: 'rgb(75, 192, 192)',
                        fill: false,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: { enabled: true },
                        legend: { display: true }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Дата/время сделки' },
                            ticks: { autoSkip: true, maxRotation: 60, minRotation: 45 }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Баланс ($)' }
                        }
                    }
                }
            });
        </script>
    <? elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Ошибка загрузки или чтения файла</div>
    <? endif; ?>
</body>
</html>
