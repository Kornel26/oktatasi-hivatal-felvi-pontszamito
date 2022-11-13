<?php

class PointCalculator
{
    private static $instance = null;
    private static $MANDATORY_SUBJECTS = ['magyar nyelv és irodalom', 'történelem', 'matematika'];
    private static $MANDATORY_SUBJECTS_COUNT;
    //TODO: Make these an enum/json collection rather than separate variables
    //TODO: Refactor names
    private static $MANDATORY_ARRAY_KEY = 'erettsegi-eredmenyek';
    private static $MANDATORY_COLUMN_KEY = 'nev';
    private static $THRESHOLD_COLUMN_KEY = 'eredmeny';
    private static $THRESHOLD_VALUE = 20;
    private static $UNIVERSITY_DATA;
    private static $UNIVERSITY_NAME_KEY = 'egyetem';
    private static $UNIVERSITY_FACULTY_KEY = 'kar';
    private static $UNIVERSITY_MAYOR_KEY = 'szak';
    private static $REQUIRED_COLUMN_KEY = 'kotelezo';
    private static $REQUIRED_LEVEL_COLUMN_KEY = 'kotelezo-szint';
    private static $LEVEL_COLUMN_KEY = 'tipus';
    private static $OPTIONAL_COLUMN_KEY = 'valaszthato';
    private static $EXTRA_POINTS_COLUMN_KEY = 'tobbletpontok';
    private static $CALCULATION_FAILED_STRING = 'Calculation is not possible!';

    private function __construct()
    {
        self::$MANDATORY_SUBJECTS_COUNT = count(self::$MANDATORY_SUBJECTS);
        require 'mock-universities.php';
        self::$UNIVERSITY_DATA = $universityData;
    }
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PointCalculator();
        }
        return self::$instance;
    }

    public static function Calculate($data)
    {
        if (
            !self::hasAllMandatorySubjects(array_column($data[self::$MANDATORY_ARRAY_KEY], self::$MANDATORY_COLUMN_KEY)) ||
            !self::allSubjectsAboveThreshold($data[self::$MANDATORY_ARRAY_KEY])
        ) {
            return self::$CALCULATION_FAILED_STRING;
        }

        $universityRequirements = self::getUniversityRequirements($data['valasztott-szak']);
        if ($universityRequirements === null)
            return self::$CALCULATION_FAILED_STRING;

        if (self::hasRequiredSubjectAndLevel($data[self::$MANDATORY_ARRAY_KEY], $universityRequirements)) {
            return self::$CALCULATION_FAILED_STRING;
        }
        //TODO: Another check for optional subject

        return self::CalculateBase($data[self::$MANDATORY_ARRAY_KEY], $universityRequirements) + self::CalculateExtra($data, $universityRequirements);
    }

    private static function CalculateBase($data, $universityRequirements)
    {
        $points = 0;
        $mandatory = self::findObjectByValue($data, self::$MANDATORY_COLUMN_KEY, $universityRequirements[self::$REQUIRED_COLUMN_KEY]);
        $points += intval($mandatory[self::$THRESHOLD_COLUMN_KEY]);

        $required = array_intersect(array_column($data, self::$MANDATORY_COLUMN_KEY), $universityRequirements[self::$OPTIONAL_COLUMN_KEY]);
        //TODO: Make this faster
        $max = 0;
        foreach ($required as $element) {
            $current = intval(self::findObjectByValue($data, 'nev', $element)[self::$THRESHOLD_COLUMN_KEY]);
            if ($current > $max) {
                $max = $current;
            }
        }
        $points += $max;

        return $points * 2;
    }

    private static function findObjectByValue($array, $key, $value)
    {
        foreach ($array as $element) {
            if ($value === $element[$key]) {
                return $element;
            }
        }
        return false;
    }

    private static function CalculateExtra($data, $universityRequirements)
    {
        $points = 0;
        $mandatory = self::findObjectByValue($data[self::$MANDATORY_ARRAY_KEY], self::$MANDATORY_COLUMN_KEY, $universityRequirements[self::$REQUIRED_COLUMN_KEY]);
        //TODO: Move string constant
        if ($mandatory[self::$LEVEL_COLUMN_KEY] === 'emelt')
            $points += 50;

        $extra = $data[self::$EXTRA_POINTS_COLUMN_KEY];
        //TODO: Make this scaleable
        //TODO: Move string constants
        if (self::findObjectByValue($extra, 'tipus', 'C1')) {
            $points += 40;
        } elseif (self::findObjectByValue($extra, 'tipus', 'B2')) {
            $points += 28;
        }

        return ($points > 100) ? 100 : $points;
    }

    private static function hasAllMandatorySubjects($data)
    {
        return count(array_intersect(self::$MANDATORY_SUBJECTS, $data)) === self::$MANDATORY_SUBJECTS_COUNT;
    }

    private static function allSubjectsAboveThreshold($data)
    {
        $above = 1;
        foreach ($data as $values) {
            $above = $above && (intval($values[self::$THRESHOLD_COLUMN_KEY]) > self::$THRESHOLD_VALUE);
        }
        return $above;
    }

    private static function hasRequiredSubjectAndLevel($data, $universityRequirements)
    {
        //TODO
    }

    private static function getUniversityRequirements($data)
    {
        foreach (self::$UNIVERSITY_DATA as $values) {
            if (
                $values[self::$UNIVERSITY_NAME_KEY] === $data[self::$UNIVERSITY_NAME_KEY] &&
                $values[self::$UNIVERSITY_FACULTY_KEY] === $data[self::$UNIVERSITY_FACULTY_KEY] &&
                $values[self::$UNIVERSITY_MAYOR_KEY] === $data[self::$UNIVERSITY_MAYOR_KEY]
            ) {
                return $values;
            }
        }
        return null;
    }

}

?>
