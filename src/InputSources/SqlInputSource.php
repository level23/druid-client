<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class SqlInputSource implements InputSourceInterface
{
    /**
     * @var string[]
     */
    protected array $sqls;

    protected bool $foldCase;

    protected string $connectURI;

    protected string $username;

    protected string $password;

    /**
     * SqlInputSource constructor.
     *
     * @param string   $connectURI
     * @param string   $username
     * @param string   $password
     * @param string[] $sqls     List of SQL queries where each SQL query would retrieve the data to be indexed.
     * @param bool     $foldCase Toggle case folding of database column names. This may be enabled in cases where the
     *                           database returns case insensitive column names in query results.
     */
    public function __construct(
        string $connectURI,
        string $username,
        string $password,
        array $sqls,
        bool $foldCase = false
    ) {
        $this->sqls       = $sqls;
        $this->foldCase   = $foldCase;
        $this->connectURI = $connectURI;
        $this->username   = $username;
        $this->password   = $password;
    }

    /**
     * @return array<string,string|true|array<string,string|string[]>>
     */
    public function toArray(): array
    {
        $response = [
            'type'     => 'sql',
            'database' => [
                'type'            => $this->databaseTypeFromConnectUri($this->connectURI),
                'connectorConfig' => [
                    'connectURI' => $this->connectURI,
                    'user'       => $this->username,
                    'password'   => $this->password,
                ],
            ],
            'sqls'     => $this->sqls,
        ];

        if ($this->foldCase) {
            $response['foldCase'] = $this->foldCase;
        }

        return $response;
    }

    protected function databaseTypeFromConnectUri(string $connectURI): string
    {
        return (strpos(strtolower($connectURI), 'postgresql') !== false) ? 'postgresql' : 'mysql';
    }
}