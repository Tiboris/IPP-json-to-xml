#!/usr/bin/php
<?php
    function help()
    {
        $help=
        "
    \t--input=filename 
    \t\t(UTF-8) in json\n\n
    \t--outpu=filename 
    \t\t(UTF-8) in XML\n\n
    \t-h=subst    
    \t\tve jméně elementu odvozeném z dvojice jméno-hodnota nahraďte každý nepovolený
    \t\tznak ve jméně XML značky řetězcem subst. Implicitně (i při nezadaném parametru -h) uvažujte
    \t\tnahrazování znakem pomlčka (-). Vznikne-li po nahrazení invalidní jméno XML elementu,
    \t\tskončete s chybou a návratovým kódem 51\n\n
    \t-n
    \t\tnegenerovat XML hlavičku 1 na výstup skriptu (vhodné například v případě kombinování více výsledků)\n\n
    \t-r=root-element
    \t\tjméno párového kořenového elementu obalujícího výsledek. Pokud nebude
    \t\tzadán, tak se výsledek neobaluje kořenovým elementem, ač to potenciálně porušuje validitu
    \t\tXML (skript neskončí s chybou). Zadání řetězce root-element vedoucího na nevalidní XML
    \t\tznačku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).\n\n
    \t--array-name=array-element  
    \t\ttento parametr umožní přejmenovat element obalující pole
    \t\tz implicitní hodnoty array na array-element. Zadání řetězce array-element vedoucího na
    \t\tnevalidní XML značku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).\n\n
    \t--item-name=item-element    
    \t\tanalogicky, tímto parametrem lze změnit jméno elementu pro
    \t\tprvky pole (implicitní hodnota je item). Zadání řetězce item-element vedoucího na nevalidní
    \t\tXML značku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).\n\n
    \t-s  
    \t\thodnoty (v dvojici i v poli) typu string budou transformovány na textové elementy místo atributů.\n\n
    \t-i  
    \t\thodnoty (v dvojici i v poli) typu number budou transformovány na textové elementy místo atributů.\n\n
    \t-l  
    \t\thodnoty literálů (true, false, null) budou transformovány na elementy <true/>,<false/> a <null/> místo na atributy\n\n
    \t-c  
    \t\taktivuje překlad problematických znaků.\n\n
    \t-a, --array-size    
    \t\tu pole bude doplněn atribut size s uvedením počtu prvků v tomto poli\n\n
    \t--start=n   
    \t\tinicializace inkrementálního čitače pro indexaci prvků pole na zadané kladné celé 
    \t\tčíslo n včetně nuly (implicitně n = 1)\n
    \t\t(nutno kombinovat s parametrem --index-items, jinak chyba s návratovým kódem 1)\n\n
    \t-t, --index-items   
    \t\tke každému prvku pole bude přidán atribut index s určením indexu prvku
    \t\tv tomto poli (číslování začíná od 1, pokud není parametrem --start určeno jinak).";
        
        echo $help;
        exit(0);
    }

    function err($errcode) 
    {
        echo "Program error, exit code '" . $errcode . "', type '--help' for more info.\n" ; // stderr
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

        if ($count == 1) { 
            return false;
        }
        if ( ( $count == 2 ) && ( $args[1] === "--help" ) ) {
            help();
        }
        for ( $i = 1; $i <= $count-1; $i++ ) 
        { 
            if( ereg($long_opt_rex_val, $args[$i], $option) || ereg($shrt_opt_rex_val, $args[$i], $option) )
            {
                if ( ! isset($parsing[$option[1]]) && ( $option[2] != null ) ) { 
                    $parsing[$option[1]] = $option[2];
                }
                else {               
                    return false;
                }
            }
            elseif ( ereg($shrt_opt_rex, $args[$i], $option) || ereg($long_opt_rex, $args[$i], $option) )
            {
                if ( ! isset($parsing[$option[1]]) ) {
                    $parsing[$option[1]] = true;
                }
                else {
                    return false;
                }
            }
            else {
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
        if ( $args === false ) { 
            return false;
        }
        if ( isset( $args['t'] ) && isset( $args['index-items'] ) ) {
            return false;
        }
        if ( isset( $args['start'] ) ) 
        {
            if ( ! isset( $args['t'] ) && ! isset( $args['index-items'] ) ) {
                echo "asdas";
                return false;
            }
            if ( ( ereg("^[0-9]*$", $args['start']) ) === false ) {
                return false;
            }
            else {
                $args['start']=(int)$args['start']; 
            }
        }
        else {
            $args['start']=1;
        }
        if ( ! isset( $args['h'] ) ) {
            $args['h'] = "-"; 
        }
        if ( ! isset( $args['array-name'] ) ) {
            $args['array-name']="array";
        }
        if ( ! isset( $args['item-name'] ) ) {
            $args['item-name']="item";
        }
        if ( isset($args['array-size']) && $args['a'] ) {
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
        $writer->openURI(realpath($args['output']));
        if ( ! isset($args['n']) ) {
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
            if ( is_object( $value) ) {
                write_xml($writer, $value, $args);
            }
            else if ( is_array( $value) ) { 
                write_array($writer, $value, $args); 
            }
            else {
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
        if ( isset($args['array-size']) || isset($args['a']) ) {
            $writer->writeAttribute('size', count($array));    
        }
        $index = $args['start'];
        foreach ($array as $key => $value)
        {
            $writer->startElement($args['item-name']);
            if ( isset( $args['index-items']) || isset( $args['t']) ) {
                $writer->writeAttribute('index', $index++);  
            }
            if ( is_object( $value) ) {
                write_xml($writer, $value, $args);
            }
            else if ( is_array( $value) ) { 
                write_array($writer, $value, $args);
            }
            else {
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
    if ( ( $args = check_args( parse_args($argv, $argc) ) ) === false ) {
        err(1);
    }
    if ( ( $json_input_path = realpath($args['input']) ) == NULL ) {
        err(2);
    }
    if ( ( $json_input = file_get_contents($json_input_path) ) === false ) {
        err(2);
    }
    if ( ( $xml_output = fopen($args['output'], 'w')) === false ) {
        err(3);
    }
    else {
        fclose($xml_output);
    }
    if( ! is_array($json_input = json_decode($json_input, false)) && ! is_object($json_input) ) {
        err(4);
    }
    // starting writer
    write($json_input, $args);
    // end of script    
?>