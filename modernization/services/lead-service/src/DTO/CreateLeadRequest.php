<?php

declare(strict_types=1);

namespace App\DTO;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * DTO representing the minimal data required to create a Lead.
 */
final class CreateLeadRequest
{
    public string $lastName;
    public string $email;
    public string $company;

    /**
     * Create DTO from a PSR-7 request (expects JSON payload).
     *
     * @throws InvalidArgumentException
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON body.');
        }

        $lastName = isset($data['lastName']) ? (string) $data['lastName'] : '';
        $email = isset($data['email']) ? (string) $data['email'] : '';
        $company = isset($data['company']) ? (string) $data['company'] : '';

        $dto = new self();
        $dto->lastName = trim($lastName);
        $dto->email = trim($email);
        $dto->company = trim($company);

        return $dto;
    }
}
