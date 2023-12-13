<?php
/**
 * Document Object Model tags, or DOMtags for short.
 * @since 1.0.0
 *
 * @author Jace Fincham
 * @package DomTags
 */
class DomTag {
	/**
	 * Props to always whitelist.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected const ALWAYS_WL = array('id', 'class', 'style', 'title');
	
	/**
	 * Construct a DOMtag.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param string $name -- The name of the tag.
	 * @param array $props -- The whitelisted properties.
	 * @param array|null $args (optional) -- The list of arguments.
	 * @return string
	 */
	protected static function constructTag(string $name, array $props, ?array $args = null): string {
		$tag = '<' . $name;
		
		if($name === 'input')
			if(!in_array('type', $props, true)) $tag .= ' type="text"';
		
		if(!is_null($args)) {
			foreach($args as $key => $value) {
				// Check whether the property has been whitelisted
				if(in_array($key, $props, true) || str_starts_with($key, 'data-')) {
					switch($key) {
						case 'checked':
						case 'disabled':
						case 'required':
						case 'autofocus':
						case 'selected':
							$tag .= $value ? ' ' . $key : '';
							break;
						default:
							$tag .= ' ' . $key . '="' . $value . '"';
					}
				}
			}
		}
		
		$tag .= '>';
		
		$self_closing = array('br', 'hr', 'img', 'input');
		
		if(!in_array($name, $self_closing, true)) {
			$tag .= $args['content'] ?? '';
			$tag .= '</' . $name . '>';
		}
		
		if(!empty($args['label'])) {
			$label_props = self::labelProps();
			$label = '<label';
			
			if(isset($args['label']['content'])) {
				$content = $args['label']['content'];
				unset($args['label']['content']);
			} else {
				$content = '';
			}
			
			foreach($args['label'] as $key => $value) {
				if(in_array($key, $label_props, true) || str_starts_with($key, 'data-'))
					$label .= ' ' . $key . '="' . $value . '"';
			}
			
			$label .= '>' . $tag . ($content ?? '') . '</label>';
			$tag = $label;
		}
		
		return $tag;
	}
	
	/**
	 * Properties for the label tag.
	 * @since 1.0.0
	 *
	 * @access private
	 * @return array
	 */
	private static function labelProps(): array {
		return array_merge(
			self::ALWAYS_WL,
			array('for')
		);
	}
}