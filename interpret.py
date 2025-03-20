import re
import argparse
import xml.etree.ElementTree as ET
import sys

def_glob_var=[]
glob_var={}
glob_var_type={}
labels={}
i = 0
instr_numbr = 0
call_stack = []

class argument:
    def __init__(self, type, value):
        self.type = type
        self.value = value

class instruction:
    # list se vsemi instrukcemi
    instruction_list = []
    # hodnoty promennych TF
    temporary_var = {}
    # typy promennych TF
    temporary_var_type = {}
    # definice promennych TF
    def_tmp_var=[]
    # definice promennych LF
    def_lf_var=[]
    # hodnoty promennych LF
    local_var=[]
    # typy promennych LF
    local_var_type=[]
    # zasobnik pro stack instrukce
    data_stack = []
    # argument --input
    inputFile = None
    # zda je zalozen docasny ramec
    tmp = 0

    def __init__(self,opcode,order):
       self.opcode = opcode
       self.order = order
       # vsechny argumenty instrukce
       self.args = []
       self.instruction_list.append(self)
              
    def get_order(self):
        return self.order
    
    def get_opcode(self):
        return self.opcode

    def get_arguments(self):
        return self.args
    # pridani argumentu instrukce
    def add_argument(self, type, value):
        self.args.append(argument(type,value))

    def exact_value_from_symb(self, position):
        # pokud to neni promenna, nebo int
        if(re.search(r'^(bool|string|nil|type)$',self.get_arguments()[position].type)):
            return self.get_arguments()[position].value
        # kontrola zda je int opravdu cislo
        if(self.get_arguments()[position].type == "int"):
            self.check_arg(self.get_arguments()[position].value,"int")
            return int(self.get_arguments()[position].value)
        # promenna (var)
        if(self.get_arguments()[position].type == "var"):
            value = self.get_arguments()[position].value.split("@")
            if value[0] == "TF":
                if instruction.tmp == 0:
                    sys.exit(55)
                return instruction.temporary_var[value[1]]
            if value[0] == "LF":
                # vrchol zasobniku LF
                return instruction.local_var[len(instruction.local_var)-1][value[1]]
            return glob_var[value[1]]
    # jmeno promenne (var)
    def get_name_of_symb(self, position):
        value = self.get_arguments()[position].value.split("@")
        return value[1]
    # kontrola zda je int cislo a bool true/false
    def check_arg(self,value,type):
        if type == "int":
            num = 0
            for i in value:
                if i < '0' or i >'9':
                    if num != 0:
                        sys.exit(32)
                    if i != "-":
                        sys.exit(32)
                num+=1
        if type == "bool":
            if value.upper() != "TRUE" and value.upper() != "FALSE":
                sys.exit(32)
    # vrati typ argumentu
    def argument_type(self, position):
        if self.get_arguments()[position].type != "var":
            self.check_arg(self.get_arguments()[position].value,self.get_arguments()[position].type )
            return self.get_arguments()[position].type
        # ziskani hodnoty z promenne
        value = self.get_arguments()[position].value.split("@")
        if value[0] == "TF":
            if instruction.tmp == 0:
                sys.exit(55)
            if value[1] in instruction.temporary_var_type:
                return instruction.temporary_var_type[value[1]]
            check = instruction.temporary_var[value[1]]
        if value[0] == "GF":
            if value[1] in glob_var_type:
                return glob_var_type[value[1]]
            if value[1] not in glob_var:
                return ""
            check = glob_var[value[1]]
        if value[0] == "LF":
            if value[1] in instruction.local_var_type[len(instruction.local_var_type)-1]:
                return instruction.local_var_type[len(instruction.local_var_type)-1][value[1]]
            check = instruction.local_var[len(instruction.local_var)-1][value[1]]
        if check == "nil":
            return "nil"
        if isinstance(check,bool):
            return "bool"
        elif isinstance(check,int):
            return "int"
        elif isinstance(check,str):
            return "string"
        else:
            return "None"
    # vrati true/false v textove podobe
    def bool_string(self, position):
        if self.get_arguments()[position].type != "var":
            return self.get_arguments()[position].value
        if self.exact_value_from_symb(position) == True or self.exact_value_from_symb(position) == "true":
            return "true"
        return "false"
    # vraci true/false jako hodnoty True/False
    def bool_value(self, position):
        if self.get_arguments()[position].type != "var":
            if self.get_arguments()[position].value == "true":
                return True
            return False
        return self.exact_value_from_symb(position)
    # urci ve kterem ramci se nachazi hledana promenna
    def destination_var(self, position):
        if self.get_arguments()[position].type == "var":
            value = self.get_arguments()[position].value.split("@")
            if value[0] == "TF":
                if instruction.tmp == 0:
                    sys.exit(55)
                if self.get_name_of_symb(0) not in instruction.def_tmp_var:
                    sys.exit(54)
            if value[0] == "GF":
                if self.get_name_of_symb(0) not in def_glob_var:
                    sys.exit(54)
            return value[0]
    # ulozeni hodnoty do promenne v urcenem ramci
    def asign_value_to_var(self, position, value):
        if self.destination_var(position) == "GF":
            glob_var[self.get_name_of_symb(position)] = value
        if self.destination_var(position) == "TF":
            if instruction.tmp == 0:
                    sys.exit(55)
            instruction.temporary_var[self.get_name_of_symb(position)] = value
        if self.destination_var(position) == "LF":
            instruction.local_var[len(instruction.local_var)-1][self.get_name_of_symb(position)] = value
    # ulozeni typu promenne
    def asign_type_of_var(self, position, value):
        if self.destination_var(position) == "GF":
            glob_var_type[self.get_name_of_symb(position)] = value
        if self.destination_var(position) == "TF":
            if instruction.tmp == 0:
                    sys.exit(55)
            instruction.temporary_var_type[self.get_name_of_symb(position)] = value
        if self.destination_var(position) == "LF":
            instruction.local_var_type[len(instruction.local_var)-1][self.get_name_of_symb(position)] = value
    # nahrazeni \000 - \999 ascii hodnot
    def ascii_replace(self, position):
        string = ""
        helps = ""
        i = 0
        if self.exact_value_from_symb(position) == None:
            return
        while(i < len(self.exact_value_from_symb(position))):
            helps = ""
            no_number = True
            if self.exact_value_from_symb(position)[i] == "\\":
                if i+3 < len(self.exact_value_from_symb(position)): 
                    for j in range(3):
                        if self.exact_value_from_symb(position)[i + j + 1] >= '0' and self.exact_value_from_symb(position)[i + j + 1] <= '9':
                            helps+=self.exact_value_from_symb(position)[i + j + 1]
                            no_number = False
                        else:
                            no_number = True
                else:
                    no_number = True
                if not no_number:
                    i+=3
                    string+=chr(int(helps))
            if no_number:
                string+=self.exact_value_from_symb(position)[i]
            i+=1
        return string
    # pomocna funkce pro setchar
    def change_char_in_setchar(self, position):
        ind = 0
        txt =""
        if self.destination_var(position) == "GF":
            dest = glob_var[self.get_name_of_symb(position)]
        if self.destination_var(position) == "TF":
            dest = instruction.temporary_var[self.get_name_of_symb(position)]
        if self.destination_var(position) == "LF":
            dest =  instruction.local_var[len(instruction.local_var)-1][self.get_name_of_symb(position)]
        for i in dest:
            if ind == self.exact_value_from_symb(1):
                txt += self.exact_value_from_symb(2)[0]
            else:
                txt += i
            ind+=1
        self.asign_value_to_var(0,txt)

