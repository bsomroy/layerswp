<?php /**
 * Widget Design Controller Class
 *
 * This file is the source of the Widget Design Pop out  in Layers.
 *
 * @package Layers
 * @since Layers 1.0.0
 */

class Layers_Design_Controller {

	/**
	* Generate Design Options
	*
	* @param    string     $type       Sidebar type, side/top
	* @param    array       $this->widget     Widget object (for name, id, etc)
	* @param    array       $instance   Widget $instance
	* @param    array       $components Array of standard components to support
	* @param    array       $custom_components Array of custom components and elements
	*/

	public function __construct( $type = 'side' , $widget = NULL, $instance = array(), $components = array( 'columns' , 'background' , 'imagealign' ) , $custom_components = array() ) {

		// Initiate Widget Inputs
		$this->form_elements = new Layers_Form_Elements();

		// If there is no widget information provided, can the operation
		if( NULL == $widget ) return;
		$this->widget = $widget;

		// Set type side | top
		$this->type = $type;

		// Set widget values as an object ( we like working with objects )
		if( empty( $instance ) ) {
			$this->values = array( 'design' => NULL );
		} elseif( isset( $instance[ 'design' ] ) ) {
			$this->values = $instance[ 'design' ];
		} else {
			$this->values = NULL;
		}

		// Setup the components for use
		$this->components = $components;
		$this->custom_components = $custom_components;

		// Setup the controls
		$this->setup_controls();

		// Fire off the design bar
		$this->render_design_bar();

	}

	public function render_design_bar() {

		$container_class = ( 'side' == $this->type ? 'layers-pull-right' : 'layers-visuals-horizontal' ); ?>

		<div class="layers-visuals <?php echo esc_attr( $container_class ); ?>">
			<h6 class="layers-visuals-title">
				<span class="icon-settings layers-small"></span>
			</h6>
			<ul class="layers-visuals-wrapper layers-clearfix">
				<?php // Render Design Controls
				$this->render_controls(); ?>
				<?php // Show trash icon (for use when in an accordian)
				$this->render_trash_control(); ?>
			</ul>
		</div>
	<?php }
	
	private function setup_controls() {

		$this->controls = array();
		
		foreach( (array) $this->components as $component_key => $component_value ) {
			
			if ( is_array( $component_value ) ) {
				
				// This case allows for overriding of existing Design Bar Component types, and the creating of new custom Components.
				$method = "{$component_key}_component";
				
				if ( method_exists( $this, $method ) ) {
					
					// This is the overriding existing component case.
					ob_start();
					$this->$method( $component_value );
					$this->controls[] = trim( ob_get_clean() );
				}
				else {
					
					// This is the creating of new custom component case.
					ob_start();
					$this->custom_component(
						$component_key, // Give the component a key (will be used as class name too)
						$component_value // Send through the inputs that will be used
					);
					$this->controls[] = trim( ob_get_clean() );
				}
			}
			elseif ( 'custom' === $component_value && !empty( $this->custom_components ) ) {
				
				// This case is legacy - the old method of creating custom components.
				foreach ( $this->custom_components as $key => $custom_component_args ) {
					
					ob_start();
					$this->custom_component(
						$key, // Give the component a key (will be used as class name too)
						$custom_component_args // Send through the inputs that will be used
					);
					$this->controls[] = trim( ob_get_clean() );
				}
			}
			elseif ( method_exists( $this, "{$component_value}_component" ) ) {
				
				// This is the standard method of calling a component that already exists
				$method = "{$component_value}_component";
				
				ob_start();
				$this->$method();
				$this->controls[] = trim( ob_get_clean() );
			}
		}

	}

	private function render_controls(){

		// If there are no controls to render, do nothing
		if( empty( $this->controls ) ) return;

		echo implode( '', $this->controls );
	}

	/**
	* Custom Compontent
	*
	* @param    string     $key        Simply the key and classname for the icon,
	* @param    array       $args       Component arguments, including the form items
	*/

