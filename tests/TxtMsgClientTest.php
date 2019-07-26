<?php

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Support\Collection;
use Level23\TxtMsg\Client as TxtMsgClient;
use Level23\TxtMsg\Exceptions\CommunicationException;
use Level23\TxtMsg\Exceptions\ErrorResponseException;
use Level23\TxtMsg\Response\Message;
use PHPUnit\Framework\TestCase;

class TxtMsgClientTest extends TestCase
{
    /**
     * @var \GuzzleHttp\Client|\Mockery\MockInterface
     */
    protected $guzzle;

    public function setUp(): void
    {
        parent::setUp();

        $this->guzzle = Mockery::mock(GuzzleHttpClient::class);;
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testFetch()
    {
        $numbers = [
            "211178920165",
            "32468901328",
            "6477051191472",
            "77625310172",
            "905102661067",
        ];

        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function ($url) use ($numbers) {
            $this->assertEquals("https://api.txtmsg.io/api/fetch?country=nl&operator=vodafone&amount=30", $url);

            return new GuzzleResponse(
                200,
                [],
                json_encode([
                    "success"    => true,
                    "identifier" => "66:vli6hc:Th",
                    "numbers"    => $numbers,
                ]) ?: ""
            );
        });

        $client   = new TxtMsgClient(5, $this->guzzle);
        $response = $client->fetch("nl", "vodafone", 30);

        $this->assertEquals("66:vli6hc:Th", $response->identifier);
        $this->assertEquals(
            new Collection($numbers),
            $response->numbers
        );
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testIncorrectJsonResponse()
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () {
            return new GuzzleResponse(
                200,
                [],
                "{ 'success': true, 'numbers" // incorrect json
            );
        });

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Error, we failed to decode the given response:');

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->fetch("nl", "vodafone", 30);
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testFetchIncorrectHttpResponse()
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () {
            throw new ServerException(
                "Something went wrong!",
                new GuzzleRequest("GET", "http://api.txtmsg.io"),
                new GuzzleResponse(
                    500,
                    [],
                    json_encode([
                        "success" => false,
                        "error"   => "You did something stupid.",
                    ]) ?: ""
                )
            );
        });

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage("You did something stupid.");

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->fetch("", "", 30);
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testFetchTimeoutResponse()
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () {
            throw new ConnectException(
                "cURL error 28: Operation timed out after 1000 milliseconds with 0 bytes received",
                new GuzzleRequest("GET", "http://api.txtmsg.io")
            );
        });

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage("We failed to do an api call to url");

        $client = new TxtMsgClient(1, $this->guzzle);
        $client->fetch("nl", "vodafone", 30);
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testStatus()
    {
        $messages = [
            [
                "message"    => "66:vli6hc:Th dolorem possimus atque id saepe magni eaque beatae quas et",
                "reward_eur" => 3.28,
                "sender"     => "221336371",
                "number"     => "521615446680",
            ],
            [
                "message"    => "66:vli6hc:Th assumenda architecto sint ullam natus omnis sequi natus aspernatur id",
                "reward_eur" => 0.041,
                "sender"     => "221336371",
                "number"     => "911827968271",
            ],
        ];

        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function ($url) use ($messages) {
            $this->assertEquals("https://api.txtmsg.io/api/status?identifier=" . urlencode("66:vli6hc:Th"), $url);

            return new GuzzleResponse(
                200,
                [],
                json_encode([
                    "success"    => true,
                    "identifier" => "66:vli6hc:Th",
                    "messages"   => $messages,
                    "totals"     => [
                        "messages"  => 2,
                        "total_eur" => 3.89,
                    ],
                ]) ?: ""
            );
        });

        $client   = new TxtMsgClient(5, $this->guzzle);
        $response = $client->status("66:vli6hc:Th");

        $this->assertEquals("66:vli6hc:Th", $response->identifier);
        $this->assertEquals(2, $response->totalMessages);
        $this->assertEquals(3.89, $response->totalRewardEur);
        $this->assertInstanceOf(Collection::class, $response->messages);

        foreach ($response->messages as $index => $message) {
            $this->assertInstanceOf(Message::class, $message);

            /**
             * @var \Level23\TxtMsg\Response\Message $message
             */
            $this->assertEquals($messages[$index]['message'], $message->message);
            $this->assertEquals($messages[$index]['sender'], $message->sender);
            $this->assertEquals($messages[$index]['number'], $message->number);
            $this->assertEquals($messages[$index]['reward_eur'], $message->rewardEur);
        }
    }

    /**
     * Return different combinations of json response with fields missing. We expect an exception for all of these
     * cases.
     *
     * @return array
     */
    public function provideMissingJsonFieldsForFetch(): array
    {
        return [
            ['{ "identifier": "66:vli6hc:Th", "numbers": [ "211178920165","32468901328" ] }'],
            ['{ "success": true, "numbers": [ "211178920165","32468901328" ] }'],
            ['{ "success": true, "identifier": "66:vli6hc:Th" }'],
        ];
    }

    /**
     * @dataProvider provideMissingJsonFieldsForFetch
     *
     * @param string $json
     *
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testFetchJsonFieldsMissing(string $json)
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () use ($json) {
            return new GuzzleResponse(200, [], $json);
        });

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage("We expect the field");

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->fetch("nl", "vodafone", 30);
    }

    /**
     * Return different combinations of json response with fields missing. We expect an exception for all of these
     * cases.
     *
     * @return array
     */
    public function provideMissingJsonFieldsForStatus(): array
    {
        return [
            ['{ "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "sender": "221336371", "number": "521615446680" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "number": "521615446680" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371" } ], "totals": { "messages": 2, "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ] }'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ], "totals": { "total_eur": 3.89 }}'],
            ['{ "success": true, "identifier": "66:vli6hc:Th", "messages": [ { "message": "66:vli6hc:Th dolorem", "reward_eur": 1.94, "sender": "221336371", "number": "521615446680" } ], "totals": { "messages": 2 }}'],
        ];
    }

    /**
     * @dataProvider provideMissingJsonFieldsForStatus
     *
     * @param string $json
     *
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testStatusJsonFieldsMissing(string $json)
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () use ($json) {
            return new GuzzleResponse(200, [], $json);
        });

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage("We expect the field");

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->status("66:vli6hc:Th");
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testSuccessIsFalseStatusResponse()
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () {

            return new GuzzleResponse(
                200,
                [],
                json_encode([
                    "success" => false,
                    "error"   => "Something went wrong",
                ]) ?: ""
            );
        });

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage("Something went wrong");

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->status("66:vli6hc:Th");
    }

    /**
     * @throws \Level23\TxtMsg\Exceptions\CommunicationException
     * @throws \Level23\TxtMsg\Exceptions\ErrorResponseException
     */
    public function testSuccessIsFalseFetchResponse()
    {
        $this->guzzle->shouldReceive("get")->once()->andReturnUsing(function () {

            return new GuzzleResponse(
                200,
                [],
                json_encode([
                    "success" => false,
                    "error"   => "Something went wrong",
                ]) ?: ""
            );
        });

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage("Something went wrong");

        $client = new TxtMsgClient(5, $this->guzzle);
        $client->fetch("nl", "vodafone", 30);
    }

    /**
     * Just a stupid test to hit 1 line of code which is untestable.
     */
    public function testWeWant100Percent()
    {
        new TxtMsgClient(5);
        $this->assertTrue(true);
    }
}