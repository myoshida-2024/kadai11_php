<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>TEST</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<script
  src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js"
  integrity="sha512-VMsZqo0ar06BMtg0tPsdgRADvl0kDHpTbugCBBrL55KmucH6hP9zWdLIWY//OTfMnzz6xWQRxQqsUFefwHuHyg=="
  crossorigin="anonymous"></script>
<script
  src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@next/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.css">
</head>
<body>
<div style="width:800px">
  <canvas id="mychart"></canvas>
</div>

</body>
</html>


<?php
session_start();

include("funcs.php");
$pdo = db_conn();

// ---------- ひと毎の会話量

// labelごとのwidthの合計を格納するための配列
$labelWidths = [];

// データベースから一行ずつデータを取得
$sql = "SELECT label, width FROM gs_1on1_table"; // テーブル名を適切に置き換えてください
$stmt = $pdo->prepare($sql);
$stmt->execute();

// 各行のデータをループ処理
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $label = $row['label'];
    $width = $row['width'];

    // labelごとのwidthを累積
    if (!isset($labelWidths[$label])) {
        $labelWidths[$label] = 0; // 初回は0で初期化
    }
    $labelWidths[$label] += $width; // widthの合計を計算
}

// PHPからJavaScriptにデータを渡すためのJSONエンコード
$labelWidthsJson = json_encode($labelWidths);

// 結果の出力
// echo "LabelごとのWidthの合計: <br>";
// foreach ($labelWidths as $label => $totalWidth) {
    // echo "Label: $label, 合計 Width: $totalWidth <br>";
// }

?>
<script>

   // PHPからのデータをJavaScriptで利用できるようにする
   const labelDurations = <?php echo $labelWidthsJson; ?>;
        console.log("取得したデータ：", labelDurations);

        // データを配列に変換してChart.jsで使用
        const labels = Object.keys(labelDurations);
        const data = Object.values(labelDurations);

 // ここに chart.js のコードを追加して、データを表示
 var ctx2 = document.getElementById('mychart');
 ctx2.width = 300;
 ctx2.height = 300;
 var myChart = new Chart(ctx2, {
   type: 'pie',
   data: {
     labels: labels,
     datasets: [{
       data: data,
       backgroundColor: [
         'rgba(200, 0, 0, 0.6)',
         'rgba(0, 0, 200, 0.6)'
     ]}],
       
   },
   options: {
        responsive: false // サイズを固定したい場合
    }
 });
 </script>
 <?php
// ーーーーーーー会話のグラフ


// データベースから情報を取得
$sql = "SELECT x, y, width, height, colorR, colorG, colorB FROM gs_1on1_table";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 取得したデータをJavaScriptで利用できるようにJSONエンコード
$rectangleData = json_encode($results);

// 最初の四角形の x 値を取得して min 値に設定
$firstX = isset($results[0]['x']) ? $results[0]['x'] * 0.02 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rectangle Drawing</title>
</head>
<body>
    <!-- キャンバスとスライダー -->
    <canvas id="myCanvas" width="800" height="350" style="border:1px solid #000000;"></canvas>
    <input type="range" id="slider" min="<?php echo $firstX; ?>" max="5000" value="<?php echo $firstX; ?>" style="width: 800px;" />

    <script>
        // PHPから取得したデータをJavaScriptで利用
        const rectangleData = <?php echo $rectangleData; ?>;
        const firstX = <?php echo json_encode($firstX); ?>;

        const canvas = document.getElementById('myCanvas');
        const ctx3 = canvas.getContext('2d');
        const slider = document.getElementById('slider');
        const rate = 0.02;

        // drawRectangle関数の定義
        function drawRectangle(x, y, width, height, colorR, colorG, colorB) {
            ctx3.fillStyle = `rgb(${colorR}, ${colorG}, ${colorB})`;
            ctx3.fillRect(x, y, width, height);
        }

        // スライダーの変化に応じて再描画
        function redrawRectangles(offset) {
            ctx3.clearRect(0, 0, canvas.width, canvas.height); // キャンバスのクリア
            rectangleData.forEach(item => {
                const { x, y, width, height, colorR, colorG, colorB } = item;
                drawRectangle((x * rate) - offset, y, width * rate, height, colorR, colorG, colorB);
            });
        }

        // 初期描画
        redrawRectangles(firstX);

        // スライダーのイベントリスナー
        slider.addEventListener('input', (event) => {
            const offset = event.target.value;
            redrawRectangles(offset);
        });
    </script>
</body>
</html>