	public function render_control( $key = NULL, $args = array() ){

		if( empty( $args ) ) return;

		// Setup variables from $args
		$icon_css = $args[ 'icon-css' ];
		$label = $args[ 'label' ];
		$menu_wrapper_class = ( isset( $args[ 'wrapper-class' ] ) ? $args[ 'wrapper-class' ] : 'layers-pop-menu-wrapper layers-content-small' );

		// Add a fallback to the elements arguments
		$element_args = ( isset( $args[ 'elements' ] ) ? $args[ 'elements' ] : array() );

		// Return filtered element array
		$elements = apply_filters( 'layers_design_bar_' . $key . '_elements', $element_args ); ?>

		<li class="layers-visuals-item">
			<a href="" class="layers-icon-wrapper">
				<span class="<?php echo esc_attr( $icon_css ); ?>"></span>
				<span class="layers-icon-description">
					<?php echo $label; ?>
				</span>
			</a>
			<?php if( isset( $elements ) ) { ?>
				<div class="<?php echo esc_attr( $menu_wrapper_class ); ?>">
					<div class="layers-pop-menu-setting">
						<?php foreach( $elements as $key => $form_args ) { ?>
						   <?php echo $this->render_input( $form_args ); ?>
						<?php } ?>
					</div>
				</div>
			<?php } // if we have elements ?>
		</li>
	<?php }

	private function render_trash_control(){
		
		if( isset( $this->widget['show_trash'] ) ) { ?>
		<li class="layers-visuals-item layers-pull-right">
			<a href="" class="layers-icon-wrapper layers-icon-error">
				<span class="icon-trash" data-number="<?php echo $this->widget['number']; ?>"></span>
			</a>
		</li>
	<?php }
	}


