<?php
header ("Content-Type: text/html; charset=utf-8");

include_once 'config.php';
require_once 'functions.php';

if (!isAuthorizedUser()) {
    echo "<a href='login.php'>Войдите на сайт</a>";
    die;
}

$sql = "SELECT id FROM user WHERE login = ?";
        $sth = $pdo->prepare($sql);
        $sth->execute([
            isAuthorizedUser()
        ]);

$userId = $sth->fetchColumn();

$action = !empty($_GET['action']) ? $_GET['action'] : null;
$orderBy = "date_added";

$sortVariants = ['date_added', 'description', 'is_done'];

if (isset($_POST['sort']) && !empty($_POST['sort_by']) && in_array($_POST['sort_by'], $sortVariants)) {
    $orderBy = $_POST['sort_by'];
}

if (!isset($_GET['id']) && isset($_POST['save']) && !empty($_POST['description'])) {
    $sth = $pdo->prepare("INSERT INTO task (user_id, description, date_added) VALUES (?, ?, NOW())");
    $sth->execute([
        $userId,
        $_POST['description']
    ]);

    redirectToHome();
}

if (!empty($action) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($action == 'done') {
        $sth=$pdo->prepare("UPDATE task SET is_done = 1 WHERE id = ? AND (user_id = ? OR assigned_user_id = ?)");
        $sth->execute([$id, $userId, $userId]);

         redirectToHome();
    }

    if ($action == 'delete') {
      $sth=$pdo->prepare("DELETE FROM task WHERE id = ? AND user_id = ?");
        $sth->execute([$id, $userId]);

    }

    if ($action == 'edit') {
        if (!empty($_POST['description'])) {
            $sth = $pdo->prepare("UPDATE task SET description = ? WHERE id = ? AND user_id = ?");
            $sth->execute([
                $_POST['description'],
                $id,
                $userId
          ]);

          redirectToHome();
        }
        $sql = "SELECT description FROM tasks WHERE id = ?";
        $sth = $pdo->prepare($sql);
        $sth->execute([$id]);

        $_POST['description'] = $sth->fetchColumn();
    }
}

if (!empty($_POST['assign']) && !empty($_POST['assigned_user_id'])) {
    $formData = explode("_", $_POST['assigned_user_id']);
    $assignedUserId = (int)$formData[1];
    $taskId = (int)$formData[3];

    if (!empty($userId) && !empty($taskId)) {
        $sth = $pdo->prepare("UPDATE task SET assigned_user_id = ? WHERE id = ? AND user_id = ?");
        $sth->execute([
            $assignedUserId,
            $taskId,
            $userId
        ]);

        redirectToHome();
    }
}

$sql = "SELECT t.*, u.login, u2.login author
        FROM task t
        LEFT JOIN user u ON t.assigned_user_id = u.id
        LEFT JOIN user u2 ON t.user_id = u2.id
        WHERE user_id = ?
        ORDER BY $orderBy";
$sth = $pdo->prepare($sql);
$sth->execute([
    $userId
]);

$myTasks = $sth->fetchAll();

$sql = "SELECT t.*, u.login, u2.login author
        FROM task t
        LEFT JOIN user u ON t.assigned_user_id = u.id
        LEFT JOIN user u2 ON t.user_id = u2.id
        WHERE assigned_user_id = ? ORDER BY $orderBy";
$sth = $pdo->prepare($sql);
$sth->execute([
    $userId
]);

$myAssignedTasks = $sth->fetchAll();

$sth = $pdo->prepare("SELECT * FROM user WHERE id <> ?");
$sth->execute([
    $userId
]);

$userList = $sth->fetchAll();
$user = [];

foreach ($userList as $item) {
    $user[$item['id']] = $item['login'];
}

?>
<!DOCTYPE html>
<html>
    <head>
		  	<meta charset="utf-8">
		  	<title>Задания</title>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }

            table td, table th {
                border: 1px solid #ccc;
                padding: 5px;
            }

            table th {
                background: #eee;
            }

            form {
                margin: 0;
            }
        </style>

    </head>
		<body>

    <h1>Здравствуйте, <?=$_SESSION['login']?>! Вот ваш список дел:</h1>

<div style="float: left; margin-bottom: 20px;">
    <form method="POST">
        <input type="text" name="description" placeholder="Описание задачи" value="<?=$_POST['description']?>">
        <input type="submit" name="save" value="<?php echo ($action == 'edit' ? 'Сохранить' : 'Добавить') ?>">
    </form>
</div>
<div style="float: left; margin-left: 20px;">
    <form method="POST">
        <label for="sort">Сортировать по:</label>
        <select name="sort_by">
            <option value="date_created">Дате добавления</option>
            <option value="is_done">Статусу</option>
            <option value="description">Описанию</option>
        </select>
        <input type="submit" name="sort" value="Отсортировать">
    </form>
</div>
<div style="clear: both"></div>

<?php printTasks($myTasks, $userId, $user); ?>

<p><strong>Также, посмотрите, что от Вас требуют другие люди:</strong></p>

<?php printTasks($myAssignedTasks, $userId); ?>

<p><a href="logout.php">Выход</a></p>

</body>
</html>
