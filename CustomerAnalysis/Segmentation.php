<?php

namespace CustomerAnalysis;

/**
 * Class Segmentation
 * @package CustomerAnalysis
 */
class Segmentation
{
    /**
     * @var array
     */
    private $dbRecords;

    /**
     * @var int
     */
    private $segmentsNumber = 3;

    /**
     * @var string
     */
    private $field;

    /**
     * @var array
     */
    private $uv = null;

    /**
     * Segmentation constructor.
     *
     * @param $records
     * @throws \Exception
     */
    public function __construct($records)
    {
        if (!is_array($records) || empty($records))
            throw new \Exception('Data set is not array or empty');

        $this->dbRecords = $records;
    }

    /**
     * @param int $count
     */
    public function setSegmentsCount($count)
    {
        $count = (int)$count;

        if ($count < 3)
            $count = 3;

        $this->segmentsNumber = $count;
    }

    /**
     * Calculation field setter.
     *
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSegments()
    {
        $set = $this->getSortedValues();
        $count = count($set);

        if ($count < $this->segmentsNumber)
            throw new \Exception('Specified number of segments is greater than the data set');

        $segments = [];
        $chunks = array_chunk($set, ceil($count / $this->segmentsNumber));
        foreach ($chunks as $s => $chunk) {
            $segments[] = [
                'id' => $s + 1,
                'min' => (float)min($chunk),
                'max' => (float)max($chunk)
            ];
        }

        return $segments;
    }

    /**
     * @param string $letter
     * @return array
     * @throws \Exception
     */
    public function calculate($letter)
    {
        $segments = $this->getSegments();
        foreach ($this->dbRecords as &$record) {
            $value = $record[$this->field];
            foreach ($segments as $segment) {
                if ($value >= $segment['min'] && $value <= $segment['max']) {
                    $record[$letter] = $segment['id'];
                    break;
                }
            }
        }
        unset($record);

        return $this->dbRecords;
    }

    /**
     * @return array
     */
    private function getSortedValues()
    {
        if ($this->uv === null) {
            $set = array_map(
                function ($record) {
                    return $record[$this->field];
                },
                $this->dbRecords
            );

            sort($set);

            $this->uv = $set;
        }

        return $this->uv;
    }

}

// EOF Segmentation.php