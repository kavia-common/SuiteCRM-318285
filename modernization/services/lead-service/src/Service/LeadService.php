<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateLeadRequest;
use App\Repository\LeadRepositoryInterface;
use InvalidArgumentException;

/**
 * Service layer for lead-related business logic.
 */
final class LeadService
{
    private LeadRepositoryInterface $repository;

    public function __construct(LeadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate and create a new lead, returning its ID.
     *
     * @throws InvalidArgumentException
     */
    public function createLead(CreateLeadRequest $lead): string
    {
        if ($lead->lastName === '') {
            throw new InvalidArgumentException('lastName is required.');
        }

        if ($lead->email === '' || !filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email is required.');
        }

        if ($lead->company === '') {
            throw new InvalidArgumentException('company is required.');
        }

        return $this->repository->save($lead);
    }
}
