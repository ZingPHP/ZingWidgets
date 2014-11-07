<?php

namespace Widgets\Form;

use Widgets\Widget;

/**
 *
 * @author Ryan Naddy <rnaddy@corp.acesse.com>
 * @name Register.php
 * @version 1.0.0 Nov 5, 2014
 */
class Form extends Widget{

    public function setDefaultSettings(){
        return array(
            "validate"    => false,
            "action"      => "",
            "wrapper"     => array(
                "tag"   => "div",
                "class" => "register-box"
            ),
            "submit"      => array(
                "visible" => true,
                "text"    => "Submit",
            ),
            "itemWrapper" => "div",
            "fields"      => array(
                "first"      => array(
                    "text"     => "First Name",
                    "required" => true,
                ),
                "last"       => array(
                    "text"     => "Last Name",
                    "required" => true,
                ),
                "email"      => array(
                    "text"     => "Email Address",
                    "required" => true,
                    "filter"   => "email"
                ),
                "password"   => array(
                    "type"     => "password",
                    "text"     => "Password",
                    "required" => true,
                    "minlen"   => 6
                ),
                "repassword" => array(
                    "type"     => "password",
                    "text"     => "Re-Type Password",
                    "required" => true,
                    "equals"   => "password"
                )
            )
        );
    }

    public function runWidget(){
        if(!$this->settings["validate"]){
            $this->saveSettings();
            $this->makeForm();
        }else{
            $this->loadSavedSettings();
            $this->validateForm();
        }
    }

    protected function makeForm(){
        $wrapper       = $this->settings["wrapper"]["tag"];
        $wrapperClass  = $this->settings["wrapper"]["class"];
        $submitVisible = $this->settings["submit"]["visible"];
        $submitText    = $this->settings["submit"]["text"];

        $str = '<' . $wrapper . ' class="' . $wrapperClass . '">';
        $str .= '<form method="post" action="' . $this->settings["action"] . '">';
        foreach($this->settings["fields"] as $name => $value){
            $type            = isset($value["type"]) ? $value["type"] : "input";
            $text            = isset($value["text"]) ? $value["text"] : "";
            $itmWrapper      = isset($value["wrapper"]) ? $value["wrapper"] : $wrapper;
            $itmWrapperClass = isset($value["wrapperClass"]) ? $value["wrapperClass"] : "";
            $class           = isset($value["class"]) ? $value["class"] : "";
            if($type != "textarea"){
                $str .= '<' . $itmWrapper . ' class="' . $itmWrapperClass . '"><input type="' . $type . '" name="' . $name . '" placeholder="' . $text . '" class="register-input register-' . $type . ' ' . $class . '" /></' . $itmWrapper . '>';
            }else{
                $str .= '<' . $itmWrapper . ' class="' . $itmWrapperClass . '"><textarea name="' . $name . '" placeholder="' . $text . '" class="register-input register-textarea ' . $class . '" /></' . $itmWrapper . '>';
            }
        }
        if($submitVisible){
            $str .= '<' . $itmWrapper . '><input type="submit" class="register-submit" name="submit" value="' . $submitText . '"></' . $itmWrapper . '>';
        }
        $str .= '</form>';
        $str .= '</' . $wrapper . '>';
        $this->html = $str;
    }

    protected function validateForm(){
        $this->html = array();
        foreach($_POST as $name => $value){
            if(!isset($this->settings["fields"][$name])){
                continue;
            }
            $setting  = $this->settings["fields"][$name];
            $required = isset($setting["required"]) ? (bool)$setting["required"] : false;
            $filter   = isset($setting["filter"]) ? $setting["filter"] : "";
            $minlen   = isset($setting["minlen"]) ? (int)$setting["minlen"] : null;
            $maxlen   = isset($setting["maxlen"]) ? (int)$setting["maxlen"] : null;
            $equals   = isset($setting["equals"]) ? $setting["equals"] : null;

            $this->html["values"][$name] = $value;

            // Empty field
            if($required && $this->util->blank($value)){
                $this->html["errors"][$name] = "blank";
            }
            // Invalid email
            if($filter === "email" && !$this->_validate($value, "email")){
                $this->html["errors"][$name] = "email";
            }
            // Invalid url
            if($filter === "url" && !$this->_validate($value, "url")){
                $this->html["errors"][$name] = "email";
            }
            // Invalid Number String
            if($filter === "number" && !$this->_validate($value, "number")){
                $this->html["errors"][$name] = "email";
            }
            // Invalid Alpaha String
            if($filter === "alpha" && !$this->_validate($value, "alpha")){
                $this->html["errors"][$name] = "email";
            }
            // Invalid Alpaha Numeric String
            if($filter === "alnum" && !$this->_validate($value, "alnum")){
                $this->html["errors"][$name] = "email";
            }
            // Too Short
            if($minlen !== null && strlen($value) < $minlen){
                $this->html["errors"][$name] = "short";
            }
            // Too Long
            if($maxlen !== null && strlen($value) > $maxlen){
                $this->html["errors"][$name] = "long";
            }
            // Fields don't match
            if($equals !== null && $value !== $this->input->post($equals)){
                $this->html["errors"][$name] = "not_equal";
            }
        }
    }

    protected function _validate($value, $type){
        switch(strtolower($type)){
            case "email":
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            case "url":
                return filter_var($value, FILTER_VALIDATE_URL);
            case "number":
                return ctype_digit($value);
            case "alpha":
                return ctype_alpha($value);
            case "alnum":
                return ctype_alnum($value);
        }
    }

}
