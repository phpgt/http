<?php
namespace Gt\Http\ResponseStatusException;

use Gt\Http\StatusCode;

/**
 * The URI provided was too long for the server to process. Often the result of
 * too much data being encoded as a query-string of a GET request, in which
 * case it should be converted to a POST request.
 * Called "Request-URI Too Long" previously.
 * @link https://httpstatuses.com/414
 */
class HttpUriTooLong extends AbstractResponseStatusException {
	protected function getHttpCode():int {
		return StatusCode::REQUEST_URI_TOO_LONG;
	}
}