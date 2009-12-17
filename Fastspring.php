<?php
/**
 * @package  Fastspring
 * @author   Michael Botsko, Trellis Development, LLC
 * @license  Mozilla Public License, 1.1
 *
 * This library assists with building URLs and forms for integration
 * with the payment services website, Fastspring.com.
 */

/**
 * @package Fastspring
 */
class Fastspring {

	/***********
	 * PUBLIC MEMBER VARIABLES
	 */

	/**
	 * @var string Sets the url slug for the company identifier
	 * @access public
	 */
	public $company;

	/**
	 * @var string Sets the html spit out by the link method
	 * @access public
	 */
	public $link_html = '<a href="%s">%s</a>';

	/**
	 * @var string Sets the url/form mode to either test or live.
	 * @access public
	 */
	public $mode = 'test';

	/**
	 * @var string Sets the product. May also be set through the url() at call time.
	 * @access public
	 */
	public $product;

	/**
	 * @var string Sets the base URL for all fastspring actions
	 * @access public
	 */
	public $url_base = 'sites.fastspring.com';


	/***********
	 * PROTECTED MEMBER VARIABLES
	 */

	/**
	 * @var string Contains the link type
	 * @access private
	 */
	protected $link_type = 'instant';

	/**
	 * @var array Contains any additional url/form parameters
	 * @access private
	 */
	protected $params = array();

	/**
	 * @var string Contains the protocol to use, depending on the link type
	 * @access private
	 */
	protected $protocol = 'https://';


	/**
	 * Push a new parameter to the query string of the output url or to
	 * the hidden fields of the form.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 * @access public
	 */
	public function addParameter($key, $value){
		$accepted = array(
						'mode',
						'referrer',
						'action',
						'destination',
						'contact_fname',
						'contact_lname',
						'contact_company',
						'contact_email',
						'contact_phone');

		if(in_array($key, $accepted)){
			$this->params[$key] = $value;
			return true;
		}
		return false;
	}


	/**
	 * Sets the link type and adjusts the protocol accordingly.
	 *
	 * @param string $type
	 * @return boolean
	 * @access public
	 */
	public function setLinktype($type){

		$accepted = array('instant','product','api');

		if(in_array($type, $accepted)){

			$this->link_type = $type;

			switch($this->link_type){
				case 'instant':
					$this->protocol = 'https://';
					break;
				case 'api':
					$this->product  = 'order';
					$this->protocol = 'http://';
				case 'product':
					$this->protocol = 'http://';
					break;
			}
			return true;
		}
		return false;
	}


	/**
	 * Generates a url for either the link or form action.
	 *
	 * @param string $product
	 * @param string $link_type
	 * @return string
	 * @access public
	 */
	public function url($product = false, $link_type = false){

		$this->product	= $product ? $product : $this->product;

		$this->setLinktype($link_type);

		// Begin the URL
		$url = $this->protocol;
		$url .= $this->url_base;
		$url .= '/'.$this->company;
		$url .= '/'.$this->link_type;
		$url .= '/'.$this->product;

		// Append the mode parameter if it's set
		if($this->mode && $this->mode != 'live'){
			$this->addParameter('mode', $this->mode);
		}

		// Append any additional parameters
		$url .= $this->build_params('url');

		return $url;

	}


	/**
	 * Builds the parameters to be passed. Query string form for URLs, hidden
	 * field format for forms.
	 *
	 * @param string $request_type
	 * @return string
	 * @access private
	 */
	protected function build_params($request_type = 'url'){

		$params = '';

		if(!empty($this->params)){
			if($this->link_type != 'api'){
				$params = '?'.http_build_query($this->params);
			} else {
				if($request_type != 'url'){

					$input = '<input type="hidden" name="%s" value="%s">'."\n";

					foreach($this->params as $key => $value){
						$params .= sprintf($input, $key, $value);
					}
				}
			}
		}

		return $params;

	}


	/**
	 * Generates a link directly to the URL.
	 *
	 * @param string $link_type
	 * @return string
	 * @access public
	 */
	public function link($text = false, $product = false, $link_type = false){
		return sprintf($this->link_html, $this->url($product, $link_type), ($text ? $text : $product));
	}


	/**
	 * Generates a base form submission to the existing URLs
	 *
	 * @param string $product
	 * @param string $action
	 * @param string $text
	 * @return string
	 * @access public
	 */
	public function form($product, $action = 'add', $text = 'Purchase'){

		// Fprms seems to always reference the product link type
		$this->setLinktype('product');

		// Append the action parameter
		$this->addParameter('action',$action);

		// Build the form html
		$html = '<form method="POST" action="%s"><input type="submit" value="%s" /></form>';
		return sprintf($html, $this->url($product), $text);

	}


	/**
	 * Builds an advanced api create or update order form.
	 *
	 * @param array $products
	 * @param string $style
	 * @param string $action
	 * @param string $destination
	 * @param string $text
	 * @return string
	 * @access public
	 */
	public function form_adv($products, $style = 'checkbox', $action = 'create', $destination = 'contents', $text = 'Purchase'){

		$this->setLinktype('api');
		$this->addParameter('action',$action);
		$this->addParameter('destination',$destination);

		// Build the form html
		$html = '<form method="POST" action="%s">%s%s<input type="submit" value="%s" /></form>';

		// Add the fields
		$field_html = '';
		switch($style){
			case 'checkbox':
				$field_html = $this->product_input_checkbox($products);
				break;
			case 'radio':
				$field_html = $this->product_input_radio($products);
				break;
			case 'text':
				$field_html = $this->product_input_text($products);
				break;
		}

		return sprintf($html, $this->url(), $this->build_params('form'), $field_html, $text);

	}


	/**
	 * Returns input textbox fields for the API creat form
	 *
	 * @param array $products
	 * @return string
	 * @access private
	 */
	protected function product_input_text($products){
		$field = '<input type="hidden" name="product_%s_path" value="/%s"><input type="text" name="product_%1$s_quantity" value="0"/> %s<br />';
		if(is_array($products)){

			$field_html = '';
			$i = 1;

			foreach($products as $slug => $product){
				$field_html .= sprintf($field, $i, $slug, $product);
			}
		}
		return $field_html;
	}


	/**
	 * Returns input checkbox fields for the API creat form
	 *
	 * @param array $products
	 * @return string
	 * @access private
	 */
	protected function product_input_checkbox($products){
		$field = '<input type="checkbox" name="product_%s_path" value="/%s"/> %s<br />';
		if(is_array($products)){

			$field_html = '';
			$i = 1;

			foreach($products as $slug => $product){
				$field_html .= sprintf($field, $i, $slug, $product);
			}
		}
		return $field_html;
	}


	/**
	 * Returns input radio fields for the API creat form
	 *
	 * @param array $products
	 * @return string
	 * @access private
	 */
	protected function product_input_radio($products){
		$field = '<input type="radio" name="product_%s_path" group="products" value="/%s"/> %s<br />';
		if(is_array($products)){

			$field_html = '';
			$i = 1;

			foreach($products as $slug => $product){
				$field_html .= sprintf($field, $i, $slug, $product);
			}
		}
		return $field_html;
	}
}
?>