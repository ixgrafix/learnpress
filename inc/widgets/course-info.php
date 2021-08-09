<?php

/**
 * Course Info Widget.
 *
 * @author   ThimPress
 * @category Widgets
 * @package  Learnpress/Widgets
 * @version  4.0.0
 * @extends  LP_Widget
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Widget_Course_Info' ) ) {

	/**
	 * Class LP_Widget_Course_Info
	 */
	class LP_Widget_Course_Info extends LP_Widget {

		/**
		 * LP_Widget_Course_Info constructor.
		 */
		public function __construct() {
			$this->widget_cssclass    = 'learnpress widget_course_info';
			$this->widget_description = esc_html__( 'Display the Course Infomation', 'learnpress' );
			$this->widget_id          = 'learnpress_widget_course_info';
			$this->widget_name        = esc_html__( 'LearnPress - Course Info', 'learnpress' );
			$this->settings           = array(
				'title'     => array(
					'label' => esc_html__( 'Title', 'learnpress' ),
					'type'  => 'text',
					'std'   => esc_html__( 'Course Info', 'learnpress' ),
				),
				'course_id' => array(
					'label'     => esc_html__( 'Select Course', 'learnpress' ),
					'type'      => 'autocomplete',
					'post_type' => LP_COURSE_CPT,
				),
				'css_class' => array(
					'label' => esc_html__( 'CSS Class', 'learnpress' ),
					'type'  => 'text',
					'std'   => '',
				),
			);

			parent::__construct();
		}

		/**
		 * Show widget in frontend.
		 */
		public function widget( $args, $instance ) {
			if ( ! learn_press_is_course() ) {
				return;
			}

			wp_enqueue_script( 'lp-widgets' );

			$serialized_instance = serialize( $instance );

			$data = array_merge(
				$this->widget_data_attr,
				array(
					'widget'   => $this->widget_id,
					'instance' => base64_encode( $serialized_instance ),
					'hash'     => wp_hash( $serialized_instance ),
					'courseId' => get_the_ID(),
				)
			);

			echo $this->lp_widget_content( $data, $args, $instance );
		}

		public function lp_rest_api_content( $instance, $params ) {
			if ( ! empty( $params['courseId'] ) ) {
				$course = learn_press_get_course( $params['courseId'] );

				if ( $course ) {
					return learn_press_get_template_content(
						'widgets/course-info.php',
						array(
							'course'   => $course,
							'instance' => $instance,
						)
					);
				}
			}

			return new WP_Error( 'no_params', esc_html__( 'Error: Data Course progress invalid', 'learnpress' ) );
		}
	}
}
