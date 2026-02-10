<?php
namespace Api\V8\Request;

/**
 * DTO for creating a Lead.
 *
 * This is intentionally small and focused on common lead-create fields.
 */
class CreateLeadRequest
{
    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $title;

    /** @var string|null */
    public $phoneWork;

    /** @var string|null */
    public $phoneMobile;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $primaryAddressStreet;

    /** @var string|null */
    public $primaryAddressCity;

    /** @var string|null */
    public $primaryAddressState;

    /** @var string|null */
    public $primaryAddressPostalCode;

    /** @var string|null */
    public $primaryAddressCountry;

    /**
     * Build from an associative array (already parsed JSON body).
     *
     * Accepts both snake_case and camelCase keys for convenience.
     *
     * @param array $data
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        $req = new static();

        $req->firstName = self::getAny($data, ['first_name', 'firstName']);
        $req->lastName = self::getAny($data, ['last_name', 'lastName']);
        $req->title = self::getAny($data, ['title']);
        $req->phoneWork = self::getAny($data, ['phone_work', 'phoneWork']);
        $req->phoneMobile = self::getAny($data, ['phone_mobile', 'phoneMobile']);
        $req->email = self::getAny($data, ['email', 'email1']);

        $req->primaryAddressStreet = self::getAny($data, ['primary_address_street', 'primaryAddressStreet']);
        $req->primaryAddressCity = self::getAny($data, ['primary_address_city', 'primaryAddressCity']);
        $req->primaryAddressState = self::getAny($data, ['primary_address_state', 'primaryAddressState']);
        $req->primaryAddressPostalCode = self::getAny($data, ['primary_address_postalcode', 'primaryAddressPostalCode']);
        $req->primaryAddressCountry = self::getAny($data, ['primary_address_country', 'primaryAddressCountry']);

        return $req;
    }

    /**
     * Return first non-null value for a list of keys.
     *
     * @param array $data
     * @param array $keys
     *
     * @return mixed|null
     */
    protected static function getAny(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }
        return null;
    }
}
