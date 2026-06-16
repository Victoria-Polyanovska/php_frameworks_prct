<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    private \PDO $pdo;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../../var/data.db';
        $this->pdo = new \PDO("sqlite:" . $dbPath);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            duration INTEGER NOT NULL
        )");
    }

    // GET
    #[Route('/api/courses', name: 'course_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $stmt = $this->pdo->query("SELECT * FROM courses");
        $courses = $stmt->fetchAll();

        return new JsonResponse($courses, Response::HTTP_OK);
    }

    // POST
    #[Route('/api/courses', name: 'course_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['description']) || empty($data['duration'])) {
            return new JsonResponse(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $stmt = $this->pdo->prepare("INSERT INTO courses (title, description, duration) VALUES (:title, :description, :duration)");
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':duration' => (int)$data['duration']
        ]);

        $id = $this->pdo->lastInsertId();
        $data['id'] = (int)$id;

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    // PATCH
    #[Route('/api/courses/{id}', name: 'course_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $course = $stmt->fetch();

        if (!$course) {
            return new JsonResponse(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        $title = $data['title'] ?? $course['title'];
        $description = $data['description'] ?? $course['description'];
        $duration = isset($data['duration']) ? (int)$data['duration'] : $course['duration'];

        $stmt = $this->pdo->prepare("UPDATE courses SET title = :title, description = :description, duration = :duration WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':duration' => $duration,
            ':id' => $id
        ]);

        return new JsonResponse([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'duration' => $duration
        ], Response::HTTP_OK);
    }

    // DELETE
    #[Route('/api/courses/{id}', name: 'course_destroy', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            return new JsonResponse(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        $stmt = $this->pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return new JsonResponse(['message' => 'Course deleted successfully'], Response::HTTP_OK);
    }
}