<?php
/**
 * The <br|hr> DOMtag.
 * @since 1.0.0
 *
 * @author Jace Fincham
 * @package DomTags
 */
namespace DomTags;

class SeparatorTag extends \DomTag implements DomTagInterface {
	/**
	 * The tag types.
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private const TAG_TYPES = array('br', 'hr');
	
	/**
	 * Construct the DOMtag.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array|null $args (optional) -- The list of arguments.
	 * @return string
	 */
	public static function tag(?array $args = null): string {
		$type = self::TAG_TYPES[0];
		
		if(isset($args['type']) && in_array($args['type'], self::TAG_TYPES, true))
			$type = $args['type'];
		
		return parent::constructTag($type, self::props(), $args);
	}
	
	/**
	 * The tag's props.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public static function props(): array {
		return parent::ALWAYS_WL;
	}
}