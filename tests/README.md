# JSON2XML-Tests
Tests for JSON2XML project on fit vutbr

##Usage:
- clone repo into your project folder
- `cd JSON2XML-Tests/`
- `php test.php`
- on Merlin server `php -d open_basedir="" test.php`

##Options:
- --clean - automatically clean tmp folder after test
- --extend - starts extended tests

##Outputs:
- /tmp - outputs from your script
- console - [ERR] on error or [OK] when everything is ok
