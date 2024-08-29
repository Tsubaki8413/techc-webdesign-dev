<?php

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// 掲示板部分
if(isset($_POST['body'])){
	$image_filename = null;
  
	if(isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])){
		if(preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1){
 			header("HTTP/1.1 302 Found");
			header("Location: ./first_assignment.php");
		}

		$pathinfo = pathinfo($_FILES['image']['name']);
		$extension = $pathinfo['extension'];

		$image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
		$filepath =  '/var/www/upload/image/' . $image_filename;
		move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
	}

	$insert_sth = $dbh->prepare("INSERT INTO assignment_entries (body, image_filename) VALUES (:body, :image_filename)");
	$insert_sth->execute([
		':body' => $_POST['body'],
		':image_filename' => $image_filename,
	]);

	header("HTTP/1.1 302 Found");
	header("Location: ./first_assignment.php");
	return;
}

// ページ部分
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$count_per_page = 10;

$skip_count = $count_per_page * ($page - 1);

$count_sth = $dbh->prepare('SELECT COUNT(*) FROM assignment_entries;');
$count_sth->execute();
$count_all = $count_sth->fetchColumn();
if($skip_count >= $count_all){
  print('このページは存在しません。');
  return;
}

$select_sth = $dbh->prepare('SELECT * FROM assignment_entries ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count');
$select_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
$select_sth->bindParam(':skip_count', $skip_count, PDO::PARAM_INT);
$select_sth->execute();

?>


<head>
  <title>前期最終課題</title>
</head>

<form method="POST" action="./first_assignment.php" enctype="multipart/form-data">
  <textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button type="submit">送信</button>
</form>

<hr>

<div style="width: 100%; text-align: center; padding-bottom: 1em; border-bottom: 1px solid #ccc; margin-bottom: 0.5em">
	<?= $page ?>ページ目
	(全 <?= floor($count_all / $count_per_page) + 1 ?>ページ中)

	<div style="display: flex; justify-content: space-between; margin-bottom: 2em;">
 		<div>
 			<?php if($page > 1): ?>
 				<a href="?page=<?= $page - 1 ?>">前のページ</a>
 			<?php endif; ?>
 		</div>
 		<div>
 			<?php if($count_all > $page * $count_per_page): ?>
 				<a href="?page=<?= $page + 1 ?>">次のページ</a>
 			<?php endif; ?>
 		</div>
	</div>
</div>

<?php foreach($select_sth as $entry): ?>
	<dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
	  <dt>ID</dt>
		<dd><a href="./assignment_view.php?id=<?= $entry['id'] ?>"><?= $entry['id'] ?></a></dd>
  	<dt>日時</dt>
  	<dd><?= $entry['created_at'] ?></dd>
  	<dt>内容</dt>
  	<dd>
  		<?= nl2br(htmlspecialchars($entry['body'])) ?>
  		<?php if(!empty($entry['image_filename'])): ?>
  			<div>
  				<img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
  			</div>
  		<?php endif; ?>
  	</dd>
  </dl>
<?php endforeach ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  	const imageInput = document.getElementById("imageInput");
  	imageInput.addEventListener("change", () => {
  		if(imageInput.files.length < 1){
  			return;
  		}

	 		if(imageInput.files[0].size > 5 * 1024 * 1024){
	 			alert("5MB以下のファイルを選択してください。");
 	 			imageInput.value = "";
	 		}
 	 	});
  });
</script>
