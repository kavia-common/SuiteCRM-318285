<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateLeadRequest;
use PDO;
use RuntimeException;

/**
 * PDO implementation of the LeadRepositoryInterface.
 */
final class PdoLeadRepository implements LeadRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(CreateLeadRequest $lead): string
    {
        // Table name can be overridden via env to fit target schema.
        $table = $_ENV['LEADS_TABLE'] ?? getenv('LEADS_TABLE') ?: 'leads';

        // Minimal generic schema expectation: columns last_name, email, company.
        $sql = sprintf(
            'INSERT INTO %s (last_name, email, company) VALUES (:last_name, :email, :company)',
            preg_replace('/[^a-zA-Z0-9_]/', '', $table)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':last_name' => $lead->lastName,
            ':email' => $lead->email,
            ':company' => $lead->company,
        ]);

        $id = $this->pdo->lastInsertId();
        if ($id === '0' || $id === '') {
            // Some schemas may not have auto-increment IDs; in that case this should be adapted.
            throw new RuntimeException('Lead was saved but no ID was returned by the database.');
        }

        return (string) $id;
    }
}
