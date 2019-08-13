<?php

namespace Level23\Druid\Dimensions;

interface DimensionInterface
{
    const OUTPUT_TYPE_STRING = "string";

    public function getDimension() : array;


}