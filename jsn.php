#!/usr/bin/php
<?php
    function help()
    {
        $help=
        "
        --input=filename 
        \t(UTF-8) input has to be in json format if not set script take stdin\n
        --outpu=filename 
        \t(UTF-8) output will be in XML if not set script will use stdout\n
        -h=subst    
        \tve jméně elementu odvozeném z dvojice jméno-hodnota nahraďte každý 
        \tnepovolený znak ve jméně XML značky řetězcem subst. Implicitně 
        \t(i při nezadaném parametru -h) uvažujte nahrazování znakem pomlčka (-)
        \tVznikne-li po nahrazení invalidní jméno XML elementu, skončete s 
        \tchybou a návratovým kódem 51\n
        -n
        \tnegenerovat XML hlavičku 1 na výstup skriptu (vhodné například v 
        \tpřípadě kombinování více výsledků)\n
        -r=root-element
        \tjméno párového kořenového elementu obalujícího výsledek. Pokud nebude
        \tzadán, tak se výsledek neobaluje kořenovým elementem, ač to 
        \tpotenciálně porušuje validitu XML (skript neskončí s chybou). Zadání 
        \třetězce root-element vedoucího na nevalidní XML značku ukončí skript s
        \tchybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).\n
        --array-name=array-element  
        \ttento parametr umožní přejmenovat element obalující pole
        \tz implicitní hodnoty array na array-element. Zadání řetězce 
        \tarray-element vedoucího na nevalidní XML značku ukončí skript s chybou
        \ta návratovým kódem 50 (nevalidní znaky nenahrazujte).\n
        --item-name=item-element    
        \tanalogicky, tímto parametrem lze změnit jméno elementu pro prvky pole 
        \t(implicitní hodnota je item). Zadání řetězce item-element vedoucího 
        \tna nevalidní XML značku ukončí skript s chybou a návratovým kódem 50.\n
        -s  
        \thodnoty (v dvojici i v poli) typu string budou transformovány 
        \tna textové elementy místo atributů.\n
        -i  
        \thodnoty (v dvojici i v poli) typu number budou transformovány 
        \tna textové elementy místo atributů.\n
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
        \tinitialization of counter for option -t/ --index-items\n";
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

        if ($count == 1) 
        { 
            return false;
        }
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
        if ( ! isset( $args['item-name'] ) ) 
        {
            $args['item-name'] = "item";
        }
        if ( isset($args['array-size']) && $args['a'] ) 
        {
            return false;
        } 
        return $args;
    }
    /*
    ** initialization of writer and calling write_xml function
    **/
    function write($json_input, $args)
    {
        $writer = new XMLWriter();
        $writer->openURI($args['output']);
        if ( ! isset($args['n']) ) 
        {
            $writer->startDocument('1.0','UTF-8');
        }
        $writer->setIndent(true);
        write_xml( $writer, $json_input, $args );

    }
    /*
    ** recursively called for writing objects
    */
    function write_xml($writer, $json_input, $args)
    {
        foreach ($json_input as $key => $value) 
        {
            $writer->startElement($key); 
            if ( is_object($value) ) 
            {
                write_xml($writer, $value, $args);
            } 
            elseif ( is_array($value) ) 
            { 
                write_array($writer, $value, $args); 
            } 
            else 
            {
                $writer->text($value);
            }
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
            if ( is_object($value) ) 
            {
                write_xml($writer, $value, $args);
            } 
            elseif ( is_array($value) ) 
            { 
                write_array($writer, $value, $args);
            } 
            else 
            {
                $writer->text($value);
            }
            $writer->endElement();
        }
        $writer->endElement(); 
    }
    /*
    ** end of function declaration 
    **/

    /*
    ** Input / Output chceking
    **/
    if ( ( $args = check_args( parse_args($argv, $argc) ) ) === false ) 
    {
        err(1);
    }
    if ( ( $json_input = file_get_contents($args['input']) ) === false ) 
    {
        err(2);
    }
    if ( ( $xml_output = fopen($args['output'], 'w')) === false ) 
    {
        err(3);
    } 
    else 
    {
        fclose($xml_output);
    }
    if( ! is_object($json_input = json_decode($json_input, false)) && ! is_array($json_input) ) 
    {
        err(4);
    }


    // for debugging
    if (is_object($json_input)) {
        echo "object\n";
    }
    elseif (is_array($json_input)) {
        echo "array\n";
    }
    else{
        echo "ERROR\n";
    }


    // starting writer
    write($json_input, $args);
    // end of script    
?>