#!/usr/bin/php
<?php
    function help()
    {
        echo "THIS IS HELP ?!\n";
        exit(0);
    }

    function err( $errcode ) 
    {
        echo "Program error, exit code '" . $errcode . "' type '--help' for more info.\n" ; // stderr
        exit( $errcode );
    }

    function parse_args( $args, $count )
    {
        $shrt_opt_rex       = "^-(s|n|i|l|c|a|t)$";
        $long_opt_rex       = "^--(index-items|array-size)$";
        $shrt_opt_rex_val   = "^-(r|h)=(.*)";
        $long_opt_rex_val   = "^--(input|output|array-name|item-name|start)=(.*)";

        if ( $count == 1 ) 
            return false;

        for ( $i=1; $i <= $count-1; $i++ ) 
        { 
            if( ereg( $long_opt_rex_val, $args[$i], $option ) || ereg( $shrt_opt_rex_val, $args[$i], $option ) )
            {
                if ( ! isset( $parsing[$option[1]] ) && ( $option[2] != null ) ) 
                    $parsing[$option[1]] = $option[2];
                else               
                    return false;
            }
            elseif ( ereg( $shrt_opt_rex, $args[$i], $option ) || ereg( $long_opt_rex, $args[$i], $option ) )
            {
                if ( ! isset( $parsing[$option[1]] ) )
                    $parsing[$option[1]] = true;
                else               
                    return false;
            }
            else
                return false;       
        }
        return $parsing;
    }

    function check_args( $args )
    {
        if ( $args === false ) 
            return false;
        if ( isset( $args['start'] ) )
        {
            if ( ! isset( $args['index-items'] ) )
                return false;
            if ( ereg( "^[0-9]*$", $args['start'] ) ) 
                $args['start']=(int)$args['start'];
            else
                return false;
        }
        else
            $args['start']=1;
        if ( ! isset( $args['h'] ) ) 
            $args['h'] = "-";       
        return $args;
    }
    /*
    ** end of function declaration 
    **/
    
    if ( ( $argc == 2 ) && ( $argv[1] === "--help" ) ) // iba samostatne???     
        help();
    if ( ( $args = check_args( parse_args( $argv, $argc ) ) ) === false ) 
        err(100);
    if ( ( $json_input = file_get_contents( realpath($args['input']) ) ) === false )
        err(111);
    $json_input = json_decode($json_input,true);
    var_dump($json_input); // WHY null ? 

    /*$json = 
    '{
        "glossary": {
            "title": "example glossary",
            "GlossDiv": {
                "title": "S",
                "GlossList": {
                    "GlossEntry": {
                        "ID": "SGML",
                        "SortAs": "SGML",
                        "GlossTerm": "Standard Generalized Markup Language",
                        "Acronym": "SGML",
                        "Abbrev": "ISO 8879:1986",
                        "GlossDef": {
                            "para": "A meta-markup language, used to create markup languages such as DocBook.",
                            "GlossSeeAlso": ["GML", "XML"]
                        },
                        "GlossSee": "markup"
                    }
                }
            }
        }
    }';*/

    //var_dump(json_decode($json));
    //var_dump(json_decode($json, true));
    
    $writer = new XMLWriter();

    // end of script
/*
    --input=filename (UTF-8) v json
    --outpu=filename (UTF-8) v XML
    
    -h=subst    ve jméně elementu odvozeném z dvojice jméno-hodnota nahraďte každý nepovolený
                znak ve jméně XML značky řetězcem subst. Implicitně (i při nezadaném parametru -h) uvažu-
                jte nahrazování znakem pomlčka (-). Vznikne-li po nahrazení invalidní jméno XML elementu,
                skončete s chybou a návratovým kódem 51
    
    -n      negenerovat XML hlavičku 1 na výstup skriptu (vhodné například v případě kombinování více výsledků)
    
    -r=root-element     jméno párového kořenového elementu obalujícího výsledek. Pokud nebude
                        zadán, tak se výsledek neobaluje kořenovým elementem, ač to potenciálně porušuje validitu
                        XML (skript neskončí s chybou). Zadání řetězce root-element vedoucího na nevalidní XML
                        značku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).
    
    --array-name=array-element  tento parametr umožní přejmenovat element obalující pole
                                z implicitní hodnoty array na array-element. Zadání řetězce array-element vedoucího na
                                nevalidní XML značku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky ne-
                                nahrazujte).
    
    --item-name=item-element    analogicky, tímto parametrem lze změnit jméno elementu pro
                                prvky pole (implicitní hodnota je item). Zadání řetězce item-element vedoucího na nevalidní
                                XML značku ukončí skript s chybou a návratovým kódem 50 (nevalidní znaky nenahrazujte).
    
    -s hodnoty (v dvojici i v poli) typu string budou transformovány na textové elementy místo atributů.
    
    -i hodnoty (v dvojici i v poli) typu number budou transformovány na textové elementy místo atributů.
    
    -l hodnoty literálů (true, false, null) budou transformovány na elementy <true/>,<false/> a <null/> místo na atributy
    
    -c aktivuje překlad problematických znaků.
    
    -a, --array-size u pole bude doplněn atribut size s uvedením počtu prvků v tomto poli
    
    -t, --index-items ke každému prvku pole bude přidán atribut index s určením indexu prvku v tomto poli 
        (číslování začíná od 1, pokud není parametrem --start určeno jinak).

    --start=n   inicializace inkrementálního čitače pro indexaci prvků pole na zadané kladné celé 
                číslo n včetně nuly (implicitně n = 1)
                (nutno kombinovat s parametrem --index-items, jinak chyba s návratovým kódem 1)

    -t, --index-items ke každému prvku pole bude přidán atribut index s určením indexu prvku
            v tomto poli (číslování začíná od 1, pokud není parametrem --start určeno jinak).
    */
?>