	/**
	 * Load input HTML
	 *
	 * @param    array       $array()    Existing option array if exists (optional)
	 * @return   array       $array      Array of options, all standard DOM input options
	 */
	public function render_input( $form_args = array() ) {
		?>
		<div class="layers-<?php echo esc_attr( $form_args['type'] ); ?>-wrapper layers-form-item">
			<?php if ( 'checkbox' != $form_args['type'] && isset( $form_args['label'] ) && '' != $form_args['label'] ) { ?>
				<label><?php echo esc_html( $form_args['label'] ); ?></label>
			<?php } ?>

			<?php if ( isset( $form_args['wrapper'] ) ) { ?>
				<<?php echo $form_args['wrapper']; ?> <?php if ( $form_args['wrapper-class'] ) echo 'class="' . $form_args['wrapper-class'] . '"'; ?>>
			<?php } ?>

			<?php echo $this->form_elements->input( $form_args ); ?>

			<?php if ( isset( $form_args['wrapper'] ) ) { ?>
				</<?php echo $form_args['wrapper']; ?>>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Layout Options
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function layout_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'layout';

		// Setup icon CSS
		$defaults['icon-css'] = ( isset( $this->values['layout'] ) && NULL != $this->values ? 'icon-' . $this->values['layout'] : 'icon-layout-fullwidth' );

		// Add a Label
		$defaults['label'] = __( 'Layout', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-small';

		// Add elements
		$defaults['elements'] = array(
			'layout' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[layout]',
				'id' => $this->widget['id'] . '-layout',
				'value' => ( isset( $this->values['layout'] ) ) ? $this->values['layout'] : NULL,
				'options' => array(
					'layout-boxed' => __( 'Boxed', 'layerswp' ),
					'layout-fullwidth' => __( 'Full Width', 'layerswp' )
				)
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_layout_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * List Style - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function liststyle_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'liststyle';

		// Setup icon CSS
		$defaults['icon-css'] = ( isset( $this->values['liststyle'] ) && NULL != $this->values ? 'icon-' . $this->values['liststyle'] : 'icon-list-masonry' );

		// Add a Label
		$defaults['label'] = __( 'List Style', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-small';

		// Add elements
		$defaults['elements'] = array(
			'liststyle' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[liststyle]',
				'id' => $this->widget['id'] . '-liststyle',
				'value' => ( isset( $this->values['liststyle'] ) ) ? $this->values['liststyle'] : NULL,
				'options' => array(
					'list-grid' => __( 'Grid', 'layerswp' ),
					'list-list' => __( 'List', 'layerswp' ),
					'list-masonry' => __( 'Masonry', 'layerswp' )
				)
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_liststyle_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Columns - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function columns_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'columns';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-columns';

		// Add a Label
		$defaults['label'] = __( 'Columns', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-content-small';

		// Add elements
		$defaults['elements'] = array(
			'columns' => array(
				'type' => 'select',
				'label' => __( 'Columns', 'layerswp' ),
				'name' => $this->widget['name'] . '[columns]',
				'id' => $this->widget['id'] . '-columns',
				'value' => ( isset( $this->values['columns'] ) ) ? $this->values['columns'] : NULL,
				'options' => array(
					'1' => __( '1 Column', 'layerswp' ),
					'2' => __( '2 Columns', 'layerswp' ),
					'3' => __( '3 Columns', 'layerswp' ),
					'4' => __( '4 Columns', 'layerswp' ),
					'6' => __( '6 Columns', 'layerswp' )
				)
			),
			'color' => array(
				'type' => 'color',
				'label' => __( 'Background Color', 'layerswp' ),
				'name' => $this->widget['name'] . '[column-background-color]',
				'id' => $this->widget['id'] . '-columns-background-color',
				'value' => ( isset( $this->values['column-background-color'] ) ) ? $this->values['column-background-color'] : NULL
			),
			'gutter' => array(
				'type' => 'checkbox',
				'label' => __( 'Gutter', 'layerswp' ),
				'name' => $this->widget['name'] . '[gutter]',
				'id' => $this->widget['id'] . '-gutter',
				'value' => ( isset( $this->values['gutter'] ) ) ? $this->values['gutter'] : NULL
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_columns_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Text Align - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function textalign_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'textalign';

		// Setup icon CSS
		$defaults['icon-css'] = ( isset( $this->values['textalign'] ) && NULL != $this->values ? 'icon-' . $this->values['textalign'] : 'icon-text-center' );

		// Add a Label
		$defaults['label'] = __( 'Text Align', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-content-small';

		// Add elements
		$defaults['elements'] = array(
			'textalign' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[textalign]',
				'id' => $this->widget['id'] . '-textalign',
				'value' => ( isset( $this->values['textalign'] ) ) ? $this->values['textalign'] : NULL,
				'options' => array(
					'text-left' => __( 'Left', 'layerswp' ),
					'text-center' => __( 'Center', 'layerswp' ),
					'text-right' => __( 'Right', 'layerswp' ),
					'text-justify' => __( 'Justify', 'layerswp' )
				)
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_textalign_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Image Align - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function imagealign_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'imagealign';

		// Setup icon CSS
		$defaults['icon-css'] = ( isset( $this->values['imagealign'] ) && NULL != $this->values ? 'icon-' . $this->values['imagealign'] : 'icon-image-left' );

		// Add a Label
		$defaults['label'] = __( 'Image Align', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-small';

		// Add elements
		$defaults['elements'] = array(
			'imagealign' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[imagealign]',
				'id' => $this->widget['id'] . '-imagealign',
				'value' => ( isset( $this->values['imagealign'] ) ) ? $this->values['imagealign'] : NULL,
				'options' => array(
					'image-left' => __( 'Left', 'layerswp' ),
					'image-right' => __( 'Right', 'layerswp' ),
					'image-top' => __( 'Top', 'layerswp' )
				)
			),
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_imagealign_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Featured Image - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function featuredimage_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'featuredimage';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-featured-image';

		// Add a Label
		$defaults['label'] = __( 'Featured Image', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-content-small';

		// Add elements
		$defaults['elements'] = array(
			'featuredimage' => array(
				'type' => 'image',
				'label' => __( 'Featured Image', 'layerswp' ),
				'name' => $this->widget['name'] . '[featuredimage]',
				'id' => $this->widget['id'] . '-featuredimage',
				'value' => ( isset( $this->values['featuredimage'] ) ) ? $this->values['featuredimage'] : NULL
			),
			'featuredvideo' => array(
				'type' => 'text',
				'label' => __( 'Video URL (oEmbed)', 'layerswp' ),
				'name' => $this->widget['name'] . '[featuredvideo]',
				'id' => $this->widget['id'] . '-featuredvideo',
				'value' => ( isset( $this->values['featuredvideo'] ) ) ? $this->values['featuredvideo'] : NULL
			),
			'imageratios' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[imageratios]',
				'id' => $this->widget['id'] . '-imageratios',
				'value' => ( isset( $this->values['imageratios'] ) ) ? $this->values['imageratios'] : NULL,
				'options' => array(
					'image-portrait' => __( 'Portrait', 'layerswp' ),
					'image-landscape' => __( 'Landscape', 'layerswp' ),
					'image-square' => __( 'Square', 'layerswp' ),
					'image-no-crop' => __( 'None', 'layerswp' ),
					'image-round' => __( 'Round', 'layerswp' ),
				),
				'wrapper' => 'div',
				'wrapper-class' => 'layers-icon-group'
			),
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_featuredimage_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Image Size - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function imageratios_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'imageratios';

		// Setup icon CSS
		$defaults['icon-css'] = ( isset( $this->values['imageratios'] ) && NULL != $this->values ? 'icon-' . $this->values['imageratios'] : 'icon-image-size' );

		// Add a Label
		$defaults['label'] = __( 'Image Ratio', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-small';

		// Add elements
		$defaults['elements'] = array(
			'imageratio' => array(
				'type' => 'select-icons',
				'name' => $this->widget['name'] . '[imageratios]',
				'id' => $this->widget['id'] . '-imageratios',
				'value' => ( isset( $this->values['imageratios'] ) ) ? $this->values['imageratios'] : NULL,
				'options' => array(
					'image-portrait' => __( 'Portrait', 'layerswp' ),
					'image-landscape' => __( 'Landscape', 'layerswp' ),
					'image-square' => __( 'Square', 'layerswp' ),
					'image-no-crop' => __( 'None', 'layerswp' )
				)
			),
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_imageratios_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Fonts - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function fonts_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'fonts';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-font-size';

		// Add a Label
		$defaults['label'] = __( 'Text', 'layerswp' );

		// Add a Wrapper Class
		$defaults['wrapper-class'] = 'layers-pop-menu-wrapper layers-animate layers-content-small';

		// Add elements
		$defaults['elements'] = array(
			'fonts-align' => array(
				'type' => 'select-icons',
				'label' => __( 'Text Align', 'layerswp' ),
				'name' => $this->widget['name'] . '[fonts][align]',
				'id' => $this->widget['id'] . '-fonts-align',
				'value' => ( isset( $this->values['fonts']['align'] ) ) ? $this->values['fonts']['align'] : NULL,
				'options' => array(
					'text-left' => __( 'Left', 'layerswp' ),
					'text-center' => __( 'Center', 'layerswp' ),
					'text-right' => __( 'Right', 'layerswp' ),
					'text-justify' => __( 'Justify', 'layerswp' )
				),
				'wrapper' => 'div',
				'wrapper-class' => 'layers-icon-group'
			),
			'fonts-size' => array(
				'type' => 'select',
				'label' => __( 'Text Size', 'layerswp' ),
				'name' => $this->widget['name'] . '[fonts][size]',
				'id' => $this->widget['id'] . '-fonts-size',
				'value' => ( isset( $this->values['fonts']['size'] ) ) ? $this->values['fonts']['size'] : NULL,
				'options' => array(
					'small' => __( 'Small', 'layerswp' ),
					'medium' => __( 'Medium', 'layerswp' ),
					'large' => __( 'Large', 'layerswp' )
				)
			),
			'fonts-color' => array(
				'type' => 'color',
				'label' => __( 'Text Color', 'layerswp' ),
				'name' => $this->widget['name'] . '[fonts][color]',
				'id' => $this->widget['id'] . '-fonts-color',
				'value' => ( isset( $this->values['fonts']['color'] ) ) ? $this->values['fonts']['color'] : NULL
			)
		);
		
		$args = $this->merge_component( $defaults, $args );
		
		$this->render_control( $key, apply_filters( 'layerswp_font_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Background - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function background_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'background';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-photo';

		// Add a Label
		$defaults['label'] = __( 'Background', 'layerswp' );

		// Add elements
		$defaults['elements'] = array(
			'background-image' => array(
				'type' => 'image',
				'label' => __( 'Background Image', 'layerswp' ),
				'button_label' => __( 'Choose Image', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][image]',
				'id' => $this->widget['id'] . '-background-image',
				'value' => ( isset( $this->values['background']['image'] ) ) ? $this->values['background']['image'] : NULL
			),
			'background-color' => array(
				'type' => 'color',
				'label' => __( 'Background Color', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][color]',
				'id' => $this->widget['id'] . '-background-color',
				'value' => ( isset( $this->values['background']['color'] ) ) ? $this->values['background']['color'] : NULL
			),
			'background-repeat' => array(
				'type' => 'select',
				'label' => __( 'Repeat', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][repeat]',
				'id' => $this->widget['id'] . '-background-repeat',
				'value' => ( isset( $this->values['background']['repeat'] ) ) ? $this->values['background']['repeat'] : NULL,
				'options' => array(
					'no-repeat' => __( 'No Repeat', 'layerswp' ),
					'repeat' => __( 'Repeat', 'layerswp' ),
					'repeat-x' => __( 'Repeat Horizontal', 'layerswp' ),
					'repeat-y' => __( 'Repeat Vertical', 'layerswp' )
				)
			),
			'background-position' => array(
				'type' => 'select',
				'label' => __( 'Position', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][position]',
				'id' => $this->widget['id'] . '-background-position',
				'value' => ( isset( $this->values['background']['position'] ) ) ? $this->values['background']['position'] : NULL,
				'options' => array(
					'center' => __( 'Center', 'layerswp' ),
					'top' => __( 'Top', 'layerswp' ),
					'bottom' => __( 'Bottom', 'layerswp' ),
					'left' => __( 'Left', 'layerswp' ),
					'right' => __( 'Right', 'layerswp' )
				)
			),
			'background-stretch' => array(
				'type' => 'checkbox',
				'label' => __( 'Stretch', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][stretch]',
				'id' => $this->widget['id'] . '-background-stretch',
				'value' => ( isset( $this->values['background']['stretch'] ) ) ? $this->values['background']['stretch'] : NULL
			),
			'background-darken' => array(
				'type' => 'checkbox',
				'label' => __( 'Darken', 'layerswp' ),
				'name' => $this->widget['name'] . '[background][darken]',
				'id' => $this->widget['id'] . '-background-darken',
				'value' => ( isset( $this->values['background']['darken'] ) ) ? $this->values['background']['darken'] : NULL
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_background_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}
	
	/**
	 * Call To Action Customization - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function buttons_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'buttons';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-call-to-action';

		// Add a Label
		$defaults['label'] = __( 'Buttons', 'layerswp' );

		// Add elements
		$defaults['elements'] = array(
			'buttons-background-color' => array(
				'type' => 'color',
				'label' => __( 'Background Color', 'layerswp' ),
				'name' => $this->widget['name'] . '[buttons][background-color]',
				'id' => $this->widget['id'] . '-buttons-background',
				'value' => ( isset( $this->values['buttons']['background-color'] ) ) ? $this->values['buttons']['background-color'] : NULL
			),
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_button_colors_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}
	/**
	 * Advanced - Static Option
	 *
	 * @param    array       $args       Additional arguments to pass to this function
	 */
	public function advanced_component( $args = array() ) {

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Set a key for this input
		$key = 'advanced';

		// Setup icon CSS
		$defaults['icon-css'] = 'icon-settings';

		// Add a Label
		$defaults['label'] = __( 'Advanced', 'layerswp' );

		// Add elements
		$defaults['elements'] = array(
			'customclass' => array(
				'type' => 'text',
				'label' => __( 'Custom Class(es)', 'layerswp' ),
				'name' => $this->widget['name'] . '[advanced][customclass]',
				'id' => $this->widget['id'] . '-advanced-customclass',
				'value' => ( isset( $this->values['advanced']['customclass'] ) ) ? $this->values['advanced']['customclass'] : NULL,
				'placeholder' => 'example-class'
			),
			'customcss' => array(
				'type' => 'textarea',
				'label' => __( 'Custom CSS', 'layerswp' ),
				'name' => $this->widget['name'] . '[advanced][customcss]',
				'id' => $this->widget['id'] . '-advanced-customcss',
				'value' => ( isset( $this->values['advanced']['customcss'] ) ) ? $this->values['advanced']['customcss'] : NULL,
				'placeholder' => ".classname {\n\tbackground: #333;\n}"
			),
			'padding' => array(
				'type' => 'trbl-fields',
				'label' => __( 'Padding (px)', 'layerswp' ),
				'name' => $this->widget['name'] . '[advanced][padding]',
				'id' => $this->widget['id'] . '-advanced-padding',
				'value' => ( isset( $this->values['advanced']['padding'] ) ) ? $this->values['advanced']['padding'] : NULL
			),
			'margin' => array(
				'type' => 'trbl-fields',
				'label' => __( 'Margin (px)', 'layerswp' ),
				'name' => $this->widget['name'] . '[advanced][margin]',
				'id' => $this->widget['id'] . '-advanced-margin',
				'value' => ( isset( $this->values['advanced']['margin'] ) ) ? $this->values['advanced']['margin'] : NULL
			),
			'widget-id' => array(
				'type' => 'text',
				'label' => __( 'Widget Anchor ID', 'layerswp' ),
				'disabled' => TRUE,
				'value' => '#'  . str_ireplace( '-design' , '', $this->widget['id'] )
			)
		);
		
		$args = $this->merge_component( $defaults, $args );

		$this->render_control( $key, apply_filters( 'layerswp_advanced_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}

	/**
	 * Custom Compontent
	 *
	 * @param    string     $key        Simply the key and classname for the icon,
	 * @param    array       $args       Component arguments, including the form items
	 */
	public function custom_component( $key = NULL, $args = array() ) {

		if ( empty( $args ) )
			return;

		// If there is no widget information provided, can the operation
		if ( NULL == $this->widget )
			return;

		// Render Control
		$this->render_control( $key, apply_filters( 'layerswp_custom_component_args', $args, $key, $this->type, $this->widget, $this->values ) );
	}
	
	/**
	 * Merge Compontent
	 */
	public function merge_component( $defaults, $args ) {
		
		// Grab the elements and unset them - so we can work with them individually.
		$defaults_elements = isset( $defaults['elements'] ) ? $defaults['elements'] : array() ;
		if ( isset( $defaults['elements'] ) ) unset( $defaults['elements'] );
		$args_elements = isset( $args['elements'] ) ? $args['elements'] : array() ;
		if ( isset( $args['elements'] ) ) unset( $args['elements'] );
		
		// New collection of elements consisting of a specific combo of the $defaults and the $args.
		$new_elements = array();
		
		foreach ( $args_elements as $args_key => $args_value ) {
			
			if ( is_string( $args_value ) && isset( $defaults_elements[ $args_value ] ) ) {
				
				// This case means the caller has specified a custom $args 'elements' config
				// but has only passed a ref to the input by it's 'string 'background-image'
				// allowing them to reposition the input without redefining all the settings
				// the input.
				$new_elements[ $args_value ] = $defaults_elements[ $args_value ];
				
				// We've got what we needed from this element so remove it from the reference array.
				unset( $defaults_elements[ $args_value ] );
			}
			else if ( is_array( $args_value ) && isset( $defaults_elements[ $args_key ] ) ) {
				
				// This case means the caller has specified a custom $args 'elements' config
				// and has specified their own custom input field config - allowing them to
				// create a new custom field.
				$new_elements[ $args_key ] = $args_value;
				
				// We've got what we needed from this element so remove it from the reference array.
				unset( $defaults_elements[ $args_key ] );
			}
		}
		
		// This handles merging the important non-elements like 'icon-css' and 'title'
		$args = array_merge( $defaults, $args );
		
		// Either 'replace' or 'merge' the new input - so either show only the ones you have chosen
		// or show the ones you have chosen after the defaults of the component.
		if ( isset( $args['elements_combine'] ) && 'replace' === $args['elements_combine'] ) {
			$args['elements'] = $new_elements;
		}
		else{ // 'merge' or anything else.
			$args['elements'] = array_merge( $defaults_elements, $new_elements );
		}
		
		return $args;
	}
	
} //class Layers_Design_Controller
