<?php
/**
 * DOMtags basic setup.
 * @since 1.0.0
 *
 * @author Jace Fincham
 * @package DomTags
 */

/**
 * Construct a DOM tag.
 * @since 1.0.1
 *
 * @param string $tag_name -- The tag name.
 * @param array|null $args -- The args.
 * @return string
 */
function domTag(string $tag_name, ?array $args = null): string {
	switch($tag_name) {
		case 'a':
			return \DomTags\AnchorTag::tag($args);
			break;
		case 'abbr':
			return \DomTags\AbbrTag::tag($args);
			break;
		case 'br': case 'hr':
			$args['type'] = $tag_name;
			return \DomTags\SeparatorTag::tag($args);
			break;
		case 'button':
			return \DomTags\ButtonTag::tag($args);
			break;
		case 'div':
			return \DomTags\DivTag::tag($args);
			break;
		case 'em': case 'i':
			$args['type'] = $tag_name;
			return \DomTags\EmTag::tag($args);
			break;
		case 'fieldset':
			return \DomTags\FieldsetTag::tag($args);
			break;
		case 'form':
			return \DomTags\FormTag::tag($args);
			break;
		case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
			$args['type'] = $tag_name;
			return \DomTags\HeadingTag::tag($args);
			break;
		case 'img':
			return \DomTags\ImageTag::tag($args);
			break;
		case 'input':
			return \DomTags\InputTag::tag($args);
			break;
		case 'label':
			return \DomTags\LabelTag::tag($args);
			break;
		case 'li':
			return \DomTags\ListItemTag::tag($args);
			break;
		case 'ol': case 'ul':
			$args['type'] = $tag_name;
			return \DomTags\ListTag::tag($args);
			break;
		case 'option':
			return \DomTags\OptionTag::tag($args);
			break;
		case 'p':
			return \DomTags\ParagraphTag::tag($args);
			break;
		case 'section':
			return \DomTags\SectionTag::tag($args);
			break;
		case 'select':
			return \DomTags\SelectTag::tag($args);
			break;
		case 'span':
			return \DomTags\SpanTag::tag($args);
			break;
		case 'strong': case 'b':
			return \DomTags\StrongTag::tag($args);
			break;
		case 'textarea':
			return \DomTags\TextareaTag::tag($args);
			break;
		default:
			return 'Invalid tag!';
	}
}