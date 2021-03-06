<?php
namespace Gt\Http\ResponseStatusException\ServerError;

use Gt\Http\StatusCode;

/**
 * The server detected an infinite loop while processing the request (sent
 * instead of 208 Already Reported).
 * @link https://httpstatuses.com/508
 */
class HttpLoopDetected extends ServerErrorException {
	public function getHttpCode():int {
		return StatusCode::LOOP_DETECTED;
	}
}