class move(instruction):
    def execute(self):
        # bude potreba vratit hodnotu jako True/False
        if self.argument_type(1) == "bool":
            self.asign_value_to_var(0,self.bool_value(1))
            self.asign_type_of_var(0,"bool")
        else:
            # ulozeni hodnoty a typu promenne
            self.asign_value_to_var(0,self.exact_value_from_symb(1))
            self.asign_type_of_var(0,self.argument_type(1))

class createframe(instruction):
    def execute(self):
        instruction.tmp = 1
        instruction.temporary_var = {}
        instruction.def_tmp_var = []

class pushframe(instruction):
    def execute(self):
        if instruction.tmp == 0:
            sys.exit(55)
        instruction.tmp = 0
        # presunuti TF do LF
        instruction.local_var.append(instruction.temporary_var)
        instruction.local_var_type.append(instruction.temporary_var_type)
        instruction.def_lf_var.append(instruction.def_tmp_var)
        # vycisteni TF
        instruction.temporary_var = {}
        instruction.temporary_var_type = {}
        instruction.def_tmp_var = []

class popframe(instruction):
    def execute(self):
        if len(instruction.def_lf_var) == 0:
            sys.exit(55)
        # presunuti LF do TF
        instruction.tmp = 1
        instruction.temporary_var = instruction.local_var.pop()
        instruction.temporary_var_type = instruction.local_var_type.pop()
        instruction.def_tmp_var = instruction.def_lf_var.pop()

