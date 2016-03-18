#!/usr/bin/php
<?php
    function diffArrays($aArray1, $aArray2) 
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) 
        {
            if (array_key_exists($mKey, $aArray2)) 
            {
                if (is_array($mValue)) 
                {
                    $aRecursiveDiff = diffArrays  ($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
                } 
                else 
                {
                    if ($mValue != $aArray2[$mKey]) 
                    {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } 
            else 
            {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    error_reporting(0);

    $script = "../jsn.php";
    $testDir = getcwd() . "/";
    $tmpDir = $testDir . "tmp/";

    $consoleOptions = getopt("", array("clean", "extend"));

    $tests = array_diff(scandir($testDir . "tests"), array('..', '.'));
    $results = array_diff(scandir($testDir . "results"), array('..', '.'));

    include($testDir . "commands.php");

    if (isset($consoleOptions["extend"])) 
    {
        $commands = $commands + $extendCommands;
    }

    $total = count($commands);
    $good = 0; $bad = 0;

    $time_start = microtime(true);  
    foreach ($commands as $name => $opts) 
    {
        $jsonName = $name . ".json";
        $xmlName = $name . ".xml";
        $code = $opts[0];
        $options = $opts[1];

        $paths = " --input=" . $testDir . "tests/" . $jsonName . " --output=" . $tmpDir . $xmlName;
        $cmd = "php -d open_basedir=\"\" " . $script . $paths . " " . $options; # get options from command

        $errOutput = exec($cmd, $execOutput, $returnCode);

        if ($returnCode != $code) 
        { # code from commands
            echo "[ERR] Test " . $name . " failed with code: " . $returnCode . " expected: " . $code . "\n";
            $bad++;
            if ( $bad == 1) 
            {
                $failedTests .= "$name";
            } 
            else
            {
                $failedTests .= ", $name";
            }
            continue;
        } 
        elseif ($returnCode == $code && $returnCode != 0) 
        {
            echo "[OK] Test " . $name . " passed\n";
            $good++;
            continue;
        }
        if ( ($shouldBe = file_get_contents($testDir . "results/" . $xmlName)) === false) {
            echo "Error loading result files\n";
            die(1);
        }
        if (($is = file_get_contents($tmpDir . $xmlName)) === false ) {
            echo "Error loading generated files\n";
            die(1);
        }

        $shouldBeDoc = new DOMDocument();
        $isDoc = new DOMDocument();

        $shouldBeDoc->loadXML("<__ROOT__>" . str_replace(array("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "\n"), "", $shouldBe) . "</__ROOT__>");
        $isDoc->loadXML("<__ROOT__>" . str_replace(array("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "\n"), "", $is) . "</__ROOT__>");

        $shouldBe = json_decode(json_encode((array)simplexml_import_dom($shouldBeDoc)), true);
        $is = json_decode(json_encode((array)simplexml_import_dom($isDoc)), true);

        $delta = diffArrays($shouldBe, $is);

        if (empty($delta)) 
        {
            echo "[OK] Test " . $name . " passed\n";
            $good++;
        } 
        else 
        {
            echo "[ERR] Test " . $name . " failed\n";
            var_dump($delta);
            $bad++;
            if ( $bad == 1) 
            {
                $failedTests .= "$name";
            } 
            else
            {
                $failedTests .= ", $name";
            }
        }
        if (isset($consoleOptions["clean"])) 
        {
            unlink($tmpDir . $xmlName);
        }
    }
    $diffTime = microtime(true) - $time_start;
    $perc = round($good/$total*100, 2);
    echo "\nSummary: passed/total >> " . $good . "/" . $total . " << in " . $diffTime . " seconds";
    if ( $bad == 1 ) 
    {
        echo "\nWith ". $bad ." error:\nTEST: $failedTests";
    } 
    elseif ( $bad > 1 )
    {
        echo "\nWith ". $bad ." errors:\nTESTS: $failedTests";
    }
    echo "\nSuccess: ". $perc . " %\n";
    die( $bad );
?>