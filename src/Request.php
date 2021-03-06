<?php
namespace Gt\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Gt\Http\Header\RequestHeaders;

class Request implements RequestInterface {
	use Message;

	protected string $method;
	protected UriInterface $uri;
	protected string $requestTarget;

	public function __construct(
		string $method,
		UriInterface $uri,
		RequestHeaders $headers
	) {
		$this->method = RequestMethod::filterMethodName($method);
		$this->uri = $uri;
		$this->headers = $headers;

		$firstLine = $this->headers->getFirst();
		$this->protocol = substr(
			$firstLine,
			0,
			strpos($firstLine, " ")
		);
	}

	/**
	 * Retrieves the message's request target.
	 *
	 * Retrieves the message's request-target either as it will appear (for
	 * clients), as it appeared at request (for servers), or as it was
	 * specified for the instance (see withRequestTarget()).
	 *
	 * In most cases, this will be the origin-form of the composed URI,
	 * unless a value was provided to the concrete implementation (see
	 * withRequestTarget() below).
	 *
	 * If no URI is available, and no request-target has been specifically
	 * provided, this method MUST return the string "/".
	 *
	 * @return string
	 */
	public function getRequestTarget():string {
		if(isset($this->requestTarget)) {
			return $this->requestTarget;
		}

		$uri = $this->getUri();
		$requestTarget = $uri->getPath();
		if(empty($requestTarget)) {
			$requestTarget = "/";
		}

		$query = $uri->getQuery();
		if(!empty($query)) {
			$requestTarget .= "?";
		}
		$requestTarget .= $query;

		return $requestTarget;
	}

	/**
	 * Return an instance with the specific request-target.
	 *
	 * If the request needs a non-origin-form request-target — e.g., for
	 * specifying an absolute-form, authority-form, or asterisk-form —
	 * this method may be used to create an instance with the specified
	 * request-target, verbatim.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request target.
	 *
	 * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
	 *     request-target forms allowed in request messages)
	 * @param mixed $requestTarget
	 * @return static
	 */
	public function withRequestTarget($requestTarget) {
		$clone = clone $this;
		$clone->requestTarget = $requestTarget;
		return $clone;
	}

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Return an instance with the provided HTTP method.
	 *
	 * While HTTP method names are typically all uppercase characters, HTTP
	 * method names are case-sensitive and thus implementations SHOULD NOT
	 * modify the given string.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request method.
	 *
	 * @param string $method Case-sensitive method.
	 * @return static
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod($method) {
		$method = RequestMethod::filterMethodName($method);
		$clone = clone $this;
		$clone->method = $method;
		return $clone;
	}

	/**
	 * Retrieves the URI instance.
	 *
	 * This method MUST return a UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @return UriInterface Returns a UriInterface instance
	 *     representing the URI of the request.
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * This method MUST update the Host header of the returned request by
	 * default if the URI contains a host component. If the URI does not
	 * contain a host component, any pre-existing Host header MUST be carried
	 * over to the returned request.
	 *
	 * You can opt-in to preserving the original state of the Host header by
	 * setting `$preserveHost` to `true`. When `$preserveHost` is set to
	 * `true`, this method interacts with the Host header in the following ways:
	 *
	 * - If the Host header is missing or empty, and the new URI contains
	 *   a host component, this method MUST update the Host header in the returned
	 *   request.
	 * - If the Host header is missing or empty, and the new URI does not contain a
	 *   host component, this method MUST NOT update the Host header in the returned
	 *   request.
	 * - If a Host header is present and non-empty, this method MUST NOT update
	 *   the Host header in the returned request.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @param UriInterface $uri New request URI to use.
	 * @param bool $preserveHost Preserve the original state of the Host header.
	 * @return static
	 */
	public function withUri(UriInterface $uri, $preserveHost = false) {
		$clone = clone $this;

		$host = $uri->getHost();
		if(!empty($host)) {
			if(!$preserveHost
			|| !$this->headers->contains("Host")) {
				$this->headers->add("Host", $host);
			}
		}

		$clone->uri = $uri;
		return $clone;
	}
}
