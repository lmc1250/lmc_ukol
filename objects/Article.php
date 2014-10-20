<?php

class Article {

    var $id = null;
    var $title = null;
    var $content = null;
    var $last_edit = null;
    var $last_editor_id = null;
    var $author_id = null;
    var $created = null;
// -----------------------------------------------------------------------------
    var $errorMessage;
    var $nothtml = 0;

    /**
     * Reprezentuje článek (struktura jako v DB)
     * @param int|array $id Pokud je předáno ID, načte data o článku z DB, pokud je předáno pole, načte data pro objekt z pole, <br> jinak nechá prázdný objekt
     * @return boolean 
     */
    function __construct($id = null) {
        if (!isset($id)) {
            return true;
        }
        if (is_numeric($id)) {
            $data = dibi::query("SELECT * FROM [article] WHERE [id] = %i", $id);
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $this->fillFromArray($row);
                }
                return true;
            }
        }
        if (is_array($id)) {
            $this->fillFromArray($id);
            return true;
        }
    }

    /**
     * Naplnění objektu hodnotami z pole
     * @param array $data Assoc pole s hodnotami pro objekt
     */
    function fillFromArray($data = array()) {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['url'])) {
            $this->url = $data['url'];
        }
        if (isset($data['content'])) {
            $this->content = $data['content'];
        }
        if (isset($data['last_edit'])) {
            $this->last_edit = $data['last_edit'];
        }
        if (isset($data['last_editor_id'])) {
            $this->last_editor_id = $data['last_editor_id'];
        }
        if (isset($data['author_id'])) {
            $this->author_id = $data['author_id'];
        }
        if (isset($data['created'])) {
            $this->created = $data['created'];
        }
    }

    /**
     * Vytvoří data pro zápis do databáze pro daný objekt
     * @return array Serializované pole pro zápis do databáze
     */
    function serialize() {
        $data = array();

        if (isset($this->title)) {
            $data['title'] = $this->title;
        }
        if (isset($this->content)) {
            if ($this->nothtml == 1) {
                $data['content'] = htmlspecialchars($this->content);
            } else {
                $data['content'] = $this->content;
            }
        }
        if (isset($this->last_edit)) {
            $data['last_edit'] = $this->last_edit;
        }
        if (isset($this->last_editor_id)) {
            $data['last_editor_id'] = $this->last_editor_id;
        }
        if (isset($this->author_id)) {
            $data['author_id'] = $this->author_id;
        }
        if (isset($this->created)) {
            $data['created'] = $this->created;
        }

        return $data;
    }

    /**
     * Metoda vrací výpis článků s možností zobrazení/editace/výmazu, práva jsou připravena podle uživatele -
     * uživatel přihlášen - zobrazení / editace / smazání
     * uživatel nepřihlášen - zobrazení
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return string HTML výpis
     */
    public function getArticlesList() {
        global $user;
        $page = "";
        $articles = dibi::query("SELECT id, title FROM [article] ORDER BY last_edit");
        if (count($articles)) {
            $page .= "<table>";
            if ($user) {
                $page .= "<thead><th>Název</th><th>Zobrazit</th><th>Editovat</th><th>Smazat</th></thead>";
            } else {
                $page .= "<thead><th>Název</th><th>Zobrazit</th></thead>";
            }
            foreach ($articles as $row) {
                if ($user) {
                    $page .= "<tr><td>" . $row['title'] . "</td><td><a href='/index.php?page=articles&id=" . $row['id'] . "'>Zobrazit</a></td><td><a href='/index.php?page=edit&id=" . $row['id'] . "'>Editovat</a></td></td><td><a href='/index.php?page=delete&id=" . $row['id'] . "'>Smazat</a></td></tr>";
                } else {
                    $page .= "<tr><td>" . $row['title'] . "</td><td><a href='/index.php?page=articles&id=" . $row['id'] . "'>Zobrazit</a></td></tr>";
                }
            }
            $page .= "</table>";
        } else {
            return "V databázi nejsou žádné články..";
        }
        return $page;
    }

    /**
     * Vrací kompletní výpis článku v HTML formátu - nadpis + tělo článku + patička - kdo článek napsal
     * Pokud je přihlášen uživatel, pod patičkou zobrazuje možné akce s článkem
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return string HTML výpis
     */
    function getHTMLView() {
        global $user;
        $page = "";
        $page .= "<h1>" . $this->title . "</h1>";
        $page .= $this->content;
        $page .= "<hr>";
        $page .= "Napsal " . $this->getAuthorName();
        if ($user) {
            $page .= '<hr><a href="/index.php?page=edit&id=' . $this->id . '">Editovat</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="/index.php?page=delete&id=' . $this->id . '">Smazat</a>';
        }
        return $page;
    }

    /**
     * Vrací informace o autorovi článku
     * @return string HTML výpis
     */
    function getAuthorName() {
        $user = new User($this->author_id);
        return $user->getName();
    }

    /**
     * Uloží data objektu do databáze <br>
     * Pokud je zadáno ID, článek upraví, pokud ne, vytvoří nový
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return boolean TRUE při správném uložení, jinak FALSE
     */
    function save() {
        global $user;
        if (!$user) {
            $this->errorMessage = "Nelze uložit článek, pokud nejste přihlášen..";
            return false;
        }
        $this->last_edit = date("Y-m-d H:i:s");
        $this->last_editor_id = $user->id;
        if (isset($this->id)) {
            try {
                if (dibi::query("UPDATE [article] SET ", $this->serialize(), "WHERE [id] = %i ", $this->id)) {
                    return true;
                }
            } catch (Exception $ex) {
                $this->errorMessage = $ex->getCode();
                return false;
            }
        } else {
            $this->created = date("Y-m-d H:i:s");
            $this->author_id = $user->id;
            try {
                if (dibi::query("INSERT INTO [article] ", $this->serialize())) {
                    return true;
                }
            } catch (Exception $ex) {
                $this->errorMessage = $ex->getCode();
                return false;
            }
        }
    }

    /**
     * Mazání článku <br>- výmaz může provést jen přihlášený uživatel
     * @global User $user Objekt uživatele dostupný v celém systému
     * @return boolean TRUE při úspěšném vymázání jinak false
     */
    function delete() {
        global $user;
        if (!$user) {
            $this->errorMessage = "Nelze vymazat článek, pokud nejste přihlášen..";
            return false;
        }
        try {
            if (dibi::query("DELETE FROM [article] WHERE [id] = %i", $this->id)) {
                return true;
            }
        } catch (Exception $ex) {
            $this->errorMessage = $ex->getCode();
            return false;
        }
    }

}