class defvar(instruction):
    def execute(self):
        # definice promenne - kontrola ve kterem ramci bude ulozena
        if(re.search(r'\bGF@.*\b',self.get_arguments()[0].value)):
            if self.get_name_of_symb(0) in def_glob_var:
                sys.exit(52)
            def_glob_var.append(self.get_name_of_symb(0))
        if(re.search(r'\bTF@.*\b',self.get_arguments()[0].value)):
            if instruction.tmp == 0:
                sys.exit(55)
            if self.get_name_of_symb(0) in instruction.def_tmp_var:
                sys.exit(52)
            instruction.def_tmp_var.append(self.get_name_of_symb(0))
        if(re.search(r'\bLF@.*\b',self.get_arguments()[0].value)):
            if self.get_name_of_symb(0) in instruction.def_lf_var:
                sys.exit(52)
            instruction.def_lf_var.append(self.get_name_of_symb(0))

class call(instruction):
    def execute(self):
        value = i + 1
        call_stack.append(value)
        # kontrola zda label existuje
        if self.args[0].value not in labels:
            sys.exit(52)
        # vrati hodnotu na kterou se skoci
        return labels[self.args[0].value]

class return_i(instruction):
    def execute(self):
        if len(call_stack) == 0:
            sys.exit(56)
        return call_stack.pop() - 1

class pushs(instruction):
    def execute(self):
        instruction.data_stack.append(self.exact_value_from_symb(0))

class pops(instruction):
    def execute(self):
        if(len(instruction.data_stack) == 0):
            sys.exit(56)
        self.asign_value_to_var(0,instruction.data_stack.pop())

class add(instruction):
    def execute(self):
        if self.argument_type(1) != "int" or self.argument_type(2) != "int":
            sys.exit(53)
        self.asign_value_to_var(0,self.exact_value_from_symb(1) + self.exact_value_from_symb(2))
        self.asign_type_of_var(0,"int")

class sub(instruction):
    def execute(self):
        if self.argument_type(1) != "int" or self.argument_type(2) != "int":
            sys.exit(53)
        self.asign_value_to_var(0,self.exact_value_from_symb(1) - self.exact_value_from_symb(2))
        self.asign_type_of_var(0,"int")

class mul(instruction):
    def execute(self):
        if self.argument_type(1) != "int" or self.argument_type(2) != "int":
            sys.exit(53)
        self.asign_value_to_var(0,self.exact_value_from_symb(1) * self.exact_value_from_symb(2))
        self.asign_type_of_var(0,"int")

class idiv(instruction):
    def execute(self):
        if self.argument_type(1) != "int" or self.argument_type(2) != "int":
            sys.exit(53)
        if self.exact_value_from_symb(2) == 0:
            sys.exit(57)
        self.asign_value_to_var(0,int(self.exact_value_from_symb(1) / self.exact_value_from_symb(2)))
        self.asign_type_of_var(0,"int")
    
class lt(instruction):
    def execute(self):
        if self.argument_type(1) == "nil" or self.argument_type(2) == "nil":
            sys.exit(53)
        # pro jine typy bude potreba jinak vyhodnocovat
        if(self.argument_type(1) == self.argument_type(2)):
            self.asign_type_of_var(0,"bool")
            if self.argument_type(1) == "int":
                if self.exact_value_from_symb(1) < self.exact_value_from_symb(2):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
            if self.argument_type(1) == "string":
                if len(self.exact_value_from_symb(1)) < len(self.exact_value_from_symb(2)):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
            if self.argument_type(1) == "bool":
                if int(self.bool_value(1)) < int(self.bool_value(2)):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
        else:
            sys.exit(53)

