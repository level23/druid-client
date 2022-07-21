<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use InvalidArgumentException;

class CombineInputSource implements InputSourceInterface
{
    /**
     * @var \Level23\Druid\InputSources\InputSourceInterface[]
     */
    protected array $inputSources;

    /**
     * @param array<\Level23\Druid\InputSources\InputSourceInterface> $inputSources
     */
    public function __construct(array $inputSources)
    {
        // Validate the input sources.
        foreach ($inputSources as $inputSource) {
            if (!$inputSource instanceof InputSourceInterface) {
                throw new InvalidArgumentException('Only input sources are allowed!');
            }
        }

        $this->inputSources = $inputSources;
    }

    /**
     * @return array<string,string|array<array<string,string|array<mixed>|bool|int>>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'combining',
            'delegates' => array_map(
                fn(InputSourceInterface $inputSource) => $inputSource->toArray(),
                $this->inputSources
            ),
        ];
    }
}