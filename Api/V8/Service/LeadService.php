<?php
namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\JsonApi\Helper\AttributeObjectHelper;
use Api\V8\JsonApi\Helper\RelationshipObjectHelper;
use Api\V8\Request\CreateLeadRequest;
use Slim\Http\Request;

/**
 * Service for lead-specific operations (custom, simplified API surface).
 */
class LeadService
{
    /**
     * @var BeanManager
     */
    protected $beanManager;

    /**
     * @var AttributeObjectHelper
     */
    protected $attributeObjectHelper;

    /**
     * @var RelationshipObjectHelper
     */
    protected $relationshipObjectHelper;

    /**
     * @param BeanManager             $beanManager
     * @param AttributeObjectHelper   $attributeObjectHelper
     * @param RelationshipObjectHelper $relationshipObjectHelper
     */
    public function __construct(
        BeanManager $beanManager,
        AttributeObjectHelper $attributeObjectHelper,
        RelationshipObjectHelper $relationshipObjectHelper
    ) {
        $this->beanManager = $beanManager;
        $this->attributeObjectHelper = $attributeObjectHelper;
        $this->relationshipObjectHelper = $relationshipObjectHelper;
    }

    /**
     * Create a lead from a JSON request body.
     *
     * @param Request $request
     *
     * @return array JSON-serializable response payload.
     *
     * @throws \InvalidArgumentException When body is missing/invalid.
     * @throws \Exception When save fails.
     */
    public function createLead(Request $request)
    {
        $parsed = $request->getParsedBody();

        // Slim may return null if JSON is invalid/unparseable
        if (!is_array($parsed)) {
            throw new \InvalidArgumentException('Invalid JSON body.');
        }

        $createLeadRequest = CreateLeadRequest::fromArray($parsed);

        // Minimal required fields; keep this conservative and aligned with typical Leads usage.
        if ($createLeadRequest->lastName === null || trim($createLeadRequest->lastName) === '') {
            throw new \InvalidArgumentException('Field "last_name" is required.');
        }

        /** @var \Lead $lead */
        $lead = \BeanFactory::newBean('Leads');
        $lead->first_name = $createLeadRequest->firstName;
        $lead->last_name = $createLeadRequest->lastName;
        $lead->title = $createLeadRequest->title;
        $lead->phone_work = $createLeadRequest->phoneWork;
        $lead->phone_mobile = $createLeadRequest->phoneMobile;
        $lead->email1 = $createLeadRequest->email;

        // Address (optional)
        $lead->primary_address_street = $createLeadRequest->primaryAddressStreet;
        $lead->primary_address_city = $createLeadRequest->primaryAddressCity;
        $lead->primary_address_state = $createLeadRequest->primaryAddressState;
        $lead->primary_address_postalcode = $createLeadRequest->primaryAddressPostalCode;
        $lead->primary_address_country = $createLeadRequest->primaryAddressCountry;

        $id = $lead->save();
        if (!$id) {
            throw new \Exception('Failed to create lead.');
        }

        // Return a JSON:API-ish payload consistent with other v8 responses:
        // { data: { type, id, attributes, relationships? } }
        $attributes = $this->attributeObjectHelper->getAttributes($lead);
        $relationships = $this->relationshipObjectHelper->getRelationships($lead);

        return [
            'data' => [
                'type' => 'Leads',
                'id' => $lead->id,
                'attributes' => $attributes,
                'relationships' => $relationships,
            ],
        ];
    }
}
