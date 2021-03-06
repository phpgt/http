<?php
namespace Gt\Http\ResponseStatusException\ServerError;

use Gt\Http\StatusCode;

/**
 * Transparent content negotiation for the request results in a circular
 * reference.
 * @link https://httpstatuses.com/506
 */
class HttpVariantAlsoNegotiates extends ServerErrorException {
	public function getHttpCode():int {
		return StatusCode::VARIANT_ALSO_NEGOTIATES;
	}
}
