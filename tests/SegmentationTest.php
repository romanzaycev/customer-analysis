<?php

require_once __DIR__ . '/../CustomerAnalysis/Segmentation.php';

class SegmentationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Data set is not array or empty
     */
    public function testConstructWithWrongSet()
    {
        $foo = new \CustomerAnalysis\Segmentation([]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Specified number of segments is greater than the data set
     */
    public function testNumberOfSegments()
    {
        $foo = new \CustomerAnalysis\Segmentation([['foo' => 1], ['foo' => 2]]);
        $foo->setField('foo');
        $foo->setSegmentsCount(3);
        $foo->getSegments();
    }

    public function testGettingSegments()
    {
        $foo = new \CustomerAnalysis\Segmentation([['foo' => 1], ['foo' => 2], ['foo' => 3]]);
        $foo->setField('foo');
        $foo->setSegmentsCount(3);
        $seg = $foo->getSegments();

        $this->assertEquals(
            $seg,
            [
                [
                    'id' => 1,
                    'min' => 1.0,
                    'max' => 1.0
                ],
                [
                    'id' => 2,
                    'min' => 2.0,
                    'max' => 2.0
                ],
                [
                    'id' => 3,
                    'min' => 3.0,
                    'max' => 3.0
                ]
            ]
        );
    }

    public function testCalculateWithLetter()
    {
        $foo = new \CustomerAnalysis\Segmentation([['foo' => 1], ['foo' => 2], ['foo' => 3]]);
        $foo->setField('foo');
        $foo->setSegmentsCount(3);

        $records = $foo->calculate('X');

        $this->assertEquals(
            $records,
            [
                [
                    'foo' => 1,
                    'X' => 1
                ],
                [
                    'foo' => 2,
                    'X' => 2
                ],
                [
                    'foo' => 3,
                    'X' => 3
                ]
            ]
        );
    }
}

// EOF SegmentationTest.php