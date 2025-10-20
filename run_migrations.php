<?php
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Core/Migrations.php';

$db = \Core\Database::getInstance();
\Core\Migrations::run($db);

echo "Migrations completed.\n";
?>