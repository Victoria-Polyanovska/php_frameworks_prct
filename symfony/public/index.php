<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (strpos($_SERVER['REQUEST_URI'], '/api/courses') === 0) {
    header('Content-Type: application/json');
    
    // Підключаємося до бази даних SQLite в папці var
    $dbPath = dirname(__DIR__) . '/var/data.db';
    $pdo = new \PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    
    // Створюємо таблицю, якщо її немає
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        duration INTEGER NOT NULL
    )");

    // Обробка GET-запиту 
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM courses");
        echo json_encode($stmt->fetchAll());
        exit;
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
