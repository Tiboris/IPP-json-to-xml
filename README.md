# json_to_xml
Script for converting *.json to *.xml files

##Usage:
- `php jsn.php [option]...`
- on Merlin server `php -d open_basedir="" jsn.php`

##Options:
- --input=filename 
	(UTF-8) input has to be in json format if not set script take stdin
 --outpu=filename 
	(UTF-8) output will be in XML if not set script will use stdout
- -h=subst    
	in name of element are invalid characters replaced always with 'subst'  
	default: subst=\"-\"
- -n
	script will not generate XML header\n
- -r=root-element
	when set script will generate result into 'root-element' tag\n
- --array-name=array-element  
	allow to generate every array element into 'array-element' tag, 
	default: array-element='array'\n
- --item-name=item-element    
 	allow to generate every item of array into 'item-element' tag
 	default: item-element='item'\n
- -s  
 	string value from tag will be replaced with text elements\n
- -i  
 	numeric values from tag will be replaced with text elements\n
- -l  
 	values of literals (true, false, null) will be transformed into 
 	<true/>, <false/>, <null/> instead of attributes\n
- -c  
 	translation of problematic characters\n
- -a, --array-size    
 	attribute size will be added to an array elements\n
- -t, --index-items   
 	to each element of array will be added atribute index
 	starts from '1' unless parameter --start is set.\n
- --start=n   
 	initialization of counter for option -t, --index-items
 	causes error when -t or --index-items option is not set\nAUTHOR
