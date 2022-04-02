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

    public function toArray(): array
    {
        $delegates = [];
        foreach ($this->inputSources as $inputSource) {
            $delegates[] = $inputSource->toArray();
        }

        return [
            'type'      => 'combining',
            'delegates' => $delegates,
        ];
    }
}