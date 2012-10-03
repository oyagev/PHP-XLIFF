<?php

class XliffNode{
	protected $attributes = array();
	protected $containers = array();
	protected $supportedContainers = array();
	
	protected $nodes = array();
	protected $supportedNodes = array();
	
	protected $textContent=NULL;
	
	protected $name = '';
	
	function __construct($name=NULL){
		if($name) $this->setName($name);
		foreach($this->supportedContainers as $name=>$class){
			$this->containers[$name] = array();
		}
	}
	
	public function getName()
	{
	    return $this->name;
	}

	public function setName($name)
	{
	    $this->name = $name;
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
	
	public function getTextContent()
	{
	    return $this->textContent;
	}

	public function setTextContent($textContent)
	{
	    $this->textContent = $textContent;
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
			
		}elseif(!empty($this->supportedNodes[$name])){
			//Check if node already created
			if (empty($this->nodes[$name])){
				//create new node on-the-fly
				$cls = $this->supportedNodes[$name];
				
				$this->nodes[$name] = new $cls();
				$this->nodes[$name]->setName($name);
			}
			
			return $this->nodes[$name];
		}
		throw new Exception(sprintf("'%s' is not supported for '%s'",$name,get_class($this)));
	}
	
	function toDOMElement(DOMDocument $doc){
		$element = $doc->createElement($this->getName());
		foreach($this->attributes as $name=>$value){
			$element->setAttribute($name, $value);
		}
		foreach($this->containers as $container){
			foreach($container as $node){
				$element->appendChild($node->toDOMElement($doc));
			}
		}
		foreach($this->nodes as $node){
			$element->appendChild($node->toDOMElement($doc));
		}
		if ($text = $this->getTextContent()){
			$textNode = $doc->createTextNode($text);
			$element->appendChild($textNode);
		}
		return $element;
	}
	

	
} 

/**
 * Enter description here ...
 * @author oyagev
 * @method XliffFile file() file()
 */
class XliffDocument extends XliffNode{
	/**
     * uncomplete xliff Namespace
     */
    const NS = 'urn:oasis:names:tc:xliff:document:';
    
    protected $name = 'xliff';
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
    
   
    public function toDOM(){
    	// create the new document
    	$doc = new DOMDocument();

        // create the xliff root element
        $xliff = $this->toDOMElement($doc);
        // little hack to workaround the unusable php namespace handling
        $xliff->setAttribute('xmlns', self::NS . $this->version);
        // add the xliff version
        $xliff->setAttribute('version',$this->version);
        //$xliff->appendChild($this->toDOMElement($doc));
        $doc->appendChild($xliff);
        return $doc;
        
    }
    
   
    
}


/**
 * Enter description here ...
 * @author oyagev
 * @method XliffFileBody body()
 * @method XliffFileHeader header()
 */
class XliffFile extends XliffNode{
	protected $name = 'file';
	protected $supportedNodes = array(
		'header' 	=> 'XliffFileHeader',
		'body' 		=> 'XliffFileBody',
	);
	
}

class XliffFileHeader extends XliffNode{
	protected $name = 'header';
}
/**
 * Enter description here ...
 * @author oyagev
 * @method XliffUnitsGroup group()
 * @method XliffUnit unit()
 * @method array groups()
 * @method array units()
 */
class XliffFileBody extends XliffNode{
	protected $name = 'body';
	protected $supportedContainers = array(
    	'groups'	=> 'XliffUnitsGroup',
		'units'		=> 'XliffUnit'
    );
}


class XliffUnitsGroup extends XliffNode{
	protected $name = 'group';
	protected $supportedContainers = array(
		'units'		=> 'XliffUnit'
    );
}



/**
 * Enter description here ...
 * @author oyagev
 * @method XliffNode source()
 * @method XliffNode target()
 */
class XliffUnit extends XliffNode{
	protected $name = 'trans-unit';
	protected $supportedNodes = array(
		'source' => 'XliffNode',
		'target' => 'XliffNode',
	);
}