class gt(instruction):
    def execute(self):
        if self.argument_type(1) == "nil" or self.argument_type(2) == "nil":
            sys.exit(53)
        # pro jine typy bude potreba jinak vyhodnocovat
        if(self.argument_type(1) == self.argument_type(2)):
            self.asign_type_of_var(0,"bool")
            if self.argument_type(1) == "int":
                if int(self.exact_value_from_symb(1)) > int(self.exact_value_from_symb(2)):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
            if self.argument_type(1) == "string":
                if len(self.exact_value_from_symb(1)) > len(self.exact_value_from_symb(2)):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
            if self.argument_type(1) == "bool":
                if int(self.bool_value(1)) > int(self.bool_value(2)):
                    self.asign_value_to_var(0,True)
                else:
                    self.asign_value_to_var(0,False)
        else:
            sys.exit(53)

class eq(instruction):
    def execute(self):
        if(self.argument_type(1) == self.argument_type(2) or self.argument_type(1) == "nil" or self.argument_type(2) == "nil"):
            self.asign_type_of_var(0,"bool")
            if self.exact_value_from_symb(1) == self.exact_value_from_symb(2):
                self.asign_value_to_var(0,True)
            else:
                self.asign_value_to_var(0,False)
        else:
            sys.exit(53)

class and_i(instruction):
    def execute(self):
        if self.argument_type(1) != "bool" or self.argument_type(2) != "bool":
            sys.exit(53)
        self.asign_type_of_var(0,"bool")
        if self.bool_string(1) == "true" and self.bool_string(2) == "true":
            self.asign_value_to_var(0,True)
        else:
            self.asign_value_to_var(0,False)

class or_i(instruction):
    def execute(self):
        if self.argument_type(1) != "bool" or self.argument_type(2) != "bool":
            sys.exit(53)
        self.asign_type_of_var(0,"bool")
        if self.bool_string(1) == "true" or self.bool_string(2) == "true":
            self.asign_value_to_var(0,True)
        else:
            self.asign_value_to_var(0,False)
        

class not_i(instruction):
    def execute(self):
        if self.argument_type(1) != "bool":
            sys.exit(53)
        self.asign_type_of_var(0,"bool")
        if self.bool_string(1) == "true":
            self.asign_value_to_var(0,False)
        else:
            self.asign_value_to_var(0,True)

class int2char(instruction):
    def execute(self):
        if self.argument_type(1) != "int":
            sys.exit(53)
        if 0 < self.exact_value_from_symb(1) < 1114111:
            if 55296 < self.exact_value_from_symb(1) < 57323:
                sys.exit(58)
            self.asign_value_to_var(0,chr(self.exact_value_from_symb(1)))
        else:
            sys.exit(58)

class stri2int(instruction):
    def execute(self):
        if self.argument_type(2) != "int":
            sys.exit(53)
        if self.argument_type(1) != "string":
            sys.exit(53)
        if self.exact_value_from_symb(2) >= len(self.exact_value_from_symb(1)):
            sys.exit(58)
        self.asign_value_to_var(0,ord(self.exact_value_from_symb(1)[self.exact_value_from_symb(2)]))

class read(instruction):
    def execute(self):
        if self.argument_type(1) != "type":
            sys.exit(32)
        if self.exact_value_from_symb(1) != "int" and self.exact_value_from_symb(1) != "string" and self.exact_value_from_symb(1) != "bool":
            sys.exit(53)
        # zda nebyl zadan argument --input, bude se cist ze vstupu
        if not instruction.inputFile:
           inputs = input()
        else:
            inputFile = open(instruction.inputFile,"r")
            inputs = inputFile.readline().rstrip()
        if inputs == "":
            self.asign_value_to_var(0,"nil")
            self.asign_type_of_var(0,"nil")
        else:
            self.asign_value_to_var(0,inputs)
            self.asign_type_of_var(0,self.exact_value_from_symb(1))

