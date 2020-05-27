<?php
namespace Gt\Http\ResponseStatusException;

use Gt\Http\StatusCode;

class HttpLocked extends AbstractResponseStatusException {
	public function getHttpCode():int {
		return StatusCode::LOCKED;
	}
}