<?php
declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default_index")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(
            [
                'action' => 'index',
                'time' => time()
            ]
        );
    }

    /**
     * @Route("/database", name="database_infos")
     */
    public function databaseVersion(): JsonResponse
    {
        $connectionParams = [
            'dbname' => 'udemy',
            'user' => 'root',
            'host' => 'mysqldb',
            'driver' => 'pdo_mysql',
        ];
        $conn = DriverManager::getConnection($connectionParams);
        $res = $conn->executeQuery('SELECT * FROM blog_post;');
        return new JsonResponse(
            $res->fetchAll()
        );
    }
}