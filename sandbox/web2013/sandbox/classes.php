
<?php 
class multipleInheritance 
{ 
    function callClass($class_to_call) 
    { 
        return new $class_to_call(); 
    } 
} 

class A 
{ 
    function insideA() 
    { 
        echo "I'm inside A!<br />"; 
    } 
} 

class B 
{ 

    function insideB() 
    { 
        echo "I'm inside B!<br />"; 
    } 
} 

class C extends multipleInheritance 
{ 
    function insideC() 
    { 
        $a = parent::callClass('A'); 
        $a->insideA(); 
        $b = parent::callClass('B'); 
        $b->insideB(); 
    } 
} 

$c = new C(); 
$c->insideC(); 
?> 