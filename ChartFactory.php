<?php

namespace Malwarebytes\AltamiraBundle;

use Altamira\Chart;
use Altamira\ChartIterator;
use Altamira\ChartRenderer;

class ChartFactory {
    protected $library;
    private $logger;


    function __construct($library,$logger) {
        $this->logger=$logger;
        $this->setLibrary($library);

        \Altamira\Config::setConfigFile(__DIR__.'/Resources/config/altamira-config.ini');
    }


    public function setLibrary($library) {
        $this->logger->debug("Altamira library set to ".$library."!");
        $this->library=$library;

        if ($library == \Altamira\JsWriter\Flot::LIBRARY) {
            ChartRenderer::pushRenderer( '\Altamira\ChartRenderer\DefaultRenderer' );
            ChartRenderer::pushRenderer( '\Altamira\ChartRenderer\TitleRenderer' );
        }
    }


    public function createChart($name) {
        return new Chart($name,$this->library);
    }


    public function getChartIterator(array $charts) {
        return new ChartIterator($charts);
    }
}
?>
