<?php
/**
 * The <input> DOMtag.
 * @since 1.0.0
 *
 * @author Jace Fincham
 * @package DomTags
 */
namespace DomTags;

class InputTag extends \DomTag implements DomTagInterface {
	/**
	 * Construct the DOMtag.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array|null $args (optional) -- The list of arguments.
	 * @return string
	 */
	public static function tag(?array $args = null): string {
		return parent::constructTag('input', self::props(), $args);
	}
	
	/**
	 * The tag's props.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public static function props(): array {
		return array_merge(
			array('type'),
			parent::ALWAYS_WL,
			array('name', 'maxlength', 'pattern', 'value', 'placeholder', 'checked', 'disabled', 'required', 'autofocus')
		);
	}
}