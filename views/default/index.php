<?php


/* @var $this yii\web\View */
$this->title = $page['title'];

if ($page['url']) {
    $this->registerLinkTag([
        'rel' => "canonical",
        'href' => $page['url'],
    ]);
}

?>

<?= $page['content'] ?>