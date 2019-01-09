<?php

namespace Speelpenning\PostcodeNl\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;
use Speelpenning\PostcodeNl\Exceptions\AccountSuspended;
use Speelpenning\PostcodeNl\Exceptions\AddressNotFound;
use Speelpenning\PostcodeNl\Exceptions\Unauthorized;
use Speelpenning\PostcodeNl\Services\AddressLookup;

class AddressController extends Controller
{
    /**
     * @var AddressLookup
     */
    protected $lookup;

    /**
     * AddressController constructor.
     *
     * @param AddressLookup $lookup
     */
    public function __construct(AddressLookup $lookup)
    {
        $this->lookup = $lookup;
    }

    /**
     * Performs a Dutch address lookup and returns a JSON response.
     *
     * @param string $postcode
     * @param int|string $houseNumber
     * @param null|string $houseNumberAddition
     * @return JsonResponse
     */
    public function get(string $postcode, string $houseNumber, string $houseNumberAddition = null): JsonResponse
    {
        try {
            $postcode = strtoupper($postcode);
            $postcode = str_replace(' ', '', $postcode);

            $address = $this->lookup->lookup($postcode, (int)$houseNumber, $houseNumberAddition);
            return response()->json($address);
        } catch (ValidationException $e) {
            abort(400, 'Bad Request');
        } catch (Unauthorized $e) {
            abort(401, 'Unauthorized');
        } catch (AccountSuspended $e) {
            abort(403, 'Account suspended');
        } catch (AddressNotFound $e) {
            abort(404, 'Not Found');
        }
    }
}
