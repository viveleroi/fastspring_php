<?php

/**
 * 
 *
 * @todo cleanup this comment
 * @author botskonet
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
	 * PRIVATE MEMBER VARIABLES
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
	 */
	public function addParameter($key, $value){
		$accepted = array('mode','referrer');
		if(in_array($key, $accepted)){
			$this->params[$key] = $value;
			return true;
		}
		return false;
	}


	/**
	 * Sets the link type and adjusts the protocol accordingly.
	 *
	 * @param <type> $type
	 * @return <type>
	 */
	public function setLinktype($type){

		$accepted = array('instant','product');

		if(in_array($type, $accepted)){

			$this->link_type = $type;

			switch($this->link_type){
				case 'instant':
					$this->protocol = 'https://';
					break;
				case 'product':
					$this->protocol = 'http://';
					break;
			}
			return true;
		}
		return false;
	}

	
	/**
	 * <a href="https://sites.fastspring.com/surechoice/instant/autoandhealth?mode=test&referrer=1" target="_top">Auto and Health</a>
	 * @param <type> $link_type
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
		if(!empty($this->params)){
			$url .= '?'.http_build_query($this->params);
		}

		return $url;

	}


	/**
	 * <a href="https://sites.fastspring.com/surechoice/instant/autoandhealth?mode=test&referrer=1" target="_top">Auto and Health</a>
	 * @param <type> $link_type
	 */
	public function link($text = false, $product = false, $link_type = false){
		return sprintf($this->link_html, $this->url($product, $link_type), ($text ? $text : $product));
	}


	/**
	 * Option 2: Add Product to Order and Checkout
	 * <form method="POST" action="http://sites.fastspring.com/surechoice/product/autoandhealth?action=order" target="_top"><input type="submit" value="Purchase" /></form>
	 *
	 * Option 4: Shopping Cart - Add Product to Order
	 * <form method="POST" action="http://sites.fastspring.com/surechoice/product/autoandhealth?action=add" target="_top"><input type="submit" value="Add to Order" /></form>
	 *
	 * Option 5: Shopping Cart - View Product Detail Page
	 * <a href="http://sites.fastspring.com/surechoice/product/autoandhealth?action=adds" target="_top">Add to Order</a>
	 */

	/**
	 * Option 6: Create Order API (Advanced)
	 *
	 *
	 *
Parameter Notes

    * action - Use the value "create" to always replace the order contents, or the value "update" to add / modify contents.
    * destination - Controls the landing page and may be either "contents" or "checkout".
    * product_X_path, product_X_quantity - Increment the value of X to pass in more than one product.
    * mode - Optionally use a hidden input field with the name "mode" and value of "test" to activate test purchases.
    * Optional Customer Information - Customer information may optionally be passed in via the parameters: contact_fname, contact_lname, contact_company, contact_email, contact_phone


	 *
	 *
	 * Example Form with Quantity Box:
	 * <form method="POST" action="http://sites.fastspring.com/surechoice/api/order">
<input type="hidden" name="action" value="create"/>
<input type="hidden" name="destination" value="contents"/>
<p>
<input type="hidden" name="product_1_path" value="/autoandhealth">
<input type="text" name="product_1_quantity" value="0"/> Auto and Health
</p>
<p>
<input type="submit" value="Order Now"/>
	 *
	 *
	 * Example Form with Checkbox:
	 * <form method="POST" action="http://sites.fastspring.com/surechoice/api/order">
<input type="hidden" name="action" value="create"/>
<input type="hidden" name="destination" value="contents"/>
<p>
  <input type="checkbox" name="product_1_path" value="/autoandhealth"/> Auto and Health
</p>
<p>
<input type="submit" value="Order Now"/>
</p>
	 */
	
}
?>