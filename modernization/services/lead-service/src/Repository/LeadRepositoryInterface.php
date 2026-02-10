<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateLeadRequest;

/**
 * Persistence interface for leads.
 */
interface LeadRepositoryInterface
{
    /**
     * Save a lead and return its generated identifier.
     */
    public function save(CreateLeadRequest $lead): string;
}
