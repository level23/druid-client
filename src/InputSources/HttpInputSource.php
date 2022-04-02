<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class HttpInputSource implements InputSourceInterface
{
    protected array $uris;

    protected ?string $username;

    /**
     * @var null|string|array
     */
    protected $password;

    /**
     * HttpInputSource constructor.
     *
     * @param array             $uris
     * @param string|null       $username
     * @param null|string|array $password
     */
    public function __construct(array $uris, ?string $username = null, $password = null)
    {
        $this->uris     = $uris;
        $this->username = $username;
        $this->password = $password;
    }

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