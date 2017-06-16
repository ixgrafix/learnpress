<?php

/**
 * Class LP_Email_Enrolled_Course
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Email_Enrolled_Course' ) ) {
	class LP_Email_Enrolled_Course extends LP_Email {
		/**
		 * LP_Email_Enrolled_Course constructor.
		 */
		public function __construct() {
			$this->id          = 'enrolled_course';
			$this->title       = __( 'Enrolled course', 'learnpress' );
			$this->description = __( 'Send this email to user when they enroll in the course', 'learnpress' );

			$this->template_html  = 'emails/enrolled-course.php';
			$this->template_plain = 'emails/plain/enrolled-course.php';

			$this->default_subject = __( '[{{site_title}}]  You have enrolled in this course ({{course_name}})', 'learnpress' );
			$this->default_heading = __( 'Enrolled course', 'learnpress' );

			$this->support_variables = array(
				'{{site_url}}',
				'{{site_title}}',
				'{{site_admin_email}}',
				'{{site_admin_name}}',
				'{{login_url}}',
				'{{header}}',
				'{{footer}}',
				'{{email_heading}}',
				'{{footer_text}}',
				'{{course_id}}',
				'{{course_name}}',
				'{{course_url}}',
				'{{user_id}}',
				'{{user_name}}',
				'{{user_email}}',
				'{{user_profile_url}}'
			);

			//$this->email_text_message_description = sprintf( '%s {{course_id}}, {{course_title}}, {{course_url}}, {{user_email}}, {{user_name}}, {{user_profile_url}}', __( 'Shortcodes', 'learnpress' ) );

			add_action( 'learn_press_user_enrolled_course_notification', array( $this, 'trigger' ), 99, 3 );

			parent::__construct();
		}


		/**
		 * Trigger email.
		 *
		 * @param $course_id
		 * @param $user_id
		 * @param $user_course_id
		 *
		 * @return bool|void
		 */
		public function trigger( $course_id, $user_id, $user_course_id ) {
			if ( ! $this->enable ) {
				return;
			}

			global $wpdb;

			$user_course_data = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE user_item_id = %d", $user_course_id )
			);

			if ( ! $user_course_data ) {
				// TODO: ...
				return;
			}

			$format = $this->email_format == 'plain_text' ? 'plain' : 'html';
			$course = learn_press_get_course( $course_id );
			$user   = learn_press_get_user( $user_id );

			$this->object = $this->get_common_template_data(
				$format,
				array(
					'course_id'        => $course_id,
					'course_name'      => $course->get_title(),
					'course_url'       => get_the_permalink( $course_id ),
					'user_id'          => $user_id,
					'user_name'        => learn_press_get_profile_display_name( $user ),
					'user_email'       => $user->user_email,
					'user_profile_url' => learn_press_user_profile_link( $user->id )
				)
			);

			$this->variables = $this->data_to_variables( $this->object );

			$this->object['course'] = $course;
			$this->object['user']   = $user;

			$this->recipient = $user->user_email;

			$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			return $return;
		}

		/**
		 * Email template.
		 *
		 * @param string $format
		 *
		 * @return array|object
		 */
		public function get_template_data( $format = 'plain' ) {
			return $this->object;
		}

		/**
		 * Admin settings.
		 */
		public function get_settings() {
			return apply_filters(
				'learn-press/email-settings/enrolled-course/settings',
				array(
					array(
						'type'  => 'heading',
						'title' => $this->title,
						'desc'  => $this->description
					),
					array(
						'title'   => __( 'Enabled', 'learnpress' ),
						'type'    => 'yes-no',
						'default' => 'no',
						'id'      => 'emails_enrolled_course[enable]'
					),
					array(
						'title'      => __( 'Subject', 'learnpress' ),
						'type'       => 'text',
						'default'    => $this->default_subject,
						'id'         => 'emails_enrolled_course[subject]',
						'desc'       => sprintf( __( 'Email subject, default: <code>%s</code>', 'learnpress' ), $this->default_subject ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_enrolled_course[enable]',
									'compare' => '=',
									'value'   => 'yes'
								)
							)
						)
					),
					array(
						'title'      => __( 'Heading', 'learnpress' ),
						'type'       => 'text',
						'default'    => $this->default_heading,
						'id'         => 'emails_enrolled_course[heading]',
						'desc'       => sprintf( __( 'Email heading, default: <code>%s</code>', 'learnpress' ), $this->default_heading ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_enrolled_course[enable]',
									'compare' => '=',
									'value'   => 'yes'
								)
							)
						)
					),
					array(
						'title'                => __( 'Email content', 'learnpress' ),
						'type'                 => 'email-content',
						'default'              => '',
						'id'                   => 'emails_enrolled_course[email_content]',
						'template_base'        => $this->template_base,
						'template_path'        => $this->template_path,//default learnpress
						'template_html'        => $this->template_html,
						'template_plain'       => $this->template_plain,
						'template_html_local'  => $this->get_theme_template_file( 'html', $this->template_path ),
						'template_plain_local' => $this->get_theme_template_file( 'plain', $this->template_path ),
						'support_variables'    => $this->get_variables_support(),
						'visibility'           => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_enrolled_course[enable]',
									'compare' => '=',
									'value'   => 'yes'
								)
							)
						)
					),
				)
			);
		}
	}
}

return new LP_Email_Enrolled_Course();