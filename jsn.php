#!/usr/bin/php
<?php
#JSN:xdudla00
    function help()
    {
        $help =
        "NAME\n\tjsn.php - script to converting *.json to *.xml format\n\nSYNOPSYS
        jsn.php [OPTION]...\n\nOPTIONS\n
        --input=filename 
        \t(UTF-8) input has to be in json format if not set script take stdin\n
        --outpu=filename 
        \t(UTF-8) output will be in XML if not set script will use stdout\n
        -h=subst    
        \tin name of element are invalid characters replaced always with 'subst'  
        \tdefault: subst=\"-\"\n
        -n
        \tscript will not generate XML header\n
        -r=root-element
        \twhen set script will generate result into 'root-element' tag\n
        --array-name=array-element  
        \tallow to generate every array element into 'array-element' tag, 
        \tdefault: array-element='array'\n
        --item-name=item-element    
        \tallow to generate every item of array into 'item-element' tag
        \tdefault: item-element='item'\n
        -s  
        \tstring value from tag will be replaced with text elements\n
        -i  
        \tnumeric values from tag will be replaced with text elements\n
        -l  
        \tvalues of literals (true, false, null) will be transformed into 
        \t<true/>, <false/>, <null/> instead of attributes\n
        -c  
        \ttranslation of problematic characters\n
        -a, --array-size    
        \tattribute size will be added to an array elements\n
        -t, --index-items   
        \tto each element of array will be added atribute index
        \tstarts from '1' unless parameter --start is set.\n
        --start=n   
        \tinitialization of counter for option -t, --index-items
        \tcauses error when -t or --index-items option is not set\nAUTHOR
        Written by Tibor Dudlák.\nERROR EXIT CODES
        1\t: Invalid options or their combinations
        2\t: Invalid input file
        3\t: Invalid output file
        4\t: Invalid input file format
        50\t: When options --array-name or --array-item contains invalid characters
        51\t: When option -h is set or contains invalid characters\n";
        echo $help;
        exit(0);
    }
    /*
    ** function prints error message with errcode and exits with errcode
    **/
    function err($errcode) 
    {
        fwrite(STDERR, "Program error, exit code '" . $errcode . "', type '--help' for more info.\n"); // stderr
        die($errcode);
    }
    /*
    ** function parse_args returns 
    **    false on wrong argument combination 
    **    calls help when '--help' is second argument 
    **    else return associative array of specified arguments and their value
    **/
    function parse_args($args, $count)
    {
        $shrt_opt_rex       = "^-(n|i|l|c|a|t|s)$";
        $long_opt_rex       = "^--(index-items|array-size)$";
        $shrt_opt_rex_val   = "^-(r|h)=(.*)";
        $long_opt_rex_val   = "^--(input|output|array-name|item-name|start)=(.*)";

        if ( ( $count == 2 ) && ( $args[1] === "--help" ) ) 
        {
            help();
        }
        for ( $i = 1; $i <= $count-1; $i++ ) 
        { 
            if( ereg($long_opt_rex_val, $args[$i], $option) || ereg($shrt_opt_rex_val, $args[$i], $option) )
            {
                if ( ! isset($parsing[$option[1]]) && ( $option[2] != null ) ) 
                { 
                    $parsing[$option[1]] = $option[2];
                } 
                else 
                {               
                    return false;
                }
            } 
            elseif ( ereg($shrt_opt_rex, $args[$i], $option) || ereg($long_opt_rex, $args[$i], $option) )
            {
                if ( ! isset($parsing[$option[1]]) ) 
                {
                    $parsing[$option[1]] = true;
                } 
                else 
                {
                    return false;
                }
            } 
            else 
            {
                return false;       
            }
        }
        return $parsing;
    }
    /*
    ** function check_args returns 
    **    false if wrong argument value 
    **    else associative array filled of all needed arguments and their value
    **/
    function check_args($args)
    {
        if ( $args === false ) 
        { 
            return false;
        }
        if ( ! isset( $args['input']) ) 
        {
            $args['input'] = 'php://stdin';
        }
        if (! isset( $args['output']) ) 
        {
            $args['output'] = 'php://stdout';
        }
        if ( isset( $args['t'] ) && isset( $args['index-items'] ) ) 
        {
            return false;
        }
        if ( isset( $args['start'] ) ) 
        {
            if ( ! isset( $args['t'] ) && ! isset( $args['index-items'] ) ) 
            {
                return false;
            }
            elseif ( ( ereg("^[0-9]*$", $args['start']) ) === false ) 
            {
                return false;
            } 
            else 
            {
                $args['start'] = (int) $args['start']; 
            }
        } 
        else 
        {
            $args['start'] = 1;
        }
        if ( ! isset( $args['h'] ) ) 
        {
            $args['h'] = "-"; 
        }
        if ( ! isset( $args['array-name'] ) ) 
        {
            $args['array-name'] = "array";
        }
        else
        {
            $args['array-name'] = check_name($args['array-name'],$args['h'],false);
        }
        if ( ! isset( $args['item-name'] ) ) 
        {
            $args['item-name'] = "item";
        }
        else
        {
            $args['item-name'] = check_name($args['item-name'],$args['h'],false);
        }
        if ( isset($args['array-size']) && $args['a'] ) 
        {
            return false;
        } 
        return $args;
    }
    /*
    ** initialization of writer and calling write_ functions
    **/
    function write($json_input, $args)
    {
        $writer = new XMLWriter();
        $writer->openURI($args['output']);
        $writer->setIndent(true);
        if ( ! isset($args['n']) ) 
        {
            $writer->startDocument('1.0','UTF-8');
        }
        if ( isset($args['r']) ) 
        {
            $writer->startElement(check_name($args['r'], $args['h'], false));
            write_value( $writer, $json_input, $args );
            $writer->endElement();
        }
        else
        {
            write_value( $writer, $json_input, $args );
        }
    }
    /*
    ** recursively called for writing objects
    */
    function write_object($writer, $object, $args)
    {
        foreach ($object as $key => $value) 
        {
            $writer->startElement(check_name($key, $args['h'], true)); 
            write_value($writer, $value, $args);
            $writer->endElement();
        }
    }
    /*
    ** recursively called for writing arrays
    */
    function write_array($writer, $array, $args)
    {
        $writer->startElement($args['array-name']);
        if ( isset($args['array-size']) || isset($args['a']) ) 
        {
            $writer->writeAttribute('size', count($array));    
        }
        $index = $args['start'];
        foreach ($array as $key => $value)
        {
            $writer->startElement($args['item-name']);
            if ( isset( $args['index-items']) || isset( $args['t']) ) 
            {
                $writer->writeAttribute('index', $index++);  
            }
            write_value($writer, $value, $args);
            $writer->endElement();
        }
        $writer->endElement(); 
    }
    /*
    ** function for writing values
    */
    function write_value($writer, $value, $args)
    {       
        if ( is_object($value) ) 
        {
            write_object($writer, $value, $args);
        } 
        elseif ( is_array($value) ) 
        { 
            write_array($writer, $value, $args);
        }
        elseif ( ( is_int($value) || is_float($value) ) ) 
        {
            $value = floor($value);
            if (isset($args['i'])) 
            {
                $writer->text($value);
            }
            else
            {
                $writer->writeAttribute('value', $value );
            }
        }
        elseif ( ( ! isset($args['s']) && is_string($value) ) ) 
        {
            $writer->writeAttribute('value', $value );
        }
        elseif ( ( $value === null || $value === false || $value === true ) ) 
        {
            if (isset($args['l'])) 
            {
                if ( $value === true ) 
                {
                    $writer->writeElement("true");
                }
                elseif ( $value === false ) 
                {
                    $writer->writeElement("false");
                }
                elseif ( $value === null )
                {
                    $writer->writeElement("null");
                }
            }
            else
            {
                if ( $value === true ) 
                {
                    $writer->writeAttribute( "value", "true");
                }
                elseif ( $value === false ) 
                {
                    $writer->writeAttribute( "value", "false");
                }
                elseif ( $value === null )
                {
                    $writer->writeAttribute( "value", "null");
                }
            }  
        }
        elseif (isset($args['c']))
        {
            $writer->text($value);
        }
        else 
        {
            $writer->writeRaw($value);
        }
    }
    /*
    ** function for checking names and replacing invalid characters
    **/
    function check_name($name, $replacement, $allow_replace)
    {
        $start_char_rex = '/^[^\p{L}|\_]/';
        $validity_rex = '/<|>|"|\'|\/|\\|&|&/';
        if ( preg_match($start_char_rex, $name) || preg_match($validity_rex, $name) ) 
        {   // if regex matches there is invalid character
            if ( $allow_replace ) 
            {
                $name = preg_replace($validity_rex , $replacement , $name);
                $name = preg_replace($start_char_rex , $replacement , $name);
                if ( preg_match($start_char_rex, $name) || preg_match($validity_rex, $name) ) 
                {
                    err(51);
                }
            }
            else
            {
                err(50);
            }
        }
        return $name;
    }
    /*
    ** end of function declaration start of Input / Output chceking
    **/
    if ( ( $args = @ check_args( parse_args($argv, $argc) ) ) === false ) 
    {
        err(1);
    }
    if ( ( $json_input = @ file_get_contents($args['input']) ) === false ) 
    {
        err(2);
    }
    if ( ( $xml_output = @ fopen($args['output'], 'w')) === false ) 
    {
        err(3);
    } 
    else 
    {
        @ fclose($xml_output);
    }
    if( ! is_object( $json_input = @ json_decode($json_input, false) ) && ! is_array($json_input) ) 
    {
        err(4);
    }
    // starting writer
    @ write($json_input, $args);
    // end of script    
?>