class write(instruction):
    def execute(self):
        if self.argument_type(0) == "string":
            print(self.ascii_replace(0),end='')
        elif self.argument_type(0) == "nil" or self.argument_type(0) == "None":
            print("",end='')
        elif self.argument_type(0) == "bool":
            print(self.bool_string(0),end='')
        else:
            # kontrola z ktereho ramce se bude vypisovat
            if self.get_arguments()[0].type == "var":
                if self.destination_var(0) == "GF":
                    print(glob_var[self.get_name_of_symb(0)],end='')
                if self.destination_var(0) == "TF":
                    if instruction.tmp == 0:
                        sys.exit(55)
                    print(instruction.temporary_var[self.get_name_of_symb(0)],end='')
                if self.destination_var(0) == "LF":
                    print(instruction.local_var[len(instruction.local_var)-1][self.get_name_of_symb(0)],end='')
            else:
                print(self.get_arguments()[0].value,end='')
        
class concat(instruction):
    def execute(self):
        if self.argument_type(1) != "string" or self.argument_type(2) != "string":
            sys.exit(53)
        self.asign_type_of_var(0,"string")
        self.asign_value_to_var(0,self.exact_value_from_symb(1) + self.exact_value_from_symb(2)) 

class strlen(instruction):
    def execute(self):
        if self.argument_type(1) != "string":
            sys.exit(53)
        self.asign_value_to_var(0,len(self.exact_value_from_symb(1)))

class getchar(instruction):
    def execute(self):
        if self.argument_type(1) != "string" or self.argument_type(2) != "int":
            sys.exit(53)
        if self.exact_value_from_symb(2) >= len(self.exact_value_from_symb(1)):
            sys.exit(58)
        self.asign_value_to_var(0,self.exact_value_from_symb(1)[self.exact_value_from_symb(2)])

class setchar(instruction):
    def execute(self):
        if self.argument_type(1) != "int" or self.argument_type(0) != "string" or self.argument_type(2) != "string":
            sys.exit(53)
        if self.exact_value_from_symb(1) >= len(self.exact_value_from_symb(0)):
            sys.exit(58)
        if len(self.exact_value_from_symb(2)) == 0:
            sys.exit(58)
        self.change_char_in_setchar(0)

class type(instruction):
    def execute(self):
        if self.argument_type(1) == "None":
            return "None"
        self.asign_type_of_var(0,"string")
        self.asign_value_to_var(0,self.argument_type(1))

class label(instruction):
    def execute(self):
        if self.args[0].value in labels.keys():
            sys.exit(52)
        labels[self.args[0].value] = i

class jump(instruction):
    def execute(self):
        if self.args[0].value not in labels:
            sys.exit(52) 
        return labels[self.args[0].value]

class jumpifeq(instruction):
    def execute(self):
        if self.args[0].value not in labels:
            sys.exit(52)
        if(self.argument_type(1) == self.argument_type(2) or self.argument_type(1) == "nil" or self.argument_type(2) == "nil"):
            if self.argument_type(1) == "int":
                if int(self.exact_value_from_symb(1)) == int(self.exact_value_from_symb(2)):
                    return labels[self.args[0].value]
                return i
            if self.exact_value_from_symb(1) == self.exact_value_from_symb(2):
                return labels[self.args[0].value]
            return i
        sys.exit(53)
        

class jumpifneq(instruction):
    def execute(self):
        if self.args[0].value not in labels:
            sys.exit(52)
        if(self.argument_type(1) != self.argument_type(2) or self.argument_type(1) == "nil" or self.argument_type(2) == "nil"):
            sys.exit(53)
        if self.argument_type(1) == "int":
                if int(self.exact_value_from_symb(1)) != int(self.exact_value_from_symb(2)):
                    return labels[self.args[0].value]
                return i
        if self.exact_value_from_symb(1) != self.exact_value_from_symb(2):
            return labels[self.args[0].value]
        return i

class exit(instruction):
    def execute(self):
        if self.argument_type(0) != "int":
            sys.exit(53)
        if 0 <= self.exact_value_from_symb(0) <= 49:
            sys.exit(self.exact_value_from_symb(0))
        sys.exit(57)

class dprint(instruction):
    def execute(self):
        sys.stderr.write(self.exact_value_from_symb(1))

class break_i(instruction):
    def execute(self):
        print("Pozice:",i,"Pocet vykonanych instrukci:",instr_numbr,file = sys.stderr)
