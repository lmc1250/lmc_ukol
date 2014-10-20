<?php

/**
 * Ověření uživatele a zpracování přihlašovacího formuláře, <br>
 * pokud je uživatel ověřen (přes formulář, cookies nebo session), vrací objekt User, jinak FALSE
 * @return \User|boolean Objekt User pokud je přihlášen, jinak FALSE
 */
function checkLogin() {
    $user = false;

    if (isset($_REQUEST['logout'])) {
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        setcookie("name", "", 0, "/");
        setcookie("passwd", "", 0, "/");
        redirect("/index.php");
    }

    if (isset($_POST['name']) && isset($_POST['passwd'])) {
        $userID = dibi::fetchSingle("SELECT id FROM [user] WHERE [mail] = %s", $_POST['name'], " AND [pass] = %s", md5($_POST['passwd']));
        if ($userID) {
            $_SESSION['name'] = $_POST['name'];
            $_SESSION['pass'] = md5($_POST['passwd']);
            setcookie("name", $_POST['name'], 360, "/");
            setcookie("pass", md5($_POST['passwd']), 360, "/");
            redirect("/index.php");
            $user = new User($userID);            
            return $user;
        }
    }

    if (isset($_SESSION['name']) && isset($_SESSION['pass'])) {
        $userID = dibi::fetchSingle("SELECT id FROM [user] WHERE [mail] = %s", $_SESSION['name'], " AND [pass] = %s", $_SESSION['pass']);
        if ($userID) {
            $user = new User($userID);
            return $user;
        }
    }

    if (isset($_COOKIE['name']) && isset($_COOKIE['pass'])) {
        $userID = dibi::fetchSingle("SELECT id FROM [user] WHERE [mail] = %s", $_COOKIE['name'], " AND [pass] = %s", $_COOKIE['pass']);
        if ($userID) {
            $user = new User($userID);
            return $user;
        }
    }

    return $user;
}

/**
 * Pomocná funkce pro přesměrování v rámci webu nebo mimo něj
 * @param type $url URL adresa, kam má být uživatel přesměrován
 */
function redirect($url) {
    if (strtolower(substr($url, 0, 4)) == "http") {
        header('Location: ' . $url);
        exit;
    } else {
        header('Location: ' . $url);
        exit;
    }
}
