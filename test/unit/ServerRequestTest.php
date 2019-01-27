<?php
namespace Gt\Http\Test;

use Gt\Cookie\Cookie;
use Gt\Cookie\CookieHandler;
use Gt\Http\Header\RequestHeaders;
use Gt\Http\ServerInfo;
use Gt\Http\ServerRequest;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Input\InputData\BodyInputData;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Input\InputData\Datum\InputDatum;
use Gt\Input\InputData\FileUploadInputData;
use Gt\Input\InputData\InputData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase {
	public function testGetServerParams() {
		$serverParams = [
			"KEY1" => "VALUE1",
			"KEY2" => "VALUE2",
		];
		$sut = self::getServerRequest(
			null,
			null,
			[],
			$serverParams
		);
		self::assertEquals($serverParams, $sut->getServerParams());
	}

	public function testGetCookieParams() {
		$cookieParams = [
			"COOKIE1" => "VALUE1",
			"COOKIE2" => "VALUE2",
		];
		$sut = self::getServerRequest(
			null,
			null,
			[],
			[],
			[],
			$cookieParams
		);
		self::assertEquals($cookieParams, $sut->getCookieParams());
	}

	public function testWithCookieParams() {
		$cookieParams1 = [
			"COOKIE1" => "VALUE1",
			"COOKIE2" => "VALUE2",
		];
		$cookieParams2 = [
			"COOKIE3" => "VALUE3",
			"COOKIE4" => "VALUE4",
		];
		$sut1 = self::getServerRequest(
			null,
			null,
			[],
			[],
			[],
			$cookieParams1
		);
		$sut2 = $sut1->withCookieParams($cookieParams2);
		self::assertEquals($cookieParams1, $sut1->getCookieParams());
		self::assertEquals($cookieParams2, $sut2->getCookieParams());
	}

	public function testGetQueryParams() {
		$params = [
			"key1" => "value1",
			"key2" => "value2",
		];
		$server = [
			"QUERY_STRING" => http_build_query($params),
		];
		$sut = self::getServerRequest(
			null,
			null,
			[],
			$server
		);
		self::assertEquals($params, $sut->getQueryParams());
	}

// TODO: Work out problem when using cloned mock in withQueryParams below.
	public function testWithQueryParams() {
		$params1 = [
			"key1" => "value1",
			"key2" => "value2",
		];
		$params2 = [
			"key3" => "value3",
			"key4" => "value4",
		];
		$server1 = [
			"QUERY_STRING" => http_build_query($params1),
		];
		$sut1 = self::getServerRequest(
			null,
			null,
			[],
			$server1
		);
		$sut2 = $sut1->withQueryParams($params2);
		self::assertEquals($params1, $sut1->getQueryParams());
		self::assertEquals($params2, $sut2->getQueryParams());
	}

	public function testGetUploadedFilesEmpty() {
		$sut = self::getServerRequest();
		self::assertEmpty($sut->getUploadedFiles());
	}

	public function testGetUploadedFiles() {
		$sut = self::getServerRequest(
			"post",
			"/example",
			[],
			[],
			[
				"files" => [
					"file1" => [
						"name" => "myfile.txt",
					],
					"file2" => [
						"name" => "image.jpg",
					],
				]
			]
		);
		$uploadedFiles = $sut->getUploadedFiles();
		self::assertCount(2, $uploadedFiles);
	}

	public function testWithUploadedFilesFullToEmpty() {
		$sutFull = self::getServerRequest(
			"post",
			"/example",
			[],
			[],
			[
				"files" => [
					"file1" => [
						"name" => "myfile.txt",
					]
				]
			]
		);
		$sutEmpty = $sutFull->withUploadedFiles([]);
		self::assertEmpty($sutEmpty->getUploadedFiles());
	}

	public function testWithUploadedFilesEmptyToFull() {
		$sutEmpty = self::getServerRequest(
			"post",
			"/example",
			[],
			[],
			[
				"files" => [],
			]
		);
		self::assertEmpty($sutEmpty->getUploadedFiles());

		$file1 = self::createMock(FileUpload::class);
		$file2 = self::createMock(FileUpload::class);

		$sutFull = $sutEmpty->withUploadedFiles([
			$file1,
			$file2,
		]);
		self::assertCount(2, $sutFull->getUploadedFiles());
	}

	public function testGetParsedBodyEmpty() {
		$sut = self::getServerRequest(
			"get",
			"/example"
		);
		self::assertEmpty($sut->getParsedBody());
	}

	public function testGetParsedBody() {
		$sut = self::getServerRequest(
			"post",
			"/example",
			[],
			[],
			[
				"post" => [
					"key1" => "value1",
					"key2" => "value2",
				]
			]
		);

		/** @var InputData $inputData */
		$inputData = $sut->getParsedBody();
		self::assertInstanceOf(InputData::class, $inputData);
		self::assertCount(2, $inputData->asArray());
	}

	public function testWithParsedBody() {
		$sut = self::getServerRequest();
		$inputData = $sut->getParsedBody();
		self::assertEmpty($inputData->asArray());

		$inputDatumArray = [];
		$inputDatumArray []= self::createMock(InputDatum::class);
		$inputDatumArray []= self::createMock(InputDatum::class);

		$sut = $sut->withParsedBody($inputDatumArray);
		self::assertCount(2, $sut->getParsedBody()->asArray());
	}

	public function testGetAttributesEmpty() {
		$sut = self::getServerRequest();
		self::assertEmpty($sut->getAttributes());
	}

	public function testWithAttribute() {
		$sutWithout = self::getServerRequest();
		$sutWith = $sutWithout->withAttribute("testAttr", "testValue");
		self::assertEmpty($sutWithout->getAttributes());
		self::assertCount(1, $sutWith->getAttributes());
	}

	public function testGetAttribute() {
		$sut = self::getServerRequest()
			->withAttribute("testAttr", "testValue");
		self::assertEquals(
			"testValue",
			$sut->getAttribute("testAttr")
		);
	}

	public function testGetAttributeDefault() {
		$sut = self::getServerRequest()
			->withAttribute("testAttr", "testValue");
		self::assertEquals(
			"defaultValue",
			$sut->getAttribute("notExists", "defaultValue")
		);
	}

	public function testGetAttributeWithout() {
		$sut = self::getServerRequest()
			->withAttribute("testAttr1", "testValue1")
			->withAttribute("testAttr2", "testValue2")
			->withAttribute("testAttr3", "testValue3")
			->withoutAttribute("testAttr2");
		self::assertCount(2, $sut->getAttributes());
	}

	protected function getServerRequest(
		string $method = null,
		string $uri = null,
		array $headerArray = [],
		array $serverArray = [],
		array $inputArray = [],
		array $cookieArray = []
	):ServerRequest {
		$method = $method ?? "GET";
		$uri = self::getMockUri($uri ?? "/");
		$headers = self::getMockHeaders($headerArray);
		$serverInfo = self::getMockServerInfo($serverArray);
		$input = self::getMockInput(
			$inputArray["get"] ?? [],
			$inputArray["post"] ?? [],
			$inputArray["files"] ?? []
		);
		$cookieHandler = self::getMockCookieHandler($cookieArray);

		$sut = new ServerRequest(
			$method,
			$uri,
			$headers,
			$serverInfo,
			$input,
			$cookieHandler
		);

		return $sut;
	}

	/** @return MockObject|Uri */
	protected function getMockUri(string $uriPath):MockObject {
		$mock = self::createMock(Uri::class);
		return $mock;
	}

	/** @return MockObject|RequestHeaders */
	protected function getMockHeaders(array $headers = []):MockObject {
		$mock = self::createMock(RequestHeaders::class);
		return $mock;
	}

	/** @return MockObject|ServerInfo */
	protected function getMockServerInfo(array $server = []):MockObject {
		$mock = self::createMock(ServerInfo::class);
		$mock->method("getParams")
			->willReturn($server);
		$mock->method("getQueryParams")
			->willReturnCallback(function()use($server) {
				parse_str($server["QUERY_STRING"], $result);
				return $result;
			});
		$mock->method("withQueryParams")
			->willReturnCallback(function($params)use($mock) {
				$clone = self::createMock(ServerInfo::class);
				$clone->method("getQueryParams")
					->willReturn($params);
				return $clone;
			});
		return $mock;
	}

	/** @return MockObject|Input */
	protected function getMockInput(
		array $get = [],
		array $post = [],
		array $files = []
	):MockObject {
		$bodyArray = [];
		foreach($post as $key => $value) {
			$bodyArray []= self::createMock(InputDatum::class);
		}
		$bodyParameters = self::createMock(BodyInputData::class);
		$bodyParameters->method("asArray")
			->willReturnCallback(function()use(&$bodyArray) {
				return $bodyArray;
			});

		$fileArray = [];
		foreach($files as $file) {
			$fileArray []= self::createMock(FileUpload::class);
		}
		$fileUploadParameters = self::createMock(FileUploadInputData::class);
		$fileUploadParameters->method("getKeys")
			->willReturnCallback(function()use(&$fileArray) {
				return array_keys($fileArray);
			});
		$fileUploadParameters->method("asArray")
			->willReturnCallback(function() use(&$fileArray) {
				return $fileArray;
			});
		$fileUploadParameters->method("remove")
			->willReturnCallback(function(string...$keys)use(&$fileArray, $fileUploadParameters) {
				foreach($keys as $k) {
					unset($fileArray[$k]);
				}
				return $fileUploadParameters;
			});

		$mock = self::createMock(Input::class);
		$mock->method("getAll")
			->willReturnCallback(function($method)use($bodyParameters, $fileUploadParameters) {
				if($method === Input::DATA_FILES) {
					return $fileUploadParameters;
				}
				if($method === Input::DATA_BODY) {
					return $bodyParameters;
				}
			});
		$mock->method("add")
			->willReturnCallback(function(string $key, InputDatum $datum, string $method)use(&$bodyArray, &$fileArray) {
				if($method === Input::DATA_FILES) {
					/** @var FileUpload $datum */
					$fileArray[$key] = [
						"name" => $datum->getClientFilename()
					];
				}
				if($method === INPUT::DATA_BODY) {
					/** @var InputDatum $datum */
					$bodyArray[$key] = $datum;
				}
			});
		return $mock;
	}

	/** @return MockObject|CookieHandler */
	protected function getMockCookieHandler(array $cookies = []):MockObject {
		$mock = self::createMock(CookieHandler::class);
		$mock->method("asArray")
			->willReturn($cookies);
		return $mock;
	}
}