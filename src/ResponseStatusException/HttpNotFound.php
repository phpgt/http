<?php
namespace Gt\Http\ResponseStatusException;

use Gt\Http\StatusCode;

/**
 * The requested resource could not be found but may be available in the future.
 * Subsequent requests by the client are permissible.
 * @link https://httpstatuses.com/404
 */
class HttpNotFound extends AbstractResponseStatusException {
	public function getHttpCode():int {
		return StatusCode::NOT_FOUND;
	}
}