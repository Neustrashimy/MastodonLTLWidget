<?php


// Mastodonの「.env.production」がある場所を入力してください。
$envfile = "/home/mastodon/live/.env.production";


$settings = parse_ini_file($envfile);
if($settings === FALSE){
	die("エラー: 設定ファイルを読み込めませんでした。");
}
try {
	$dbh = new PDO(sprintf("pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
			$settings["DB_HOST"],
			$settings["DB_PORT"],
			$settings["DB_NAME"],
			$settings["DB_USER"],
			$settings["DB_PASS"]
	));
} catch(PDOException $e) {
	die("エラー: 接続に失敗しました。");
}


$sql = <<<EOL
SELECT s.id, s.account_id, a.display_name, a.avatar_file_name, a.username, s.text

FROM statuses as s, accounts as a

WHERE
  s.account_id = a.id
  AND
  s.uri IS NULL
  AND
  s.reply = 'f'
  AND
  s.sensitive = 'f'
  AND
  s.visibility = 0

ORDER BY s.id DESC
LIMIT 20

EOL;

try{
	$sth = $dbh->prepare($sql);
} catch(PDOException $e) {
	die("エラー: 情報の取得に失敗しました。(1)");
}

try{
	$sth -> execute();
} catch(PDOException $e) {
	die("エラー: 情報の取得に失敗しました。(1)");
}


print json_encode($sth->fetchAll(PDO::FETCH_ASSOC));

$sth = null;
$dbh = null;
