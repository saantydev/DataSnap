<?php
/* Controlador Home (landing) */
namespace Controllers;

use Core\Database;

class HomeController
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;

    /**
     * Constructor - inyección de dependencias
     *
     * @param Database $db Instancia de la base de datos
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Método factoría para crear instancia del controlador
     *
     * @param Database $db Instancia de la base de datos
     * @return HomeController Nueva instancia del controlador
     */
    public static function create(Database $db): HomeController
    {
        return new self($db);
    }

    /**
     * Muestra la página principal
     *
     * @return void
     */
    public function index(): void
    {
        // Mostrar la spágina de inicio (landing page)
        require_once __DIR__ . '/../../index.html';
    }
}