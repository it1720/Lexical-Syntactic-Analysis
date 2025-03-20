<?php
ini_set('display_errors','stderr');
$line_number = 0;
$order = 1;
$language = '.IPPCODE23';

for($i=0; $i < $argc; $i++){
    if(strcmp($argv[$i],"--help") == 0){
        if($argc > 2){
            exit(10);
        }
        echo("Skript typu filtr nacte ze standardniho vstupu zdrojovy kod v IPP-code23, zkontroluje lexikalni a syntaktickou spravnost kodu a vypise na standardnivystup XML reprezentaci programu\n");
        exit(0);
    }
}

function replace($arg){
    $pattern = array();
    $pattern[0] = '&';
    $pattern[1] = '<';
    $pattern[2] = '>';
    $replacement[2] = '&amp;';
    $replacement[1] = '&lt;';
    $replacement[0] = '&gt;';
    echo(str_replace($pattern,$replacement,$arg));
}

function check_var($arg) {
    if(preg_match("/^(LF|GF|TF)@[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%!?0-9]*$/", $arg)){
        replace($arg);
    }
    else
        exit(23);
}

function check_symb($arg) {
    $splitted = explode('@',$arg);
    if(preg_match("/^(LF|GF|TF)@[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%!?0-9]*$/", $arg)){
            echo("\"var\">");
            replace($arg);
    }
    else if(preg_match("/^(int|label|type)@.+$/", $arg)){
        echo("\"$splitted[0]\">".$splitted[1]);
    }
    else if(preg_match("/^bool@(true|false)$/", $arg)){
        echo("\"$splitted[0]\">".$splitted[1]);
    }
    else if(preg_match("/^nil@nil$/", $arg)){
        echo("\"$splitted[0]\">".$splitted[1]);
    }
    else if(preg_match("/^string@([^\\\\#]|\\\\[0-9][0-9][0-9])*$/",$arg)){
            echo("\"$splitted[0]\">");
            replace($splitted[1]);
    }
    else
        exit(23);
}

function check_label($arg){
    if(preg_match("/^[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%!?0-9]*$/", $arg)){
        echo($arg);
    }
    else
        exit(23);
}

function check_type($arg){
    if(preg_match("/^(int|bool|nil|string)$/", $arg)){
        echo($arg);
    }
    else
        exit(23);
}
echo("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
while($line = fgets(STDIN)){
    $line = trim(preg_replace('/\s\s+/',' ',$line));
    $comment = explode('#', trim($line, "\n"));
    $splitted = explode(' ', $comment[0]);
    $splitted[0]=strtoupper($splitted[0]);
    if($line_number == 0 && $splitted[0] != ""){
        if(strcmp($splitted[0],$language) == 0){
            echo("<program language=\"IPPcode23\">\n");
        }
        else
            exit(21);
    }
        if($splitted[0] != ''){
            switch($splitted[0]){
                #TODO
                case 'MOVE':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'CREATEFRAME':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    if(array_key_exists(1,$splitted) && $splitted[1] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'PUSHFRAME':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    if(array_key_exists(1,$splitted) && $splitted[1] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'POPFRAME':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    if(array_key_exists(1,$splitted) && $splitted[1] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                case 'DEFVAR':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'CALL':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"label\">");
                    if(array_key_exists(1,$splitted)){
                        check_label($splitted[1]);
                    }
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'RETURN':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    if(array_key_exists(1,$splitted) && $splitted[1] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'PUSHS':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=");
                    if(array_key_exists(1,$splitted)){
                        check_symb($splitted[1]);
                    }
                    else
                        exit(23);
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("</arg1>\n");
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                case 'POPS':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'ADD':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'SUB':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'MUL':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'IDIV':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'LT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'GT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'EQ':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'AND':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'OR':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'NOT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'INT2CHAR':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'STRI2INT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'READ':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");                    
                    echo("\t\t<arg2 type=\"type\">");
                    if(array_key_exists(2,$splitted)){
                        check_type($splitted[2]);
                    }
                    else
                        exit(23);  
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'WRITE':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=");
                    if(array_key_exists(1,$splitted)){
                        check_symb($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'CONCAT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'STRLEN':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'GETCHAR':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'SETCHAR':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'TYPE':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"var\">");
                    if(array_key_exists(1,$splitted)){
                        check_var($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    if(array_key_exists(3,$splitted) && $splitted[3] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'LABEL':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"label\">");
                    if(array_key_exists(1,$splitted)){
                        check_label($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'JUMP':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"label\">");
                    if(array_key_exists(1,$splitted)){
                        check_label($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'JUMPIFEQ':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"label\">");
                    if(array_key_exists(1,$splitted)){
                        check_label($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'JUMPIFNEQ':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=\"label\">");
                    if(array_key_exists(1,$splitted)){
                        check_label($splitted[1]);
                    }
                    echo("</arg1>\n");
                    echo("\t\t<arg2 type=");
                    if(array_key_exists(2,$splitted)){
                        check_symb($splitted[2]);
                    }
                    else
                        exit(23);
                    echo("</arg2>\n");
                    echo("\t\t<arg3 type=");
                    if(array_key_exists(3,$splitted)){
                        check_symb($splitted[3]);
                    }
                    else
                        exit(23);
                    echo("</arg3>\n");
                    if(array_key_exists(4,$splitted) && $splitted[4] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'EXIT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=");
                    if(array_key_exists(1,$splitted)){
                        check_symb($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'DPRINT':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    echo("\t\t<arg1 type=");
                    if(array_key_exists(1,$splitted)){
                        check_symb($splitted[1]);
                    }
                    else
                        exit(23);
                    echo("</arg1>\n");
                    if(array_key_exists(2,$splitted) && $splitted[2] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                #TODO
                case 'BREAK':
                    echo("\t<instruction order=\"$order\" opcode=\"$splitted[0]\">\n");
                    if(array_key_exists(1,$splitted) && $splitted[1] != ''){
                        exit(23);
                    }
                    echo("\t</instruction>\n");
                    $order++;
                    break;
                default:
                    if($line_number>0 && $splitted[0] != "")
                        exit(22);
            }
        }
        if($splitted[0] != "")
            $line_number++;
    }
    
echo("</program>");
?>