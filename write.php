<?php
session_start();
// エラーレポートを表示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// POSTデータ取得とデータ確認
var_dump("write.php", $_POST);

if (isset($_POST['label']) &&isset($_POST['starttime']) && isset($_POST['y']) && isset($_POST['duration']) && isset($_POST['colorR']) && isset($_POST['colorG']) && isset($_POST['colorB'])) {
  echo ("write.php");
  // POSTデータの変数代入
  $label = $_POST['label'];
  $starttime = $_POST['starttime']; // 必要に応じて変換
  $y = $_POST['y'];
  $width = $_POST['duration']; // duration を width として使用する場合
  $height = 100; // 高さは固定値か、別の値を設定
  $colorR = $_POST['colorR'];
  $colorG = $_POST['colorG'];
  $colorB = $_POST['colorB'];
} else {
  exit("必要なデータが送信されていません");
}

// デバッグ用のログ出力
error_log("write.phpで受け取ったデータ: label={$label}, starttime={$starttime}, y={$y}, duration={$duration}, colorR={$colorR}, colorG={$colorG}, colorB={$colorB}");


// DB接続
include("funcs.php");
$pdo = db_conn();

// データ登録SQL作成
$sql = "INSERT INTO gs_1on1_table (id, label, x, y, width, height, colorR, colorG, colorB) 
        VALUES (NULL, :label, :x, :y, :width, :height, :colorR, :colorG, :colorB);";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':label', $label, PDO::PARAM_STR);
$stmt->bindValue(':x', $starttime, PDO::PARAM_INT);
$stmt->bindValue(':y', $y, PDO::PARAM_INT);
$stmt->bindValue(':width', $width, PDO::PARAM_INT);
$stmt->bindValue(':height', $height, PDO::PARAM_INT);
$stmt->bindValue(':colorR', $colorR, PDO::PARAM_INT);
$stmt->bindValue(':colorG', $colorG, PDO::PARAM_INT);
$stmt->bindValue(':colorB', $colorB, PDO::PARAM_INT);
$status = $stmt->execute();

// データ登録処理後の確認
if($status == false){
  $error = $stmt->errorInfo();
  exit("SQL_ERROR: " . $error[2]);
} else {
  exit("データが正常に登録されました");
}
?>
