<?php
/**
 * The <abbr> DOMtag.
 * @since 1.0.1
 *
 * @author Jace Fincham
 * @package DomTags
 */
namespace DomTags;

class AbbrTag extends \DomTag implements DomTagInterface {
	/**
	 * Construct the DOMtag.
	 * @since 1.0.1
	 *
	 * @access public
	 * @param array|null $args (optional) -- The list of arguments.
	 * @return string
	 */
	public static function tag(?array $args = null): string {
		return parent::constructTag('abbr', self::props(), $args);
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