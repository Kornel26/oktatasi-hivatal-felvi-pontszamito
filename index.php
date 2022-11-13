<?php

require 'point-calculator.service.php';
require 'homework_input.php';

$calculator = PointCalculator::getInstance();

$array = Array($exampleData0, $exampleData1, $exampleData2, $exampleData3);

for($i = 0; $i < count($array); $i++){
    echo '<p>'.$calculator::Calculate($array[$i]).'</p>';
}

?>
