<?php

class User {

    var $id = null;
    var $name = null;
    var $mail = null;
    var $pass = null;
    var $authorized = null;
    var $created = null;
// -----------------------------------------------------------------------------
    var $encrypted = false;
    var $errorMessage = null;

    /**
     * 
     * @param integer|array $id Data o uživateli, při zadání ID naplní objekt z DB, pokud je $id pole, pak naplní z tohoto pole, jinak vytvoří prázdný objekt
     * @return boolean
     */
    function __construct($id = null) {
        if (!isset($id)) {
            return true;
        }


        if (is_numeric($id)) {
            $data = dibi::query("SELECT * FROM [user] WHERE [id] = %i", $id);
            if (count($data) > 0) {
                $this->encrypted = true;
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
     * Naplní objekt daty z předaného pole
     * @param array $data Pole s daty o uživateli
     */
    function fillFromArray($data = array()) {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['mail'])) {
            $this->mail = $data['mail'];
        }
        if (isset($data['pass'])) {
            $this->pass = $data['pass'];
        }
        if (isset($data['authorized'])) {
            $this->authorized = $data['authorized'];
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
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        if (isset($this->name)) {
            $data['name'] = $this->name;
        }
        if (isset($this->mail)) {
            $data['mail'] = $this->mail;
        }
        if (isset($this->pass)) {
            $data['pass'] = $this->pass;
        }
        if (isset($this->authorized)) {
            $data['authorized'] = $this->authorized;
        }
        return $data;
    }

    /**
     * Vrací jméno a mail uživatele
     * @return string Jméno (mail)
     */
    function getName() {
        return $this->name . " ($this->mail)";
    }

    /**
     * Uloží data objektu do databáze <br>
     * Slouží pouz pro vytváření nových uživatelů !! <br>
     * Není implementována úprava dat v DB podle objektu !!!
     * @return boolean TRUE při správném uložení, jinak FALSE
     */
    function save() {
        if (!$this->encrypted) {
            $this->pass = md5($this->pass);
            $this->encrypted = true;
        }
        if (!isset($this->created)) {
            $this->created = date("Y-m-d H:i:s");
        }
        if (isset($this->id)) {
            throw ("Not allowed editing user!");
        } else {
            $this->authorized = 0;
            try {
                if (dibi::query("INSERT INTO [user] ", $this->serialize())) {
                    return true;
                }
            } catch (Exception $ex) {
                $this->errorMessage = $ex->getCode();
            }
        }
    }

}
