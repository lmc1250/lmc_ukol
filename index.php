<!DOCTYPE html>
<!--
Webová aplikace dle zadání LMC: 
Vytvořte jednoduchou aplikaci v PHP.
Scope:
Registrace 
Přihlášení
Přidání, editace, zobrazení článků
Aplikace prosím pište tak, aby umožňovala pozdější rozšiřování. Výsledek nahrajte na
Github.
K aplikaci není připojen CSS soubor pro integraci stylů, ani nejsou připraveny třídy pro napojení CSS.
Pro jednodušší nasazení aplikace je použito jednoho obslužného skriptu (index.php), který slouží pro zobrazení všech stránek, 
běžně by bylo použito .htaccess, nicméně opět pro zjednodušení je použito žádostí o stránku přímo v adrese stránky jako parametru
 ----- Heslo v cookies/sesion -----
Pro zjednodučšení je do cookies ukládáno přímo heslo uživatele (v zahashované formě a s minimální dobou platnosti), 
pro běžný provoz by byla použita další tabulka s tokeny pro přihlášení pomocí cookies nebo session 
a expirací, aby nebylo možné tak jednoduše ukrást uživateli identitu

 ----- Struktura aplikace ----- 
hlavní controler a defakto i view vrstva je v objektu Page (Page.php),
objekty User a Article reprezentují hlavní dvě entity v systému (uživatele a článek), 
operace s nimi jsou implementovány jako funkce objektů. Pro zjednodušení funkce pro zobrazení používají funkce, 
které přímo vrací HTML fragmenty kódu, není použito žádné šablonování.
Základní funkce pro ověření uživatele, nebo běžně používané operace v systému jsou v souboru core_functions.php, kde
checkLogin ověřuje připojeného uživatele,
redirect je použito pro přesměrování v rámci stránek nebo mimo web
Konfirace základních nastavení systému je v souboru config.php

 ----- Knihovny 3. stran -----
Byla použita knihovna pro spojení s databází dibi - dovoluje provádět bezpečné dotazování a předchází SQL injection

-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Testovací web - Test: PHP developer</title>
    </head>
    <body>
        <?php
        include getcwd() . '/config.php';
        include getcwd() . '/3rd_libs/dibi.min.php';
        include getcwd() . '/objects/User.php';
        include getcwd() . '/objects/Article.php';
        include getcwd() . '/objects/Page.php';
        include getcwd() . '/core_functions.php';

        session_start();

        dibi::connect($config);

        $user = checkLogin();

        $page = new Page();
        session_commit();
        echo $page;
        ?>
    </body>
</html>
