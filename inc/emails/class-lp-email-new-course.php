<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class LP_Email_New_Course
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 1.0
 */

if ( ! class_exists( 'LP_Email_New_Course' ) ) {

	class LP_Email_New_Course extends LP_Email {
		/**
		 * LP_Email_New_Course constructor.
		 */
		public function __construct() {

			$this->id          = 'new_course';
			$this->title       = __( 'New course', 'learnpress' );
			$this->description = __( 'Email settings when a new course is submitted for review', 'learnpress' );

			$this->template_html  = 'emails/new-course.php';
			$this->template_plain = 'emails/plain/new-course.php';

			$this->default_subject = __( '[{{site_title}}] New course has been submitted for review ({{course_name}})', 'learnpress' );
			$this->default_heading = __( 'New course', 'learnpress' );

			$this->recipient = LP()->settings->get( 'emails_new_course.recipient' );

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
				'{{course_edit_url}}',
				'{{course_user_id}}',
				'{{course_user_name}}',
				'{{course_user_email}}',
			);

			//$this->email_text_message_description = sprintf( '%s {{course_id}}, {{course_title}}, {{course_url}}, {{course_edit_url}}, {{user_email}}, {{user_name}}, {{user_profile_url}}', __( 'Shortcodes', 'learnpress' ) );
			parent::__construct();
		}

		/**
		 * Trigger email.
		 *
		 * @param $course_id
		 *
		 * @return bool|void
		 */
		public function trigger( $course_id ) {

			if ( ( ! $this->enable ) || ! $this->get_recipient() ) {
				return;
			}

			$format = $this->email_format == 'plain_text' ? 'plain' : 'html';
			$course = learn_press_get_course( $course_id );
			$user   = learn_press_get_course_user( $course_id );

			$this->object = $this->get_common_template_data(
				$format,
				array(
					'course_id'         => $course_id,
					'course_name'       => $course->get_title(),
					'course_edit_url'   => admin_url( 'post.php?post=' . $course_id . '&action=edit' ),
					'course_user_id'    => $user->id,
					'course_user_name'  => learn_press_get_profile_display_name( $user ),
					'course_user_email' => $user->user_email
				)
			);

			$this->variables = $this->data_to_variables( $this->object );

			$this->object['course']      = $course;
			$this->object['user_course'] = $user;

			$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			return $return;
		}


		/**
		 * Get recipient.
		 *
		 * @return mixed|void
		 */
		public function get_recipient() {
			$recipient = $this->recipient;
			if ( ! $recipient ) {
				$recipient = get_option( 'admin_email' );
			}
			$this->recipient = $recipient;

			return parent::get_recipient(); // TODO: Change the autogenerated stub
		}


		/**
		 * Get email template.
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
				'learn-press/email-settings/new-course/settings',
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
						'id'      => 'emails_new_course[enable]'
					),
					array(
						'title'      => __( 'Recipient(s)', 'learnpress' ),
						'type'       => 'text',
						'default'    => get_option( 'admin_email' ),
						'id'         => 'emails_new_course[recipients]',
						'desc'       => sprintf( __( 'Email recipient(s) (separated by comma), default: <code>%s</code>', 'learnpress' ), get_option( 'admin_email' ) ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_new_course[enable]',
									'compare' => '=',
									'value'   => 'yes'
								)
							)
						)
					),
					array(
						'title'      => __( 'Subject', 'learnpress' ),
						'type'       => 'text',
						'default'    => $this->default_subject,
						'id'         => 'emails_new_course[subject]',
						'desc'       => sprintf( __( 'Email subject, default: <code>%s</code>', 'learnpress' ), $this->default_subject ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_new_course[enable]',
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
						'id'         => 'emails_new_course[heading]',
						'desc'       => sprintf( __( 'Email heading, default: <code>%s</code>', 'learnpress' ), $this->default_heading ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => 'emails_new_course[enable]',
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
						'id'                   => 'emails_new_course[email_content]',
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
									'field'   => 'emails_new_course[enable]',
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

return new LP_Email_New_Course();