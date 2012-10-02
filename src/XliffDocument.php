<?php

abstract class XliffElement{
	protected $attributes = array();
	protected $containers = array();
	protected $supportedContainers = array();
	
	function __construct(){
		foreach($this->supportedContainers as $name=>$class){
			$this->containers[$name] = array();
		}
	}
	
	function getAttribute($name){
		return (isset($this->attributes[$name])) ? $this->attributes[$name] : FALSE;
	}
	function setAttribute($name, $value){
		if (!(string)$value){
			throw new Exception("Attribute must be a string");
		}
		$this->attributes[$name] = trim((string)$value);
		return $this;
	}
	
	/**
	 * Support calling $obj->container($new=FALSE)
	 * or $obj->containers()
	 */
	function __call($name, $args){
		//plural
		if (!empty($this->supportedContainers[$name]) ){
			return $this->containers[$name];
		}elseif(!empty($this->supportedContainers[$name.'s'])){
			$pluralName= $name.'s';
			
			//Create new instance if explicitly by argument
			//or implicitly when no instances exist
			if ( (!empty($args) && $args[0]==TRUE) || empty($this->containers[$pluralName])){
				
				$cls = $this->supportedContainers[$pluralName];
				
				$this->containers[$pluralName][] = new $cls();
				
			}
			return end($this->containers[$pluralName]);
		}
		throw new Exception("'$name' is unknown");
	}
	
} 

class XliffDocument extends XliffElement{
	/**
     * uncomplete xliff Namespace
     */
    const NS = 'urn:oasis:names:tc:xliff:document:';
    
    protected $supportedContainers = array(
    	'files' => 'XliffFile',
    );
    /**
     * Enter description here ...
     * @var DOMDocument
     */
    protected $doc;
    /**
     * Enter description here ...
     * @var DOMElement
     */
    protected $xliff;
    
    protected $version;
    
    
    function __construct($version='1.2'){
    	parent::__construct();
    	$this->version = $version;
        
    }
    
   
    protected function createDomTree(){
    	// create the new document
    	$doc = new DOMDocument();

        // create the xliff root element
        $xliff = $doc->createElement('xliff');
        // little hack to workaround the unusable php namespace handling
        $xliff->setAttribute('xmlns', self::NS . $this->version);
        // add the xliff version
        $xliff->setAttribute('version',$this->version);
        $doc->appendChild($xliff);
        
    }
    
   
    
}


class XliffFile extends XliffElement{
	protected $supportedContainers = array(
    	'groups'	=> 'XliffUnitsGroup',
		'units'		=> 'XliffUnit'
    );
}

class XliffFileHeader extends XliffElement{
	
}

class XliffUnitsGroup extends XliffElement{
	protected $supportedContainers = array(
		'units'		=> 'XliffUnit'
    );
}

class XliffUnit extends XliffElement{
	protected $source , $target;
}



