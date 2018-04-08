<?php
header ("Content-Type: text/html; charset=utf-8");

include_once 'config.php';
require_once 'functions.php';

$message = "Введите данные для регистрации или войдите, если уже регистрировались:";

if (isset($_POST['register'])) {
    if (setRegistry()) {
        $md5Password = md5(getPostParam('password'));

        $sth = $pdo->prepare("SELECT login FROM user WHERE login = ?");
        $sth->execute([
            getPostParam('login')
        ]);

        if (empty($sth->fetchColumn())) {
            $sth = $pdo->prepare("INSERT INTO user (login, password) VALUES (?, ?)");
            $sth->execute([
                getPostParam('login'),
                $md5Password
            ]);

            login(getPostParam('login'));
        } else {
            $message = "Такой пользователь уже существует в базе данных.";
        }
    } else {
        $message = "Ошибка регистрации. Введите все необхдоимые данные.";
    }
}

if (isset($_POST['sign_in'])) {
    if (setRegistry()) {
        $md5Password = md5(getPostParam('password'));

        $sth = $pdo->prepare("SELECT login FROM user WHERE login = ? AND password = ?");
        $sth->execute([
            getPostParam('login'),
            $md5Password
        ]);

        if (!empty($sth->fetchColumn())) {
            login(getPostParam('login'));
        } else {
            $message = "Такой пользователь не существует, либо неверный пароль!";
        }
    } else {
        $message = "Ошибка входа. Введите все необхдоимые данные.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <title>Авторизация</title>
</head>
<body>
<section id="login">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="form-wrap">
                    <h1>Авторизация</h1>

                    <p class="btn btn-custom btn-lg btn-block"><?=$message?></p>

                    <form method="POST">
                        <div class="form-group">
                            <label for="lg" class="sr-only">Логин</label>
                            <input type="text" placeholder="Логин" name="login" id="lg" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="key" class="sr-only">Пароль</label>
                            <input type="password" placeholder="Пароль" name="password" id="key" class="form-control">
                        </div>
                        <input type="submit" name="sign_in" id="btn-login" class="btn btn-custom btn-lg btn-block" value="Войти">

                        <input type="submit" name="register" id="btn-login" class="btn btn-custom btn-lg btn-block" value="Регистрация">
                    </form>

                    <hr>
                </div>
            </div> <!-- /.col-xs-12 -->
        </div> <!-- /.row -->
    </div> <!-- /.container -->
</section>
</body>
</html>
