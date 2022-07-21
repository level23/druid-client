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
    protected $password;

    /**
     * HttpInputSource constructor.
     *
     * @param string[]             $uris
     * @param string|null          $username
     * @param null|string|string[] $password
     */
    public function __construct(array $uris, ?string $username = null, $password = null)
    {
        $this->uris     = $uris;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return array<string,string|string[]>
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

        return $response;
    }
}