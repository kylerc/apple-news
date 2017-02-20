<?php
namespace Apple_Exporter\Components;

use \Apple_Exporter\Exporter as Exporter;

/**
 * A byline normally describes who wrote the article, the date, etc.
 *
 * @since 0.2.0
 */
class Byline extends Component {

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			array(
				'role' => 'byline',
				'text' => '%%text%%',
			)
		);

		$this->register_json(
			'json',
			array(
				'text' => $text,
			)
	 	);

		$this->set_default_style();
		$this->set_default_layout();
	}

	/**
	 * Set the default style for the component.
	 *
	 * @access private
	 */
	private function set_default_style() {
		$this->register_spec(
			'default-byline',
			__( 'Byline Style', 'apple-news' ),
			array(
				'textAlignment' => '%%text_alignment%%',
				'fontName' => '%%byline_font%%',
				'fontSize' => '%%byline_size%%',
				'lineHeight' => '%%byline_line_height%%',
				'tracking' => '%%byline_tracking%%',
				'textColor' => '%%byline_color%%',
			)
		);

		$this->register_style(
			'default-byline',
			'default-byline',
			array(
				'textAlignment' => $this->find_text_alignment(),
				'fontName' => $this->get_setting( 'byline_font' ),
				'fontSize' => intval( $this->get_setting( 'byline_size' ) ),
				'lineHeight' => intval( $this->get_setting( 'byline_line_height' ) ),
				'tracking' => intval( $this->get_setting( 'byline_tracking' ) ) / 100,
				'textColor' => $this->get_setting( 'byline_color' ),
			),
			'textStyle'
		);
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @access private
	 */
	private function set_default_layout() {
		$this->register_spec(
			'byline-layout',
			__( 'Byline Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top' => 10,
					'bottom' => 10,
				),
			)
		);

		$this->register_full_width_layout(
			'byline-layout',
			'byline-layout',
			array(),
			'layout'
		);
	}

}

