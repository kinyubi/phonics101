<?php

namespace ReadXYZ\Display;

use Color;
use Point;
use ReadXYZ\Helpers\Debug;
use ReadXYZ\Helpers\Util;
use VerticalBarChart;
use XYDataSet;

class LearningCurve
{
    public array $data = [];

    public function learningCurveChart($data)
    {      // creates .PNG,  returns filename for <IMG /> tag
        assert(is_array($data));
        assert(count($data) > 0, 'Did not expect an empty graph for Learning Curve graph');

        $this->data = $data;          // save it for others

        require_once dirname(dirname(__DIR__)).'/3rdParty/libChart/classes/libChart.php';
        $chart = new VerticalBarChart(140 + (25 * count($this->data)), 250);
        $chart->getPlot()->getPalette()->setBarColor([new Color(0, 170, 176), new Color(0, 170, 176)]);
        $dataSet = new XYDataSet();
        $i = 1;
        $min = 0;

        //$min = $this->data[0];                      // find the smallest value of the data
        //if(count($this->data) == 1)    $min = 0;     // special case

        foreach ($this->data as $dataPoint) {
            $dataSet->addPoint(new Point($i++, $dataPoint));
            $min = min($min, max($dataPoint - 5, 0));
        }

        $chart->setDataSet($dataSet);
        $chart->bound->setLowerBound($min);
        $chart->setTitle('Time (Seconds)');

        $generatedCache = Util::getPublicPath('generated');
        if (!is_dir($generatedCache)) {
            mkdir($generatedCache);
        }

        $uuid = uniqid();      // can't reuse the filename since multiple users
        $filename = Util::getPublicPath("generated/$uuid.png");    // save to disk
        $urlName = "/generated/$uuid.png";    // refer via browser

        $chart->render($filename);

        return $urlName;
    }

    public static function cleanUpOldGraphics()
    {
        // clean up old files
        Debug::printNice('LC', 'Called cleanUpOldGraphics()');
        $generatedCache = Util::getPublicPath('generated');
        if (!is_dir($generatedCache)) {
            mkdir($generatedCache);
        }
        if (is_dir($generatedCache)) {       // does the directory exist?
            $dir = dir($generatedCache);    // create a directory object
            while (false !== ($entry = $dir->read())) {
                if ('.' == substr($entry, 0, 1)) {
                    continue;
                }     // skip the . and .. files
                if (filemtime("$generatedCache/$entry") + (60 * 60 * 8) < time()) {  // 8 hours
                    unlink("$generatedCache/$entry");
                }
            }
        }
    }
}
