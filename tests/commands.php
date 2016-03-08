<?php

  $commands = array(
    "1" => array(0, ""),
    "2" => array(0, "-i -s"),
    "3" => array(0, "-i"),
    "4" => array(0, "-r=\"root\""),
    "5" => array(0, "-r=\"some_shit\" -s -l"),
    "6" => array(0, "-l -i -s"),
    "7" => array(0, "-h=_ -l"),
    "8" => array(0, "-h=qq -r=root --array-name=wow_array"),
    "9" => array(0, "-r=my_root --item-name=a -i"),
    "10" => array(0, "-n -a -t -l -i"),

    "11" => array(1, "--help -s"),
    "12" => array(1, "--help -h=_"),
    "13" => array(2, ""),
    "14" => array(1, "-r=root --h=_"),
    "15" => array(1, "--start=5"),
    "16" => array(1, "--start=abcd --index-items"),
    "17" => array(1, "--start=-10 --index-items"),

    "18" => array(4, ""),
    "19" => array(4, "-h=_ -l -i"),
    "20" => array(4, "-r=root"),

    "21" => array(50, "-r=\"<root>\""),
    "22" => array(50, "-r=\"&name\""),
    "23" => array(50, "-r=root --array-name=\"<array>\""),
    "24" => array(50, "-r=root --item-name=-and"),
    "25" => array(51, "-h=-"),
    "26" => array(51, "-h=\"'\""),
    "27" => array(51, "-h=\"@@\""),
    "28" => array(51, "-h=21"),
    "29" => array(51, "-h=\"<>\""),
    "30" => array(51, "-h=\"*0*\""),
    
    "31" => array(0, "-h=\"@@\" -s -i"),
    "32" => array(0, "-s"),
    "33" => array(0, "-s -i"),
    "34" => array(0, "-s -l -a"),
    "35" => array(0, "-i -l -a -t --start=5"),
    "36" => array(0, "-s -i -l -r=root -a"),
    "37" => array(0, "-a -h=_"),
    "38" => array(0, "-t -i -h=qq"),
    "39" => array(0, "-i -t --start=10 -h=&"),
    "40" => array(0, "-s -i -r=_root_ -h=\":\""),
    "41" => array(51, "-s"),
    "42" => array(0, "-s -h=Q"),

    "43" => array(0, "-s -i"),
    "44" => array(0, "-l -r=root -h=_"),
    "45" => array(0, "-i"),
    "46" => array(0, "-a -t -r=root"),
    "47" => array(0, "-i -s -r=root -a"),
    "48" => array(0, "-h=_ -s -l --index-items --start=33 -a"),
    
    "49" => array(0, "-c"),
    "50" => array(0, "-c -i -s"),
    "51" => array(0, "-c -h=SUBS"),
    "52" => array(0, "")
  );

  $extendCommands = array(
    "100" => array(0, "--index-items --padding"),
    "101" => array(1, "--padding"),
    "102" => array(0, "--index-items --start=9 --padding")
  );
?>
