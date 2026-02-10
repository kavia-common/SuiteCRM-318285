<?php

declare(strict_types=1);

use App\DTO\CreateLeadRequest;
use App\Repository\LeadRepositoryInterface;
use App\Repository\PdoLeadRepository;
use App\Service\LeadService;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Bootstrap environment (.env) for local development.
 * In production, environment variables are expected to be provided by the runtime.
 */
$dotenvPath = dirname(__DIR__);
if (is_readable($dotenvPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($dotenvPath);
    $dotenv->safeLoad();
}

/**
 * Build PHP-DI container.
 */
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    LeadRepositoryInterface::class => DI\autowire(PdoLeadRepository::class),
    PDO::class => function (): PDO {
        // Env-driven DB configuration (do not hardcode credentials).
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
        $dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

        // Note: Driver is assumed MySQL/MariaDB unless overridden.
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    },
]);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

/**
 * POST /leads
 * Creates a lead record and returns 201 Created with JSON payload.
 */
$app->post('/leads', function (Request $request, Response $response) use ($container): Response {
    try {
        $dto = CreateLeadRequest::fromRequest($request);

        /** @var LeadService $leadService */
        $leadService = $container->get(LeadService::class);

        $leadId = $leadService->createLead($dto);

        $payload = json_encode([
            'id' => $leadId,
            'status' => 'created',
        ], JSON_UNESCAPED_SLASHES);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    } catch (InvalidArgumentException $e) {
        $payload = json_encode([
            'error' => 'validation_error',
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_SLASHES);

        $badResponse = new SlimResponse(400);
        $badResponse->getBody()->write($payload);

        return $badResponse->withHeader('Content-Type', 'application/json');
    } catch (Throwable $e) {
        $payload = json_encode([
            'error' => 'server_error',
            'message' => 'An unexpected error occurred.',
        ], JSON_UNESCAPED_SLASHES);

        $errResponse = new SlimResponse(500);
        $errResponse->getBody()->write($payload);

        return $errResponse->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
