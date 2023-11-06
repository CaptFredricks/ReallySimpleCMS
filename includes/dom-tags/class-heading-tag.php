<?php
/**
 * The <h1-h6> DOMtag.
 * @since 1.0.1
 *
 * @author Jace Fincham
 * @package DomTags
 */
namespace DomTags;

class HeadingTag extends \DomTag implements DomTagInterface {
	/**
	 * The tag types.
	 * @since 1.0.1
	 *
	 * @access private
	 * @var array
	 */
	private const TAG_TYPES = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
	
	/**
	 * Construct the DOMtag.
	 * @since 1.0.1
	 *
	 * @access public
	 * @param array|null $args (optional) -- The list of arguments.
	 * @return string
	 */
	public static function tag(?array $args = null): string {
		$type = self::TAG_TYPES[1];
		
		if(isset($args['type']) && in_array($args['type'], self::TAG_TYPES, true))
			$type = $args['type'];
		
		return parent::constructTag($type, self::props(), $args);
	}
	
	/**
	 * The tag's props.
	 * @since 1.0.1
	 *
	 * @access public
	 * @return array
	 */
	public static function props(): array {
		return parent::ALWAYS_WL;
	}
}