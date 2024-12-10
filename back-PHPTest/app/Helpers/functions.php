<?php

function view($view, $data=[]){
    include(str_replace("Helpers",'',__DIR__).'View/'.$view.'.php');
}