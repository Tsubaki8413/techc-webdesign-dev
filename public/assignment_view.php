<?php

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if(!isset($_GET['id'])){
  return;
}

$id = intval($_GET['id']);

$select_sth = $dbh->prepare('SELECT * FROM assignment_entries WHERE id = :id');
$select_sth->bindParam(':id', $id, PDO::PARAM_INT);
$select_sth->execute();
$entry = $select_sth->fetch();

if(!$entry){
  return;
}

?>


<a href="./first_assignment.php">一覧に戻る</a>

<dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
  <dt>ID</dt>
  <dd><?= $entry['id'] ?></dd>
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