# vyhodnocuje instrukci
class Factory:
    @classmethod
    def resolve(cls,opcode, order):
        match opcode:
            case "MOVE":
                return move(opcode,order)
            case "CREATEFRAME":
                return createframe(opcode,order)
            case "PUSHFRAME":
                return pushframe(opcode,order)
            case "POPFRAME":
                return popframe(opcode,order)
            case "DEFVAR":
                return defvar(opcode,order)
            case "CALL":
                return call(opcode,order)
            case "RETURN":
                return return_i(opcode,order)
            case "PUSHS":
                return pushs(opcode,order)
            case "POPS":
                return pops(opcode,order)
            case "ADD":
                return add(opcode,order)
            case "SUB":
                return sub(opcode,order)
            case "MUL":
                return mul(opcode,order)
            case "IDIV":
                return idiv(opcode,order)
            case "LT":
                return lt(opcode,order)
            case "GT":
                return gt(opcode,order)
            case "EQ":
                return eq(opcode,order)
            case "AND":
                return and_i(opcode,order)
            case "OR":
                return or_i(opcode,order)
            case "NOT":
                return not_i(opcode,order)
            case "INT2CHAR":
                return int2char(opcode,order)
            case "STRI2INT":
                return stri2int(opcode,order)
            case "READ":
                return read(opcode,order)
            case "WRITE":
                return write(opcode,order)
            case "CONCAT":
                return concat(opcode,order)
            case "STRLEN":
                return strlen(opcode,order)
            case "GETCHAR":
                return getchar(opcode,order)
            case "SETCHAR":
                return setchar(opcode,order)
            case "TYPE":
                return type(opcode,order)
            case "LABEL":
                return label(opcode,order)
            case "JUMP":
                return jump(opcode,order)
            case "JUMPIFEQ":
                return jumpifeq(opcode,order)
            case "JUMPIFNEQ":
                return jumpifneq(opcode,order)
            case "EXIT":
                return exit(opcode,order)
            case "DPRINT":
                return dprint(opcode,order)
            case "BREAK":
                return break_i(opcode,order)
            case _:
                exit()

# parsovani vstupu
ap = argparse.ArgumentParser()
ap.add_argument("--source",type=str)
ap.add_argument("--input",type=str)

args = ap.parse_args()


sourceFile = args.source
instruction.inputFile = args.input
# pokud nebyl zadan --input ani --source bude program ukoncen
if sourceFile == None and instruction.inputFile == None:
    sys.exit(10)
if sourceFile == None:
    sourceFile = input()
instruction_number = 0

tree = ET.parse(sourceFile)
root = tree.getroot()
if root.tag != "program":
    sys.exit(32)
# kontrola XML
for child in root.iter():
    if not re.search(r'\barg+[0-9]\b',child.tag) and child.tag != "instruction" and child.tag != "program":
        sys.exit(32)
    if child.tag == "instruction":
        if 'order' not in child.attrib or 'opcode' not in child.attrib:
            sys.exit(32)
        instruct = Factory.resolve(child.attrib['opcode'].upper(), child.attrib['order'])
        instruction_number += 1
    if re.search(r'\barg+[0-9]\b',child.tag):
        instruct.instruction_list[instruction_number-1].add_argument(child.attrib["type"], child.text)
    if "order" in child.attrib:
        # stejny order
        for instr in instruction.instruction_list:
            # nekontrolovat stejnou instrukci
            if instr != instruction.instruction_list[instruction_number-1]:
                if instr.order == child.attrib["order"]:
                    sys.exit(32)
            # zda neni order zaporne cislo
            if int(child.attrib["order"]) <= 0:
                sys.exit(32)
# poradi podle order
instruction.instruction_list.sort(key=lambda x: int(x.get_order()))
# prvni pruchod nastaveni vsechn navesti
while i < len(instruction.instruction_list):
    if(instruction.instruction_list[i].get_opcode() == "LABEL"):
        instruction.instruction_list[i].execute()
    i+=1
i = 0
# druhy pruchod vykonavani instrukci
while i < len(instruction.instruction_list):
    if(re.search(r'^(JUMP|JUMPIFEQ|JUMPIFNEQ|CALL|RETURN)$',instruction.instruction_list[i].get_opcode())):
        i = instruction.instruction_list[i].execute()
    elif(instruction.instruction_list[i].get_opcode() != "LABEL"):
        instruction.instruction_list[i].execute()
    i+=1
    instr_numbr+=1
