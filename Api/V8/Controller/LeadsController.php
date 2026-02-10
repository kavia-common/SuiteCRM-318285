<?php
namespace Api\V8\Controller;

use Api\V8\Service\LeadService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Leads API endpoints.
 *
 * Note: This controller follows the same error-handling style as other V8 controllers
 * (try/catch + generateResponse/generateErrorResponse).
 */
class LeadsController extends BaseController
{
    /**
     * @var LeadService
     */
    protected $leadService;

    /**
     * @param LeadService $leadService
     */
    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Create a lead via a simplified endpoint.
     *
     * Expected request:
     * - Content-Type: application/json
     * - JSON body representing the CreateLeadRequest DTO.
     *
     * Response:
     * - 201 with JSON on success
     * - 400 with JSON error response on invalid input or service failure
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function createLead(Request $request, Response $response, array $args)
    {
        try {
            $jsonResponse = $this->leadService->createLead($request);

            return $this->generateResponse($response, $jsonResponse, 201);
        } catch (\Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
