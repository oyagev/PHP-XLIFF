<?php

class XliffNode{
	
	static protected $mapNameToClass = array(
		'xliff'		=> 'XliffDocument',
		'file'		=> 'XliffFile',
		'body'		=> 'XliffFileBody',
		'header'	=> 'XliffFileHeader',
		'group'		=> 'XliffUnitsGroup',
		'trans-unit'=> 'XliffUnit',
		'source'	=> 'XliffNode',
		'target'	=> 'XliffNode',
	);
	
	
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
	
	function setAttributes($attr_array){
		foreach($attr_array as $key=>$val){
			$this->setAttribute($key, $val);
		}
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
	
	public function appendNode(XliffNode $node){
		
		if (!empty($this->supportedContainers[$node->getName().'s'])){
			$this->containers[$node->getName().'s'][] = $node;
		}elseif(!empty($this->supportedNodes[$node->getName()])){
			$this->nodes[$node->getName()] = $node;
		}else{
			
		}
		return $this;
	}
	
	
	
	/**
	 * Support calling $obj->container($new=FALSE)
	 * or $obj->containers()
	 */
	function __call($name, $args){
		$append = (!empty($args) && $args[0]==TRUE);
		$mapNames = array(
			'/^unit/' => 'trans-unit'			
		);
		$name = preg_replace(array_keys($mapNames), array_values($mapNames), $name);
		//var_dump($name);
		//plural
		if (!empty($this->supportedContainers[$name]) ){
			return $this->containers[$name];
		}elseif(!empty($this->supportedContainers[$name.'s'])){
			$pluralName= $name.'s';
			
			//Create new instance if explicitly specified by argument
			if ( $append ){
				
				$cls = $this->supportedContainers[$pluralName];
				
				$this->containers[$pluralName][] = new $cls();
				
			}
			return end($this->containers[$pluralName]);
			
		}elseif(!empty($this->supportedNodes[$name])){
			
			//Create new node if explicitly required
			if ($append){
				$cls = $this->supportedNodes[$name];
				$this->nodes[$name] = new $cls();
				$this->nodes[$name]->setName($name);
			}
			
			return (!empty($this->nodes[$name])) ? $this->nodes[$name] : FALSE;
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
	
	public static function fromDOMElement(DOMNode $element){
		if ($element instanceOf DOMText){
			return $element->nodeValue;
		}else{
			$name = $element->tagName;
			if (empty(self::$mapNameToClass[$element->tagName]))
				throw new Exception(sprintf("Tag name '%s' is unsupported",$name));
	
			//Create the XliffNode
			$cls = self::$mapNameToClass[$element->tagName];
			
			$node = new $cls($element->tagName);
			/* @var $node XliffNode */
			
			foreach ($element->attributes as $attrNode){
				$node->setAttribute($attrNode->nodeName, $attrNode->nodeValue);
			}
			
			foreach($element->childNodes as $child){
				$res = self::fromDOMElement($child);
				if (is_string($res)){
					$node->setTextContent($res);
				}else{
					$node->appendNode($res);
				}
			}
		}
		return $node;
		
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
    
    
    function __construct(){
    	parent::__construct();
    	$this->version = '1.2';
        
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
    
    public static function fromDOM(DOMDocument $doc){
    	if (!($doc->firstChild &&  $doc->firstChild->tagName=='xliff'))
    		throw new Exception("Not an XLIFF document");
    		
    	
    	$xlfDoc = $doc->firstChild;
    	/* @var $xlfDoc DOMElement */
    	
    	$ver = $xlfDoc->getAttribute('version') ? $xlfDoc->getAttribute('version') : '1.2';
    	
    	$xliffNamespace = $xlfDoc->namespaceURI;
    	 
    	$xliff = new XliffDocument($ver);
    	
    	
    	$element = self::fromDOMElement($xlfDoc);
    	
    	return $element;
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
 * @method XliffUnitsGroup group()
 * @method XliffUnit unit()
 * @method array groups()
 * @method array units()
 */
class XliffFileBody extends XliffNode{
	protected $name = 'body';
	protected $supportedContainers = array(
    	'groups'	=> 'XliffUnitsGroup',
		'trans-units'		=> 'XliffUnit'
    );
}


/**
 * Enter description here ...
 * @method XliffUnit unit()
 * @method array units()
 */
class XliffUnitsGroup extends XliffNode{
	protected $name = 'group';
	protected $supportedContainers = array(
		'trans-units'		=> 'XliffUnit'
    );
}



/**
 * Enter description here ...
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



