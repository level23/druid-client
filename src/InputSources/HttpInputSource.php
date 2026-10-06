<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class HttpInputSource implements InputSourceInterface
{
    /**
     * @var string[]
     */
    protected array $uris;

    protected ?string $username;

    /**
     * @var null|string|string[]
     */
    protected string|array|null $password;

    /**
     * @var array<string,string>
     */
    protected array $requestHeaders;

    /**
     * HttpInputSource constructor.
     *
     * @param string[]             $uris
     * @param string|null          $username
     * @param string|string[]|null $password
     * @param array<string,string> $requestHeaders Headers to send with each request. Requires Druid 32 or higher, and
     *                                             the header names must be allowed in the Druid configuration
     *                                             (druid.ingestion.http.allowedHeaders).
     */
    public function __construct(
        array $uris,
        ?string $username = null,
        array|string|null $password = null,
        array $requestHeaders = []
    ) {
        $this->uris           = $uris;
        $this->username       = $username;
        $this->password       = $password;
        $this->requestHeaders = $requestHeaders;
    }

    /**
     * @return array<string,string|string[]|array<string,string>>
     */
    public function toArray(): array
    {
        $response = [
            'type' => 'http',
            'uris' => $this->uris,
        ];

        if (!empty($this->username)) {
            $response['httpAuthenticationUsername'] = $this->username;
        }

        if (!empty($this->password)) {
            $response['httpAuthenticationPassword'] = $this->password;
        }

        if (count($this->requestHeaders) > 0) {
            $response['requestHeaders'] = $this->requestHeaders;
        }

        return $response;
    }
}