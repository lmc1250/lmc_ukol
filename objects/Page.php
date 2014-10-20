<?php

class Page {

    var $controler;
    var $htmlContent;

    /**
     * Objekt stránky - hlavní controler pro výstup aplikace
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return boolean 
     */
    function __construct() {
        global $user;
        if (isset($_GET) && isset($_GET['page'])) {
            $this->controler = $_GET['page'];
            return true;
        }
        if (!$user) {
            $this->controler = "articles";
            return true;
        }
    }

    /**
     * Na základě požadavku na stránku připraví obsah (rovnou v HTML) <br>
     * připravený HTML obsah je uložen v objektu
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return string HTML výpis
     */
    function composePage() {
        $this->htmlContent = "";
        global $user;
        if ($user) {
            $this->htmlContent .= "Pihlášen " . $user->getName() . "<hr>" . $this->getAdminMenuHTML() . "<hr>";
        } else {
            $this->htmlContent .= $this->getMenuHTML() . "<hr>";
        }
        switch ($this->controler) {
            case "articles":
                if (isset($_GET['id'])) {
                    $article = new Article($_GET['id']);
                    if (isset($article->id)) {
                        $this->htmlContent .= $article->getHTMLView();
                        break;
                    }
                }
                $this->htmlContent .= Article::getArticlesList();
                break;
            case "login":
                if (isset($_POST['name']) && isset($_POST['passwd']) && !$user) {
                    $this->htmlContent .= '<b>Přihlášení se nezdařilo, zkontrolujte jméno.. Pokud nejste registrováni, <a href="/index.php?page=register">registrujte se</a>..</b><br><br>';
                }
                $this->htmlContent .= $this->getLoginFormHTML();
                break;
            case "register":
                if (isset($_POST['register']) && $_POST['register'] == "Registrovat") {
                    $user = new User($_POST);
                    if ($user->save()) {
                        $this->htmlContent .= "Byl jste právě zaregistrován.. <br>";
                        $this->htmlContent .= '<a href="/index.php?page=login">Přejít na přihlašovací stránku..</a>';
                    } else {
                        $this->htmlContent .= "<b>Registrace neproběhla správně, pokuste se vyplnit znovu formulář..</b><br>";
                        if ($user->errorMessage == 1062) {
                            $this->htmlContent .= "Zadaný email je již v naší databázi..<br><br>";
                        }
                        $this->htmlContent .= $this->getRegisterFormHTML();
                    }
                } else {
                    $this->htmlContent .= $this->getRegisterFormHTML();
                }
                break;
            case "new":
                if (isset($_POST['save']) && $_POST['save'] == "Uložit") {
                    $article = new Article($_POST);
                    if (!$article->save()) {
                        $this->htmlContent .= "<b>Nepodařilo se uložit článek, zkontrolujte, že je vše vyplněno!</b><br><br>";
                        $this->htmlContent .= $article->errorMessage . "<br>";
                        $this->htmlContent .= $this->getArticleFromHTML($article);
                    } else {
                        $this->htmlContent .= "<b>Článek uložen</b><br><br>";
                        $this->htmlContent .= Article::getArticlesList();
                    }
                } else {
                    $this->htmlContent .= $this->getArticleFromHTML();
                }
                break;
            case "edit":
                if (isset($_POST['save']) && $_POST['save'] == "Uložit") {
                    $article = new Article($_POST);
                    if (!$article->save()) {
                        $this->htmlContent .= "<b>Nepodařilo se uložit článek, zkontrolujte, že je vše vyplněno!</b><br><br>";
                        $this->htmlContent .= $article->errorMessage . "<br>";
                        $this->htmlContent .= $this->getArticleFromHTML($article);
                    } else {
                        $this->htmlContent .= "<b>Článek uložen</b><br><br>";
                        $this->htmlContent .= Article::getArticlesList();
                    }
                } else {
                    if (isset($_GET['id'])) {
                        $article = new Article($_GET['id']);
                        if (!isset($article->id)) {
                            $this->htmlContent .= "Článek s ID " . $_GET['id'] . " neexistuje..<br><br>";
                            $this->htmlContent .= Article::getArticlesList();
                        } else {
                            $this->htmlContent .= $this->getArticleFromHTML($article);
                        }
                    }
                }
                break;
            case "delete":
                if (isset($_POST['delete']) && $_POST['delete'] == "Nemazat" && isset($_POST['id'])) {
                    $this->htmlContent .= Article::getArticlesList();
                    break;
                }
                if (isset($_POST['delete']) && $_POST['delete'] == "Smazat" && isset($_POST['id'])) {
                    $article = new Article($_POST['id']);
                    if ($article->delete()) {
                        $this->htmlContent .= "<b>Článek byl vymazán</b><br><br>";
                    } else {
                        $this->htmlContent .= "<b>Článek nelze smazat..</b><br><br>";
                    }
                } else {
                    if (isset($_GET['id'])) {
                        $this->htmlContent .= $this->getDeleteForm($_GET['id']);
                        break;
                    }
                }
                $this->htmlContent .= Article::getArticlesList();
                break;
            case "show":
                if (isset($_GET['id'])) {
                    $article = new Article($_GET['id']);
                    if (isset($article->id)) {
                        $this->htmlContent .= $article->getHTMLView();
                        break;
                    }
                }
                $this->htmlContent .= "Nebyl nalezen požadovaný článek..";
                break;
            default:
                $this->htmlContent .= Article::getArticlesList();
                break;
        }

        return $this->htmlContent;
    }

    /**
     * Formulář pro potvrzení vymazání článku
     * @param integer $id ID článku
     * @return string HTML formulář
     */
    function getDeleteForm($id = null) {
        if (isset($id)) {
            $page = "";
            $page = "Přejete si opravdu smazat článek?<br><br>";
            $page .= '<form method="post" action="">';
            $page .= '<input type="hidden" name="id" value="' . $id . '">';
            $page .= '<input type="submit" name="delete" value="Smazat">';
            $page .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $page .= '<input type="submit" name="delete" value="Nemazat"><br>';
            $page .= "</form>";
            return $page;
        }
    }

    /**
     * Konstrukce menu pro přihlášeného uživatele
     * @return string HTML výpis
     */
    function getAdminMenuHTML() {
        $page = "";
        $page .= '<a href="/index.php?page=articles">Výpis článků</a>';
        $page .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $page .= '<a href="/index.php?page=new">Nový článek</a>';
        $page .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $page .= '<a href="/index.php?logout">Odhlásit</a>';
        return $page;
    }

    /**
     * Konstrukce menu pro nepřihlášeného uživatele
     * @return string HTML výpis
     */
    function getMenuHTML() {
        $page = "";
        $page .= '<a href="/index.php?page=articles">Výpis článků</a>';
        $page .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $page .= '<a href="/index.php?page=register">Registrace</a>';
        $page .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $page .= '<a href="/index.php?page=login">Přihlásit</a>';
        return $page;
    }

    /**
     * Konstrukce formuláře pro přihlášení
     * @return string HTML formulář
     */
    function getLoginFormHTML() {
        $page = "";
        $page .= '<form method="post" action="">';
        $page .= '<label>Mail: </label>';
        $page .= '<input type="text" name="name" id="name"><br>';
        $page .= '<label>Heslo: </label>';
        $page .= '<input type="password" name="passwd" id="passwd"><br>';
        $page .= '<input type="submit" name="login" value="Přihlásit"><br>';
        $page .= "</form>";
        return $page;
    }

    /**
     * Konstrukce formuláře pro registraci
     * @return string
     */
    function getRegisterFormHTML() {
        $page = "";
        $page .= '<form method="post" action="">';
        $page .= '<label>Jméno: </label>';
        $page .= '<input type="text" name="name" id="name"><br>';
        $page .= '<label>Mail (bude použit jako login): </label>';
        $page .= '<input type="text" name="mail" id="mail"><br>';
        $page .= '<label>Heslo: </label>';
        $page .= '<input type="password" name="pass" id="pass"><br>';
        $page .= '<label>Heslo znovu: </label>';
        $page .= '<input type="password" name="pass_conf" id="pass_conf"><br>';
        $page .= '<input type="submit" name="register" value="Registrovat"><br>';
        $page .= "</form>";
        return $page;
    }

    /**
     * Konstrukce formuláře pro vložení / úpravu článku
     * @param Article $article Pokud je předán objekt článek, předvyplní data podle zadaného objektu
     * @return string
     */
    function getArticleFromHTML($article = null) {
        if (!is_object($article)) {
            $article = new Article();
        }
        $page = "";
        $page .= '<form method="post" action="">';
        if (isset($article->id)) {
            $page .= '<input type="hidden" name="id" value="' . $article->id . '">';
        }
        $page .= '<label>Nadpis článku: </label>';
        $page .= '<input type="text" name="title" id="title" maxlength="250" placeholder="Název" size="100" value="' . $article->title . '"><br>';
        $page .= '<label>Obsah článku: </label><br>';
        $page .= '<textarea rows="15" cols="60" name="content">' . $article->content . '</textarea><br>';
//        $page .= '<label>Pouze text - bez HTML a skruptů</label>';
//        $page .= '<input type="checkbox" name = "nothtml" value="nothtml"><br>';
        $page .= '<input type="submit" name="save" value="Uložit"><br>';
        $page .= "</form>";
        return $page;
    }

    /**
     * Vrátí kompletní obsah stránky (pouze obsah pro HTML/body)
     * @return string HTML content
     */
    function __toString() {
        try {
            if (!isset($this->htmlContent)) {
                $this->composePage();
            }
            return $this->htmlContent;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

}
